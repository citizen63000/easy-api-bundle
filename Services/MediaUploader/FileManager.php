<?php

namespace EasyApiBundle\Services\MediaUploader;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vich\UploaderBundle\Storage\FlysystemStorage;

class FileManager
{
    protected FlysystemStorage $fileSystemStorage;

    /**
     * @param FlysystemStorage $fileSystemStorage
     */
    public function __construct(FlysystemStorage $fileSystemStorage)
    {
        $this->fileSystemStorage = $fileSystemStorage;
    }

    /**
     * @param string $path
     * @return Response
     */
    public function getFileStreamedResponse(string $path, string $filename): Response
    {
        // stream opening
        $stream = fopen($path, 'r');

        $response = new StreamedResponse(function () use ($stream) {
            stream_copy_to_stream($stream, fopen('php://output', 'w'));
        });

        $response->headers->set('Content-Type', mime_content_type($path));
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', filesize($path));
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }

    /**
     * Return the system path of a file type in vich system
     * @param $entity
     * @param string $vichName Name of the file in vich configuration
     * @return string|null
     */
    public function getFileSystemPath($entity, string $vichName = 'file'): ?string
    {
        return $this->fileSystemStorage->resolvePath($entity, $vichName);
    }

    /**
     * Return the web path of a file type in vich system
     * @param $entity
     * @param string $vichName Name of the file in vich configuration
     * @return string|null
     * @throws \Exception
     */
    public function getFileWebPath($entity, string $vichName = 'file'): ?string
    {
        return $this->fileSystemStorage->resolveUri($entity, $vichName);
    }
}