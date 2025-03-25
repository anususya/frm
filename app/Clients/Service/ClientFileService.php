<?php

declare(strict_types=1);

namespace App\Clients\Service;

use App\Clients\Model\ClientsDataGenerator;
use App\Clients\Model\UploadFileModel;
use App\Clients\Repository\ClientRepository;

class ClientFileService
{
    protected ClientRepository $repository;
    protected ClientsDataGenerator $clientsGenerator;
    protected ClientImportService $importService;

    public function __construct()
    {
        $this->repository = new ClientRepository();
        $this->clientsGenerator = new ClientsDataGenerator();
        $this->importService = new ClientImportService();
    }

    /**
     * @param string $uploadFormName
     * @param bool   $needToImport
     *
     * @return array<string, bool>
     */
    public function upload(string $uploadFormName = 'file', bool $needToImport = false): array
    {
        $uploadModel = new UploadFileModel();
        $uploadResult = $uploadModel->upload($uploadFormName);

        if ($needToImport && $uploadResult) {
            $importResult = $this->importService->importFromUploadDirectory();

            return ['uploadResult' => true, 'importResult' => $importResult];
        }

        return ['uploadResult' => $uploadResult, 'importResult' => false];
    }

    public function generate(int $count): bool
    {
        return $this->clientsGenerator->generateClientsDataFile($count);
    }
}
