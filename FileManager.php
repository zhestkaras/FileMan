<?php

namespace App\Core;

class FileManager
{
    use \App\Traits\Helper;
    protected $contentDir;

    public function __construct()
    {
        $this->contentDir = 'content';
        if (!is_dir($this->contentDir)) {
            mkdir($this->contentDir, 0755, true);
        }
    }



    private function isPathSafe($fullPath)
    {
        $realBase = realpath($this->contentDir);
        $realPath = realpath($fullPath);

        if ($realPath === false || $realBase === false) {
            return false;
        }

        return strpos($realPath, $realBase) === 0;
    }

    private function buildFullPath($relativePath)
    {
        return $this->contentDir . '/' . ltrim($relativePath, '/');
    }

    public function readFile($relativePath)
    {
        $fullPath = $this->buildFullPath($relativePath);

        if (!$this->isPathSafe($fullPath)) {
            return ($fullPath);
        }

        if (!file_exists($fullPath)) {
            return ($fullPath);
        }

        if (!is_file($fullPath)) {
            return ($fullPath);
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            return ($fullPath);
        }

        return $content;
    }

    public function getListDir($dir = '/')
    {
        $full_path = $this->buildFullPath($dir);
        if (!is_dir($full_path)) return [];

        $dirs = glob($full_path . '/*', GLOB_ONLYDIR);
        return array_filter($dirs, 'is_dir');
    }

}