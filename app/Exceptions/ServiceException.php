<?php

namespace App\Exceptions;

use Exception;

class ServiceException extends Exception
{
    protected $message;

    public function __construct(string $message = '')
    {
        Parent::__construct();

        $this->message = $message;
    }
}
