<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Support\Facades\URL;

class GiftcardController extends Controller
{
    /**
     * Предпросмотр (HTML) — просто возвращаем HTML в браузер
     */
    public function previewGiftCard()
    {
        // В браузере вы увидите, как это выглядит "вживую"
        $html = $this->getGiftCardHtml();
        return response($html);
    }

    /**
     * Генерация PDF
     *
     * @param int $price Цена подарочной карты
     * @param string $locale Локаль (например, "ru")
     * @param string $code Код подарочной карты
     * @return string URL на сгенерированный PDF
     */
    public function generateGiftCardPDF($user_id, $order_id, $coupon_id, $price = 50, $locale = "ru", $code = "RN34567")
    {
        if (isset($locale) && $locale) {
            App::setLocale($locale);
        } else {
            App::setLocale('ru');
        }

        // Получаем HTML для подарочной карты
        $html = $this->getGiftCardHtml($price, $code);

        // Создаем экземпляр Dompdf
        $pdf = App::make('dompdf.wrapper');

        // Включаем поддержку удалённых ресурсов (если используются внешние URL)
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        // Задаем разрешение: по умолчанию 96 dpi
        $pdf->getDomPDF()->set_option('dpi', 96);
        $pdf->loadHTML($html);
        // Указываем размер: 900px x 700px (при dpi=96, 1px ≈ 0.75pt)
        // 900px = 675pt, 700px = 525pt
        $pdf->setPaper([0, 0, 675, 525]); // Портретная ориентация
        $pdf->setWarnings(false);

        // Сохраняем PDF
        $pdf_name = "storage/pdf-gift-card/".$order_id."_".$user_id."_".$coupon_id.".pdf";

        $filePath = public_path($pdf_name);
        $pdf->save($filePath);

        return URL::to('/') . '/'.$pdf_name;
    }

    /**
     * Генерация JPEG версии подарочной карты
     *
     * Генерирует PDF (временный файл), затем конвертирует его в JPEG с помощью Imagick.
     *
     * @param int $price Цена подарочной карты
     * @param string $locale Локаль (например, "ru")
     * @param string $code Код подарочной карты
     * @return string URL на сгенерированный JPEG
     */
    // public function generateGiftCardJPG($price = 50, $locale = "ru", $code = "RN34567")
    // {
    //     // Генерируем PDF-версию (временный файл)
    //     if (isset($locale) && $locale) {
    //         App::setLocale($locale);
    //     } else {
    //         App::setLocale('ru');
    //     }

    //     $html = $this->getGiftCardHtml($price, $code);

    //     $pdf = App::make('dompdf.wrapper');
    //     $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
    //     $pdf->getDomPDF()->set_option('dpi', 96);
    //     $pdf->loadHTML($html);
    //     $pdf->setPaper([0, 0, 675, 525]);
    //     $pdf->setWarnings(false);

    //     // Сохраняем временный PDF
    //     $pdfPath = public_path('storage/pdf/giftcard_temp.pdf');
    //     $pdf->save($pdfPath);

    //     // Используем Imagick для конвертации PDF в JPEG
    //     $imagick = new \Imagick();
    //     // Устанавливаем разрешение для конвертации (чем выше – тем лучше качество)
    //     $imagick->setResolution(300, 300);
    //     // Читаем первую страницу PDF
    //     $imagick->readImage($pdfPath . '[0]');
    //     // Если необходимо, объединяем слои
    //     $imagick = $imagick->flattenImages();
    //     $imagick->setImageFormat('jpeg');
    //     // Настраиваем качество JPEG
    //     $imagick->setImageCompressionQuality(90);

    //     // Сохраняем JPEG в /public/storage/pdf/giftcard.jpg
    //     $jpgPath = public_path('storage/pdf/giftcard.jpg');
    //     $imagick->writeImage($jpgPath);

    //     // Освобождаем ресурсы
    //     $imagick->clear();
    //     $imagick->destroy();

    //     // Удаляем временный PDF-файл
    //     if (file_exists($pdfPath)) {
    //         unlink($pdfPath);
    //     }

    //     return URL::to('/') . '/storage/pdf/giftcard.jpg';
    // }

    /**
     * Вспомогательная функция формирует HTML-код "подарочной карты"
     * с <img>, base64 для изображения, стилями @page, шрифтом DejaVu Sans и поворотом -6,5°
     *
     * @param int $price Цена подарочной карты
     * @param string $code Код подарочной карты
     * @return string HTML-разметка
     */
    private function getGiftCardHtml($price = 50, $code = "RN34567")
    {
        // Превращаем фоновую картинку в Base64
        $imagePath = public_path('theme/viar/images/gift/giftcard.png');
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData;
        } else {
            $imageSrc = ''; // Заглушка, если файл не найден
        }

        $gift_card = __("gl.gift_card");
        $card_code = __("gift_card.card_code");

        $loc = app()->getLocale();
        if($loc == "ru")
        {
            $currency_sign = "&#8364;";
        } else {
            $currency_sign = " EUR";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>{$gift_card}</title>
    <style>
        /* Настройка страницы для PDF: 900x700 px, без полей */
        @page {
            size: 900px 700px;
            margin: 0;
        }
        body, html {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
        }
        .wrapper {
            position: relative;
            width: 900px;
            height: 700px;
            margin: -70px -55px;
        }
        .rotate {
            position: relative;
            transform: rotate(-6.5deg);
            transform-origin: center;
            overflow: visible;
            display: inline-block;
            width: 900px;
        }
        .bg-image {
            position: absolute;
            width: 900px;
            height: 707px;
        }
        .text-overlay {
            position: absolute;
            top: 0%;
            left: 50%;
            transform: translate(-50%, 47%);
            text-align: center;
            width: 900px;
            height: 700px;
        }
        .gift-card-title {
            font-weight: 700;
            font-size: 44px;
            color: #1E2533;
            margin-bottom: 13px;
            white-space: nowrap;
        }
        .gift-card-value {
            font-weight: 700;
            font-size: 66px;
            color: #FA7846;
            margin-bottom: 15px;
        }
        .gift-card-code {
            font-weight: 400;
            font-size: 22px;
            line-height: 150%;
            color: #848484;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="rotate">
            <!-- Фон как <img>, чтобы Dompdf корректно обработал изображение -->
            <img src="{$imageSrc}" alt="" class="bg-image" />
            <div class="text-overlay">
                <div class="gift-card-title">{$gift_card}</div>
                <div class="gift-card-value">{$price}{$currency_sign}</div>
                <div class="gift-card-code">{$card_code} - {$code}</div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
