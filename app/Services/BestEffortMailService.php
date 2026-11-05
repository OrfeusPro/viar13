<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BestEffortMailService
{
    public function send($recipients, Mailable $mailable, string $purpose, array $context = []): bool
    {
        return $this->attempt(
            static fn () => Mail::to($recipients)->send($mailable),
            $purpose,
            array_merge($context, ['mailable' => $mailable::class])
        );
    }

    public function attempt(callable $callback, string $purpose, array $context = []): bool
    {
        try {
            $callback();

            return true;
        } catch (Throwable $exception) {
            Log::error('Transactional email could not be sent.', array_merge($context, [
                'purpose' => $purpose,
                'exception' => $exception,
            ]));

            return false;
        }
    }
}
