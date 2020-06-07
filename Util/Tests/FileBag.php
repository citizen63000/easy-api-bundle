<?php

namespace EasyApiBundle\Util\Tests;

class FileBag
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Add a file.
     *
     * @param string $fieldName  The field form name
     * @param string $contents   Contents : a file path or directly the contents of the file
     * @param bool   $asResource True if $contents is a file path
     * @param null   $fileName   The filename to the file
     * @param array  $headers    Additional headers
     */
    public function addFile(string $fieldName, string $contents, bool $asResource = true, $fileName = null, $headers = [])
    {
        $file = [
            'name' => $fieldName,
            'contents' => ($asResource ? fopen($contents, 'r') : $contents),
        ];
        if (null !== $fileName) {
            $file['filename'] = $fileName;
        }
        if (null !== $headers && !empty($headers)) {
            $file['headers'] = $headers;
        }

        $this->data[] = $file;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
