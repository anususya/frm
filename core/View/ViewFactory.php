<?php

declare(strict_types=1);

namespace Core\View;

use Core\Log\Log;
use Exception;

class ViewFactory
{
    protected TwigService $twigService;

    public function __construct()
    {
        $this->twigService = new TwigService();
    }

    /**
     * @param string $template
     * @param array<string, mixed> $data
     *
     * @return string
     */
    public function make(string $template, array $data = []): string
    {
        try {
            return $this->twigService->getTwig()->render($template, $data);
        } catch (Exception $e) {
            Log::write($e->getMessage(), 'error');
            return '';
        }
    }
}
