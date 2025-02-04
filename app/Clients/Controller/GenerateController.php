<?php

namespace App\Clients\Controller;

use App\Clients\Model\ClientsDataGenerator;
use Core\Controller\FrontendController;

class GenerateController extends FrontendController
{
    /**
     * @return void
     */
    public function index(): void
    {
        $this->prepareLayout();
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function generate(): void
    {
        $this->prepareLayout();

        if (isset($_POST['count'])) {
            $fileGenerator = new ClientsDataGenerator();
            $result = $fileGenerator->generateClientsDataFile((int) $_POST['count']);
            $this->setBlockData('result', ['result' => $result]);
        }

        $this->renderLayout();
    }
}
