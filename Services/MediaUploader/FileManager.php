<?php

namespace EasyApiBundle\Services\MediaUploader;

use EasyApiBundle\Entity\MediaUploader\AbstractMedia;
use EasyApiBundle\Exception\ApiProblemException;
use EasyApiBundle\Util\ApiProblem;
use PHPUnit\Util\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vich\UploaderBundle\Storage\StorageInterface;

class FileManager
{
    protected StorageInterface $fileSystemStorage;

    public function __construct(StorageInterface $fileSystemStorage)
    {
        $this->fileSystemStorage = $fileSystemStorage;
    }

    public function createFromWebUrl(string $class, string $url): AbstractMedia
    {
        /** @var AbstractMedia $media */
        $media = new $class;
        if (!$media instanceof AbstractMedia) {
            throw new Exception(sprintf('%s must be an instance of AbstractMedia', $class));
        }

        $fileData = file_get_contents($url);
        $tmpFilePath = '/tmp/'.md5(uniqid());
        (new Filesystem())->dumpFile($tmpFilePath, $fileData);
        $mimeType = finfo_buffer(finfo_open(), $fileData, FILEINFO_MIME_TYPE);
        $fileInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $originalFileName = "{$fileInfo['filename']}.{$fileInfo['extension']}";
        $uploadedFile = new UploadedFile($tmpFilePath, $originalFileName, $mimeType, null, true);
        $media->setFile($uploadedFile);

        return $media;
    }

    /**
     * @param string $path
     * @param string $filename
     * @return StreamedResponse
     */
    public function getFileStreamedResponse(string $path, string $filename): StreamedResponse
    {
        try {
            $stream = fopen($path, 'r');
        } catch (\Exception $e) {
            throw new ApiProblemException(new ApiProblem(Response::HTTP_NOT_FOUND, sprintf(ApiProblem::ENTITY_NOT_FOUND, $filename)));
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
     * Return the system path of a file type in vich system.
     *
     * @param $entity
     * @param string $vichName Name of the file in vich configuration
     * @return string|null
     */
    public function getFileSystemPath($entity, string $vichName = 'file'): ?string
    {
        return $this->fileSystemStorage->resolvePath($entity, $vichName);
    }

    /**
     * Return the web path of a file type in vich system.
     *
     * @param $entity
     * @param string $vichName Name of the file in vich configuration
     *
     * @return string|null
     */
    public function getFileWebPath($entity, string $vichName = 'file'): ?string
    {
        return $this->fileSystemStorage->resolveUri($entity, $vichName);
    }
}
