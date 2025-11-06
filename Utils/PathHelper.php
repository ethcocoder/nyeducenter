<?php
if (!class_exists('PathHelper')) {
class PathHelper {
    private static $instance = null;
    private $rootPath;

    private function __construct() {
        $this->rootPath = dirname(__DIR__);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getRootPath() {
        return $this->rootPath;
    }

    public function getIncludePath($relativePath) {
        return $this->rootPath . '/' . ltrim($relativePath, '/');
    }

    public function requireFile($relativePath) {
        $filePath = $this->getIncludePath($relativePath);
        if (file_exists($filePath)) {
            require_once $filePath;
        } else {
            throw new Exception("Required file not found: $filePath");
        }
    }

    public function includeFile($relativePath) {
        $filePath = $this->getIncludePath($relativePath);
        if (file_exists($filePath)) {
            include $filePath;
        } else {
            throw new Exception("Included file not found: $filePath");
        }
    }

    public function getProfileImagePath($imageName) {
        if (empty($imageName) || $imageName === 'default.png') {
            return $this->rootPath . '/assets/img/default_profile.png';
        }
        return $this->rootPath . '/assets/Upload/profile/' . $imageName;
    }

    public function getUploadPath($type, $filename) {
        return $this->rootPath . '/assets/Upload/' . $type . '/' . $filename;
    }

    public function getAssetPath($type, $filename) {
        return $this->rootPath . '/assets/' . $type . '/' . $filename;
    }
}
} 