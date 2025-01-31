<?php

use App\Clients\Controller\ParseController;
use App\Clients\Controller\SearchController;
use Core\Controller\PageNotFoundController;

// phpcs:ignore
class App
{
    public const BASE_APP_DIR = __DIR__;

    /**
     * @throws Exception
     */
    public static function run(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        switch ($path) {
            case '/analyze':
                $cont = new SearchController('clients', 'search');
                $cont->index();
                break;
            case '/parse':
                $cont = new ParseController('clients', 'parse');
                $cont->index();
                break;
            default:
                $cont = new pageNotFoundController('default', '404');
                $cont->index();
        }
    }
}
