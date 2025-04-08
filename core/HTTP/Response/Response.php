<?php

declare(strict_types=1);

namespace Core\HTTP\Response;

class Response
{
    /**
     * @param string $content
     * @param int    $statusCode
     * @param array<int|string, mixed>  $headers
     */
    public function __construct(
        protected string $content,
        protected int $statusCode = 200,
        protected array $headers = []
    ) {
    }

    public function send(): void
    {
        // Set HTTP headers
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        // Set status code
        http_response_code($this->statusCode);

        // Output the content
        echo $this->content;
    }
}
