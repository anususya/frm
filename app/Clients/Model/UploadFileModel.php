<?php

declare(strict_types=1);

namespace App\Clients\Model;

use Core\Config\Config;
use Core\File\Upload\UploadFiles;

class UploadFileModel
{
    public const UPLOAD_DIR = 'clients';

    /**
     * @var array<string, mixed>
     */
    public array $params = [
        'types' => 'text/csv',
        'maxSize' => '5242880'
    ];

    public string $fileName = '';

    public function __construct()
    {
        $this->fileName = Config::get('import.clients.fileName') ?? '';
    }

    public function upload(string $uploadFormName): bool
    {
        if ($this->fileName == '') {
            return false;
        }

        $result = UploadFiles::load($uploadFormName, self::UPLOAD_DIR, $this->params, [$this->fileName]);

        foreach ($result['uploadFiles'] as $file) {
            if ($file['newName'] == $this->fileName) {
                return true;
            }
        }

        return false;
    }
}
