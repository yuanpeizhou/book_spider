<?php

namespace App\Exceptions;

use Mockery\Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ParamsErrorException extends HttpException{
    public function __construct($message = '', $code = 418, Exception $previous = null){
        parent::__construct(200, $message ?: '参数错误', $previous, [], $code);
    }
}