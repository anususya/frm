<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Model\ClientModel;
use Core\App\Superglobals;
use Core\Controller\FrontendController;

class SearchController extends FrontendController
{
    public function index(): void
    {
        $searchParams = ClientModel::convertRequestParams(Superglobals::Get->getParamsValue());

        $blockData = [
            'search' => [
                'searchResult' => ClientModel::where($searchParams)->get(),
            ]
        ];

        $this->render('clients/search', $blockData);
    }
}
