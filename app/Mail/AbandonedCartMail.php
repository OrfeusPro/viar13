<?php

namespace App\Mail;

use App;
use App\Models\AbandonedCart;
use App\Models\GalleryDecoration;
use App\Models\GalleryItem;
use App\Repositories\BasketRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $url_cart;
    public string $url_checkout;
    public AbandonedCart $cart;
    public array $cartData;

    public function __construct(string $url_cart, string $url_checkout, AbandonedCart $cart)
    {
        $this->url_cart = $url_cart;
        $this->url_checkout = $url_checkout;
        $this->cart = $cart;
        $this->cartData = $this->processAttributes($cart);
    }

    public function build(): AbandonedCartMail
    {
        return $this->subject(__('mail.restore_basket_text11'))
            ->view('mail.abandoned_cart');
    }

    private function processAttributes(AbandonedCart $cart)
    {
        $cartData = app(BasketRepository::class)->normalizeBasket($cart->cart_data);

        try {
            foreach ($cartData as $index => $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (isset($item["pid"])) {
                    $galleryItem = GalleryItem::withTranslation(App::getLocale(), false)
                        ->where('id', $item["pid"])
                        ->first();

                    if ($galleryItem) {
                        $translated = $galleryItem->translate(App::getLocale());
                        $item['name'] = $translated->name;

                        if (!empty($item["decor_id"])) {
                            $decoration = GalleryDecoration::withTranslation(App::getLocale(), false)
                                ->where('id', $item["decor_id"])
                                ->first();

                            if ($decoration) {
                                $translatedDecor = $decoration->translate(App::getLocale());
                                $item['hud_of'] = $translatedDecor->name;
                            }
                        }
                    }
                }

                $cartData[$index] = $item;
            }
        } catch (Exception $exception) {
            Log::error('Ошибка преобразования корзины при отправке письма о забытой корзине');
        }
        return $cartData;
    }
}
