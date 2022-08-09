<?php

namespace EasyApiBundle\Services\MediaUploader;

use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vich\UploaderBundle\Storage\FileSystemStorage;
use Vich\UploaderBundle\Storage\StorageInterface;

class FileManager
{
    protected FileSystemStorage $fileSystemStorage;

    /**
     * @param FileSystemStorage $fileSystemStorage
     */
    public function __construct(StorageInterface $fileSystemStorage)
    {
        $this->fileSystemStorage = $fileSystemStorage;
    }

    /**
     * @param string $path
     * @param string $filename
     * @return Response
     */
    public function getFileStreamedResponse(string $path, string $filename): Response
    {
        try {
            $stream = fopen($path, 'r');
        } catch (\Exception $e) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_NOT_FOUND, sprintf(ApiProblem::ENTITY_NOT_FOUND, $filename))
            );
        }

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
