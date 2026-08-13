<?php

namespace App\Services\AltGeneration\Exceptions;

use RuntimeException;

/**
 * Raised when the configured daily OpenAI call limit has been reached.
 */
class DailyLimitReached extends RuntimeException
{
}
