<?php

namespace App\Services;

// Week 4 & 8: Image upload, resize, watermark with GD
class ImageService
{
    private string $uploadDir;
    private int $thumbSize = 150;
    private array $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private int $maxSize = 5 * 1024 * 1024; // 5MB

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../storage/uploads/profiles/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload failed.'];
        }
        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'error' => 'File too large. Max 5MB allowed.'];
        }

        // Week 5: MIME check via finfo (not extension)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $this->allowedMimes, true)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, WebP allowed.'];
        }

        // Week 8: Secure filename using random_bytes
        $ext      = $this->mimeToExt($mime);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $this->uploadDir . $filename;

        if (!$this->resizeAndSave($file['tmp_name'], $destPath, $mime)) {
            return ['success' => false, 'error' => 'Failed to process image.'];
        }

        return ['success' => true, 'filename' => $filename];
    }

    // Week 8: GD resize to 150x150 (center crop)
    private function resizeAndSave(string $src, string $dest, string $mime): bool
    {
        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($src),
            'image/png'  => imagecreatefrompng($src),
            'image/gif'  => imagecreatefromgif($src),
            'image/webp' => imagecreatefromwebp($src),
            default      => false,
        };

        if (!$source) return false;

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $size = $this->thumbSize;

        // Calculate center crop
        $ratio   = max($size / $srcW, $size / $srcH);
        $newW    = (int)round($srcW * $ratio);
        $newH    = (int)round($srcH * $ratio);
        $offsetX = (int)(($newW - $size) / 2);
        $offsetY = (int)(($newH - $size) / 2);

        $thumb = imagecreatetruecolor($size, $size);

        // Preserve transparency for PNG/GIF
        if (in_array($mime, ['image/png', 'image/gif'], true)) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
            imagefilledrectangle($thumb, 0, 0, $size, $size, $transparent);
        }

        $temp = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($temp, $source, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
        imagecopy($thumb, $temp, 0, 0, $offsetX, $offsetY, $size, $size);
        imagedestroy($temp);

        // Week 8: GD watermark
        $this->addWatermark($thumb);

        $result = match ($mime) {
            'image/jpeg' => imagejpeg($thumb, $dest, 85),
            'image/png'  => imagepng($thumb, $dest),
            'image/gif'  => imagegif($thumb, $dest),
            'image/webp' => imagewebp($thumb, $dest, 85),
            default      => false,
        };

        imagedestroy($source);
        imagedestroy($thumb);
        return (bool)$result;
    }

    // Week 8: GD watermark using imagestring()
    private function addWatermark(\GdImage $image): void
    {
        $color = imagecolorallocatealpha($image, 255, 255, 255, 80);
        imagestring($image, 1, 5, $this->thumbSize - 12, 'SRS', $color);
    }

    public function delete(string $filename): void
    {
        $path = $this->uploadDir . basename($filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function getUrl(string $filename): string
    {
        return url('storage/uploads/profiles/' . urlencode(basename($filename)));
    }

    private function mimeToExt(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
    }
}