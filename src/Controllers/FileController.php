<?php

namespace App\Controllers;

use App\UploadType;
use Woodlands\Core\Exceptions\AppException;

final class FileController
{
    public const MAX_FILE_SIZE = 1 * 1024 * 1024; // 1MB

    public const DIR_PROFILE_IMAGE = __DIR__ . "/../../public/uploads/profile_images";

    private static function createDirIfNotExists(string $dir): void
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    public static function saveFile(string $filename, string $tmpName, UploadType $type): string
    {
        $dir = match ($type) {
            UploadType::ProfileImage => self::DIR_PROFILE_IMAGE,
        };

        self::createDirIfNotExists($dir);
        $destination = $dir . "/$filename";

        if (!move_uploaded_file($tmpName, $destination)) {
            throw new AppException("Failed to save file");
        }

        return $destination;
    }

    public static function fileNameFor(int $userId, string $extension, UploadType $type): string
    {
        $extension = match ($extension) {
            "" => "png",
            "jpeg" => "jpg",
            default => strtolower($extension),
        };

        return match ($type) {
            UploadType::ProfileImage => "pp_$userId.$extension",
        };
    }
}
