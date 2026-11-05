<?php

namespace Tests\Unit;

use App\Services\BestEffortMailService;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class BestEffortMailServiceTest extends TestCase
{
    public function test_transport_failure_is_logged_without_interrupting_checkout(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->with('client@example.test')
            ->andThrow(new RuntimeException('Simulated SMTP failure'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Transactional email could not be sent.'
                    && $context['purpose'] === 'checkout_order_confirmation'
                    && $context['order_id'] === 701
                    && $context['exception'] instanceof RuntimeException;
            });

        $result = app(BestEffortMailService::class)->send(
            'client@example.test',
            new class extends Mailable {},
            'checkout_order_confirmation',
            ['order_id' => 701]
        );

        $this->assertFalse($result);
    }

    public function test_successful_send_is_reported_as_success(): void
    {
        Mail::fake();
        $mailable = new class extends Mailable {};

        $result = app(BestEffortMailService::class)->send(
            'client@example.test',
            $mailable,
            'checkout_order_confirmation',
            ['order_id' => 702]
        );

        $this->assertTrue($result);
        Mail::assertSent($mailable::class);
    }
}
