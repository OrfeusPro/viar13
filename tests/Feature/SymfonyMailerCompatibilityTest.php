<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class SymfonyMailerCompatibilityTest extends TestCase
{
    public function test_direct_html_mail_uses_the_symfony_compatible_api(): void
    {
        $html = '<strong>Заказ: 18449</strong>';

        Mail::send([], [], function ($message) use ($html): void {
            $message->to('admin@example.test');
            $message->subject('VIARCANVAS – новый заказ');
            $message->html($html);
        });

        $transport = Mail::getSymfonyTransport();
        $sentMessage = $transport->messages()->last();
        $message = $sentMessage->getOriginalMessage();

        $this->assertInstanceOf(Email::class, $message);
        $this->assertSame($html, $message->getHtmlBody());
        $this->assertSame('VIARCANVAS – новый заказ', $message->getSubject());
    }

    public function test_application_does_not_use_the_legacy_string_set_body_api(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(app_path())
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            $this->assertStringNotContainsString(
                '->setBody(',
                $source,
                $file->getPathname().' still uses the Symfony-incompatible legacy body API.'
            );
        }
    }
}
