<?php

namespace App\Clients\Import;

use App;
use App\Clients\Model\UploadFileModel;

class UploadFileImport extends ClientsImport
{
    /**
     * @var string
     */
    protected string $importDirectory = App::BASE_APP_DIR . '/uploads/' . UploadFileModel::UPLOAD_DIR . '/';
}
