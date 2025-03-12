<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Model\ClientsDataGenerator;
use Core\App\Request;
use Core\Controller\FrontendController;

class GenerateController extends FrontendController
{
    public function index(): void
    {
        $this->render('clients/generate/index');
    }

    public function generate(): void
    {
        $blockData = null;
        $count = Request::getParam('count');

        if ($count) {
            $fileGenerator = new ClientsDataGenerator();
            $result = $fileGenerator->generateClientsDataFile((int) $count);
            $blockData = ['result' => ['result' => $result]];
        }

        $this->render('clients/generate/generate', $blockData);
    }
}
