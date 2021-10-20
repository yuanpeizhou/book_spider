<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;

class ParamsErrorException extends HttpException
{

    /**
     * 自定异常抛出,用于验证器抛出异常
     * @return void
     */
    public function __construct(string $message = "", int $code = 201,\Exception $previous = null)
    {
        parent::__construct(200, $message, $previous, [], $code);
    }

    public function render(Request $request):JsonResponse
    {
        return response()->json(['code' => $this->code, 'message' => $this->message], 200)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}