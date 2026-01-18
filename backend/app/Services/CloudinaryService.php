<?php

namespace App\Services;

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class CloudinaryService
{
    protected $uploadApi;

    public function __construct()
    {
        // Cấu hình Cloudinary
        Configuration::instance([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        $this->uploadApi = new UploadApi();
    }

    /**
     * Upload Image
     */
    public function uploadImage($file, $prefix)
    {
        if (!$file) {
            throw new \Exception("File không hợp lệ!");
        }

        $uniqueName = $prefix . '_' . uniqid();

        $result = $this->uploadApi->upload(
            $file->getRealPath(),
            [
                'public_id' => $uniqueName,
                'folder' => 'uploads/images'
            ]
        );

        return $result['secure_url'];
    }

    /**
     * Delete Image
     */
    public function deleteImage($publicId)
    {
        $result = $this->uploadApi->destroy($publicId);
        return $result;
    }

    /**
     * Upload Video
     */
    public function uploadVideo($file, $prefix)
    {
        if (!$file) {
            throw new \Exception("File không hợp lệ!");
        }

        $uniqueName = $prefix . '_' . uniqid();

        $result = $this->uploadApi->upload(
            $file->getRealPath(),
            [
                'public_id' => $uniqueName,
                'folder' => 'uploads/videos',
                'resource_type' => 'video'
            ]
        );

        return $result['secure_url'];
    }

    /**
     * Delete Video
     */
    public function deleteVideo($publicId)
    {
        $result = $this->uploadApi->destroy($publicId, [
            'resource_type' => 'video'
        ]);
        return $result;
    }

    /**
     * Extract PublicId from URL
     */
    public function extractPublicId($url)
    {
        if (!$url) return null;

        $parts = explode('/upload/', $url);
        if (count($parts) < 2) return null;

        $path = $parts[1];

        $segments = explode('/', $path);
        if (strpos($segments[0], 'v') === 0) {
            array_shift($segments);
        }

        $pathWithoutExtension = implode('/', $segments);
        $pathWithoutExtension = preg_replace('/\.[^.]+$/', '', $pathWithoutExtension);

        return $pathWithoutExtension;
    }
}