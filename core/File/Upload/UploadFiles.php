<?php

declare(strict_types=1);

namespace Core\File\Upload;

use ArrayObject;
use Core\App\App;
use Core\App\Superglobals;

class UploadFiles
{
    public const UPLOAD_FOLDER = App::BASE_APP_DIR . '/uploads/';
    public const PARAMS_TO_FILTER = ['types', 'minSize', 'maxSize'];

    /**
     * @param string     $uploadFormName
     * @param string     $uploadDir
     * @param array<string, mixed>      $params
     * @param null|array<string> $names
     *
     * @return array<string, mixed>
     */
    public static function load(
        string $uploadFormName,
        string $uploadDir,
        array $params = [],
        ?array $names = null
    ): array {
        $uploadFiles = [];
        $failedFiles = [];

        $uploadsFiles = Superglobals::Files->getParamValue($uploadFormName);

        if (!$uploadsFiles) {
            return ['uploadFiles' => $uploadFiles, 'failedFiles' => $failedFiles];
        }

        $uploadDir = trim($uploadDir, '/');
        $uploadDir = self::UPLOAD_FOLDER . "{$uploadDir}/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $files = self::covertUploadFilesArray((array) $uploadsFiles);
        $filesArrayObject = new ArrayObject($files);
        $filesIterator = new UploadFilesFilter($filesArrayObject->getIterator(), $params);

        foreach ($filesIterator as $key => $file) {
            if ($names !== null && $names[$key]) {
                $file['newName'] = $names[$key];
                $uploadFileName = $names[$key];
            } else {
                $uploadFileName = basename($file['name']);
            }

            $uploadFile = $uploadDir . $uploadFileName;

            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                chmod($uploadFile, 0777);
                $uploadFiles[$key] = $file;
            }
        }

        $failedFiles = array_diff_key($files, $uploadFiles);
        array_walk($failedFiles, function (&$file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $file['error'] = -1;
            }
        });

        return ['uploadFiles' => $uploadFiles, 'failedFiles' => $failedFiles];
    }

    /**
     * @param array<string, mixed> $uploadFiles
     *
     * @return array<int, mixed>
     */
    public static function covertUploadFilesArray(array $uploadFiles): array
    {
        $keys = array_keys($uploadFiles);
        $files = [];

        if (!isset($uploadFiles['error'])) {
            return $files;
        }
        if (!is_array($uploadFiles['error'])) {
            return [$uploadFiles];
        }

        $count = count($uploadFiles['error']);

        for ($i = 0; $i < $count; $i++) {
            foreach ($keys as $key) {
                $files[$i][$key] = $uploadFiles[$key][$i];
            }
        }

        return $files;
    }
}
