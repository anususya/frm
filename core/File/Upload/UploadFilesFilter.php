<?php

namespace Core\File\Upload;

use FilterIterator;
use Iterator;

// @phpstan-ignore missingType.generics
class UploadFilesFilter extends FilterIterator
{
    /**
     * @var array<string, mixed>
     */
    private array $params;

    /**
     * @param Iterator $files
     * @param array<string, mixed>    $params
     */
    public function __construct(Iterator $files, array $params)
    {
        parent::__construct($files);
        $this->params = $params;
    }

    /**
     * @return bool
     */
    public function accept(): bool
    {
        $file = $this->current();

        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        if (isset($this->params['minSize']) && $this->params['minSize'] > $file['size']) {
            return false;
        }

        if (isset($this->params['maxSize']) && $this->params['maxSize'] < $file['size']) {
            return false;
        }

        if (isset($this->params['types'])) {
            if (is_array($this->params['types']) && !in_array($file['type'], $this->params['types'])) {
                return false;
            } elseif (is_string($this->params['types']) && $file['type'] !== $this->params['types']) {
                return false;
            }
        }

        return true;
    }
}
