<?php
namespace Mikaela\Shopright\Responses;

class HtmlResponse extends Response implements ResponseInterface
{
    protected string $responseType = 'Content-Type: text/html; charset=utf-8';

    function __construct(public int $responseCode, public array $body) {}

    public function __toString(): string
    {
        $this->setHeaders();
        $viewContent = "";

        if (isset($this->body['view']) && file_exists(APP_ROOT . "/src/Views/{$this->body['view']}.html")) {
            $viewContent = file_get_contents(APP_ROOT . "/src/Views/{$this->body['view']}.html");

            foreach ($this->body['viewData'] as $templateVariable => $htmlString) {
                $viewContent = str_replace("[[{$templateVariable}]]", $htmlString, $viewContent);
            }

            $viewContent = str_replace("[[timestamp]]", time(), $viewContent);
        }

        return $viewContent;
    }
}