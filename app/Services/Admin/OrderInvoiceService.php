<?php

namespace App\Services\Admin;

use App\Http\Controllers\DynamicPDFController;
use App\Mail\ApproveUserCheckoutMail;
use App\Models\Orders;
use App\Models\User;
use App\Models\UserMessage;
use App\Services\BestEffortMailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderInvoiceService
{
    public function __construct(
        private readonly DynamicPDFController $pdf,
        private readonly BestEffortMailService $mail,
    ) {}

    public function generate(Orders $order, ?array $firmData = null): string
    {
        $vrNumber = Orders::getVRById($order->id);

        if (! $vrNumber && ! $order->has_pdf) {
            throw ValidationException::withMessages([
                'invoice' => 'Сначала добавьте номер накладной.',
            ]);
        }

        $pdfUrl = DB::transaction(function () use ($order, $vrNumber, $firmData): string {
            $lockedOrder = Orders::query()->lockForUpdate()->findOrFail($order->id);
            $pdfUrl = $this->pdf->getPDFFromOrder(
                $this->prepareOrder($lockedOrder),
                $vrNumber,
                $firmData,
            );

            $lockedOrder->forceFill([
                'has_pdf' => 1,
                'pdf_link' => $pdfUrl,
            ])->save();

            return $pdfUrl;
        });

        Log::info('Admin order invoice generated.', [
            'order_id' => $order->id,
            'custom_firm_data' => $firmData !== null,
        ]);

        $order->refresh();

        return $pdfUrl;
    }

    public function approve(Orders $order): array
    {
        if (! $order->has_pdf) {
            throw ValidationException::withMessages([
                'invoice' => 'Сначала сгенерируйте счёт.',
            ]);
        }

        $customer = $order->user;

        if (! $customer) {
            throw ValidationException::withMessages([
                'invoice' => 'У заказа не найден клиент для отправки счёта.',
            ]);
        }

        $wasApproved = (bool) $order->pdf_approved;
        $pdfPath = DB::transaction(function () use ($order, $customer, $wasApproved): string {
            $lockedOrder = Orders::query()->lockForUpdate()->findOrFail($order->id);
            $approvedDate = filled($lockedOrder->approved_date) && $lockedOrder->approved_date !== 'null'
                ? $lockedOrder->approved_date
                : now()->format('d.m.Y');

            $lockedOrder->forceFill([
                'has_pdf' => 1,
                'approved_date' => $approvedDate,
                'pdf_approved' => 1,
                'status' => $lockedOrder->status === 'watching' ? 'pegging' : $lockedOrder->status,
            ])->save();

            $pdfUrl = $this->pdf->getPDFFromOrder(
                $this->prepareOrder($lockedOrder),
                Orders::getVRById($lockedOrder->id),
                null,
            );

            $lockedOrder->forceFill(['pdf_link' => $pdfUrl])->save();

            if (! $wasApproved) {
                $lockedCustomer = User::query()->lockForUpdate()->findOrFail($customer->id);
                $this->applyReferralBonus($lockedCustomer);
            }

            return public_path('storage/pdf/'.$lockedOrder->id.'.pdf');
        });

        $locale = $customer->preferredLocale() ?: 'ru';
        try {
            $messages = UserMessage::query()->get()->translate($locale, 'ru');
            $messageData = $messages[0] ?? [];
        } catch (Throwable $exception) {
            Log::warning('Invoice approval email texts could not be loaded.', [
                'order_id' => $order->id,
                'exception' => $exception,
            ]);
            $messageData = [];
        }
        $freshOrder = $order->fresh();
        $mailable = new ApproveUserCheckoutMail(
                $pdfPath,
                (string) $freshOrder->getRawOriginal('updated_at'),
                $this->decodeJson($freshOrder->getRawOriginal('items')),
                $messageData,
                $freshOrder->id,
                $locale,
            );
        $emailEnabled = (bool) config('admin_migration.invoice_email_enabled', false);
        $mailProcessed = $emailEnabled
            ? $this->mail->send(
                $customer->email,
                $mailable,
                'admin_order_invoice_approval',
                ['order_id' => $freshOrder->id, 'user_id' => $customer->id],
            )
            : $this->mail->attempt(
                static fn () => Mail::mailer('log')->to($customer->email)->send($mailable),
                'admin_order_invoice_approval_preview',
                ['order_id' => $freshOrder->id, 'user_id' => $customer->id],
            );

        Log::info('Admin order invoice approved.', [
            'order_id' => $order->id,
            'reapproved' => $wasApproved,
            'mail_sent' => $emailEnabled && $mailProcessed,
            'mail_logged' => ! $emailEnabled && $mailProcessed,
            'email_suppressed' => ! $emailEnabled,
        ]);

        $order->refresh();

        return [
            'mail_sent' => $emailEnabled && $mailProcessed,
            'mail_logged' => ! $emailEnabled && $mailProcessed,
            'email_suppressed' => ! $emailEnabled,
            'reapproved' => $wasApproved,
        ];
    }

    public function defaultFirmData(Orders $order): array
    {
        if (str_starts_with((string) Orders::getVRById($order->id), 'VR00')) {
            return [
                'name' => 'SIA "ViarStudia"',
                'reg_num' => '',
                'addr' => 'Druvas iela4 , Daugavpils nov., LV-5459',
                'bank' => 'Swedbank',
                'vat_num' => 'LV41503069660',
                'bank_code' => 'Swedbank',
                'office_addr' => 'Lubānas 63/65, Rīga',
                'acc_num' => 'LV85HABA0551039079593',
            ];
        }

        return [
            'name' => 'Viarcanvas',
            'reg_num' => '',
            'addr' => 'Sėlių a. 16-2, Zarasai',
            'bank' => 'LT193250067283712555',
            'vat_num' => '',
            'bank_code' => '',
            'office_addr' => '',
            'acc_num' => '',
        ];
    }

    private function prepareOrder(Orders $order): array
    {
        $data = (array) DB::table('orders')->where('id', $order->id)->first();
        $data['delivery'] = $this->decodeJson($data['delivery'] ?? null);
        $data['items'] = $this->decodeJson($data['items'] ?? null);
        $data['price'] = rtrim(rtrim(number_format((float) ($data['price'] ?? 0), 2, ',', ' '), '0'), ',').' €';

        return $data;
    }

    private function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function applyReferralBonus(User $customer): void
    {
        if (blank($customer->invited)) {
            return;
        }

        $inviter = User::query()
            ->where('id', '!=', $customer->id)
            ->where('inv_sale_code', $customer->invited)
            ->lockForUpdate()
            ->first();

        if (! $inviter) {
            return;
        }

        $bonus = (float) DB::table('stocks')->where('id', 1)->value('friend_sale');
        $inviter->forceFill([
            'bonuses' => (float) $inviter->bonuses + $bonus,
            'inv_sale_code' => str()->random(8),
            'is_active_friend_inv' => 1,
        ])->save();

        $customer->forceFill(['invited' => null])->save();
    }
}
