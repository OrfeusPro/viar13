<?php

namespace App\Services\AltGeneration\Exceptions;

/**
 * Raised when OpenAI responds successfully but not with the expected JSON.
 */
class InvalidOpenAiResponse extends OpenAiVisionException
{
}
