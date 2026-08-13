<?php

namespace App\Services\AltGeneration\Exceptions;

use RuntimeException;

/**
 * Raised when an image owner has no public page URL.
 */
class NoPublicUrl extends RuntimeException
{
}
