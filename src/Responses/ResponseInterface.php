<?php
namespace Mikaela\Shopright\Responses;

interface ResponseInterface
{
    function __construct(int $responseCode, array $body);

    function setHeaders();
    function __toString(): string;
}