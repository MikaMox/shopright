<?php

namespace Mikaela\Shopright\Responses;

abstract class Response
{
    public function setHeaders(): void
    {
        header($this->responseType);
        http_response_code($this->responseCode);
    }
}