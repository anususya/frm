<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Import\ClientsImport;
use Core\Controller\FrontendController;

class ParseController extends FrontendController
{
    public function index(): void
    {
        $blockData = [
            'search' => [
                'importResult' => (new ClientsImport())->run()
            ]
        ];

        $this->render('clients/parse', $blockData);
    }
}
