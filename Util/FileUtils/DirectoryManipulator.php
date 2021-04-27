<?php

namespace EasyApiBundle\Util\FileUtils;

class DirectoryManipulator
{
    /**
     * Can delete not empty directory
     * @param $dir
     * @return bool
     */
    public static function deleteDirectory(string $dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {

            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}