<?php

namespace EasyApiBundle\Util\Tests;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;

class FileBag
{
    protected array $data = [];

    /**
     * Add a file.
     *
     * @param string $fieldName The field form name
     * @param null   $fileName  The filename to the file
     * @param array  $headers   Additional headers
     */
    public function addFile(string $fieldName, string $filePath, $fileName = null, array $headers = [])
    {
        $mimeTypeGuesser = new FileinfoMimeTypeGuesser();
        $uploadedFile = new UploadedFile($filePath, $fileName, $mimeTypeGuesser->guessMimeType($filePath), null, true);

        $file = [
            'name' => $fieldName,
            'file' => $uploadedFile,
            'headers' => $headers,
            'filename' => $fileName,
        ];

        $this->data[] = $file;
    }

    /**
     * Get data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        $files = [];
        foreach ($this->data as $fileData) {
            $files[$fileData['name']] = $fileData['file'];
        }

        return $files;
    }
}
