<?php

declare(strict_types=1);

namespace Core\View;

use Core\App\App;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    private const TEMPLATE_DEFAULT_DIR = App::BASE_APP_DIR . 'frontend';
    private Environment $twig;

    public function __construct(?string $templateDir = null)
    {
        // Set up the Twig loader to load templates from the specified directory
        $loader = new FilesystemLoader(['templates'], $templateDir ?? self::TEMPLATE_DEFAULT_DIR);
        $this->twig = new Environment($loader);
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }
}
