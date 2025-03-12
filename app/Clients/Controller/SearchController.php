<?php

declare(strict_types=1);

namespace App\Clients\Controller;

use App\Clients\Model\ClientsModel;
use App\Clients\Model\TestClientModel;
use Core\App\Superglobals;
use Core\Controller\FrontendController;
use Core\Database\DatabaseServiceProvider;

class SearchController extends FrontendController
{
    public function index(): void
    {
        DatabaseServiceProvider::boot();
        $clientsModel = new ClientsModel();
        $searchParams = $clientsModel->convertRequestParams(Superglobals::Get->getParamsValue());
        $result = TestClientModel::where($searchParams)->get();

        $searchResult = $clientsModel->getClients([]);
        $blockData = [
            'search' => [
                'searchResult' => $searchResult,
                'searchResult1' => $result
            ]
        ];

        $this->render('clients/search', $blockData);
    }

    public function test(): void
    {
        DatabaseServiceProvider::boot();
        $client = TestClientModel::find(6);
        $client->setAttribute('country', 'US')->save();
        $clients = TestClientModel::findMany([6,7]);
        $clientsModel = new TestClientModel();
        $attributes = ['country' => "Macedonia, The Former Yugoslav Republic of",
                       'city' => 'Guangzhou',
                       'isActive' => 'true',
                       'gender' => 'male',
                       'birthDate' => '1979-12-31',
                       'salary' => '9574',
                       'hasChildren' => 'true',
                       'familyStatus' => 'divorced',
                       'registrationDate' => '2020-07-13'
        ];
        $clientsModel->setRawAttributes($attributes);
        $clientsModel->save();
        $searchResult = TestClientModel::all();
        foreach ($searchResult as $client) {
            //$client->delete();
        }
        $blockData = [
            'search' => [
                'searchResult' => $searchResult
            ]
        ];
    }
}
