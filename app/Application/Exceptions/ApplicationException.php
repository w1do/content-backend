<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

use Exception;
use Throwable;

abstract class ApplicationException extends Exception
{
    public function __construct(string $message = '', int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
