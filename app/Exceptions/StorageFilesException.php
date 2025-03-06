<?php

namespace App\Exceptions;

use Exception;

class StorageFilesException extends Exception
{
    public function __construct(string $message = "Files error", int $code = 500)
    {
        parent::__construct($message, $code);
    }
}
