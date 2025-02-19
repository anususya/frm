<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Model\ClientsDataGenerator;
use Core\App\Superglobals\Variables;
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
        $count = Variables::getParamValue(Variables::TYPE_POST, 'count');

        if ($count) {
            $fileGenerator = new ClientsDataGenerator();
            $result = $fileGenerator->generateClientsDataFile((int) $count);
            $blockData = ['result' => ['result' => $result]];
        }

        $this->render('clients/generate/generate', $blockData);
    }
}
