<?php

declare(strict_types=1);

namespace App\Clients\Import;

use App\Clients\Model\UploadFileModel;
use Core\App\App;

class UploadFileImport extends ClientsImport
{
    protected string $importDirectory = App::BASE_APP_DIR . '/uploads/' . UploadFileModel::UPLOAD_DIR . '/';
}
