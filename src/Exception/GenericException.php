<?php

namespace j4nr6n\ADIF\Exception;

class GenericException extends \Exception
{
    public function __construct(string $message = "", ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }
}
