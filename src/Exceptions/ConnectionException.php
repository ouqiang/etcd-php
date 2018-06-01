<?php

namespace Etcd\Exceptions;

use Exception;

class ConnectionException extends Exception
{
    public function __construct($message)
    {
        $message = is_array($message) ? \json_encode($message) : $message;
        parent::__construct($message);
    }
}
