<?php

declare(strict_types=1);

namespace Core\HTTP\Response;

use Core\View\ViewFactory;

class ResponseFactory
{
    protected ViewFactory $view;
    public function __construct()
    {
        $this->view = new ViewFactory();
    }

    /**
     * @param string $content
     * @param int $status
     * @param array<int|string, mixed> $headers
     *
     * @return Response
     */
    public function make(string $content = '', int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }

    /**
     * @param string $template
     * @param array<string, mixed> $data
     * @param int $status
     * @param array<int|string, mixed>  $headers
     *
     * @return Response
     */
    public function view(string $template, array $data = [], int $status = 200, array $headers = []): Response
    {
        $content = $this->view->make($template, $data);

        return $this->make($content, $status, $headers);
    }

    /**
     * @param array<string, mixed> $data
     * @param int   $status
     * @param array<int|string, mixed> $headers
     *
     * @return JsonResponse
     */
    public function json(array $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
}
