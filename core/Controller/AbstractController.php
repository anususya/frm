<?php

declare(strict_types=1);

namespace Core\Controller;

use Core\HTTP\Response\ResponseFactory;

class AbstractController
{
    public function __construct(
        protected ResponseFactory $response
    ) {
    }

    /**
     * @param string $template
     * @param array<string, mixed>  $data
     * @param int $status
     * @param array<int|string, mixed>  $headers
     *
     * @return void
     */
    protected function view(string $template, array $data = [], int $status = 200, array $headers = []): void
    {
        $response = $this->response->view($template, $data, $status, $headers);
        $response->send();
    }

    /**
     * @param array<string, mixed>  $data
     * @param int $status
     * @param array<int|string, mixed>  $headers
     *
     * @return void
     */
    protected function json(array $data = [], int $status = 200, array $headers = []): void
    {
        $response = $this->response->json($data, $status, $headers);
        $response->send();
    }
}
