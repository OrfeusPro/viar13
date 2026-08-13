<?php

namespace App\Services\AltGeneration\Exceptions;

use Throwable;

/**
 * Represents a non-successful OpenAI API response or transport failure.
 */
class OpenAiApiException extends OpenAiVisionException
{
    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string|null
     */
    private $responseBody;

    /**
     * @param string $message
     * @param int $statusCode
     * @param string|null $responseBody
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message,
        int $statusCode = 0,
        ?string $responseBody = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);

        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string|null
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * Whether the configured fallback model should be attempted.
     *
     * @return bool
     */
    public function shouldTryFallback(): bool
    {
        return $this->statusCode === 400 || $this->statusCode >= 500;
    }
}
