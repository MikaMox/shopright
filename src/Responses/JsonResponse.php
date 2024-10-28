<?php
namespace Mikaela\Shopright\Responses;

class JsonResponse extends Response implements ResponseInterface
{
    protected string $responseType = 'Content-Type: application/json';

    function __construct(public int $responseCode, public array $body) {}

    public function __toString(): string
    {
        $this->setHeaders();

        return json_encode($this->body);
    }
}