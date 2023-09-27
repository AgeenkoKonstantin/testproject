<?php

namespace App\Exception;

class UploadFileTypeInvalidException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('uploaded file type is invalid');
    }

}
