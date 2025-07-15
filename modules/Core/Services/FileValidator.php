<?php

namespace Modules\Core\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileValidator
{
    // =============================================
    // ERLAUBTE FILE-TYPES (SICHER)
    // =============================================

    /**
     * Sichere Dokument-Formate
     */
    public const SAFE_DOCUMENTS = [
        'pdf' => [
            'mime_types' => ['application/pdf'],
            'max_size' => 50 * 1024 * 1024, // 50MB
            'magic_bytes' => ['%PDF'],
            'description' => 'PDF Document'
        ],
        'txt' => [
            'mime_types' => ['text/plain'],
            'max_size' => 10 * 1024 * 1024, // 10MB
            'magic_bytes' => [],
            'description' => 'Text Document'
        ],
        'csv' => [
            'mime_types' => ['text/csv', 'text/plain', 'application/csv'],
            'max_size' => 25 * 1024 * 1024, // 25MB
            'magic_bytes' => [],
            'description' => 'CSV File'
        ],
        'docx' => [
            'mime_types' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'max_size' => 25 * 1024 * 1024, // 25MB
            'magic_bytes' => ['PK'], // ZIP-basiert
            'description' => 'Word Document'
        ],
        'xlsx' => [
            'mime_types' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'max_size' => 50 * 1024 * 1024, // 50MB
            'magic_bytes' => ['PK'], // ZIP-basiert
            'description' => 'Excel Spreadsheet'
        ],
        'pptx' => [
            'mime_types' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'max_size' => 100 * 1024 * 1024, // 100MB
            'magic_bytes' => ['PK'], // ZIP-basiert
            'description' => 'PowerPoint Presentation'
        ]
    ];

    /**
     * Sichere Bild-Formate
     */
    public const SAFE_IMAGES = [
        'jpg' => [
            'mime_types' => ['image/jpeg'],
            'max_size' => 10 * 1024 * 1024, // 10MB
            'magic_bytes' => ["\xFF\xD8\xFF"],
            'description' => 'JPEG Image'
        ],
        'jpeg' => [
            'mime_types' => ['image/jpeg'],
            'max_size' => 10 * 1024 * 1024, // 10MB
            'magic_bytes' => ["\xFF\xD8\xFF"],
            'description' => 'JPEG Image'
        ],
        'png' => [
            'mime_types' => ['image/png'],
            'max_size' => 10 * 1024 * 1024, // 10MB
            'magic_bytes' => ["\x89PNG\r\n\x1a\n"],
            'description' => 'PNG Image'
        ],
        'gif' => [
            'mime_types' => ['image/gif'],
            'max_size' => 5 * 1024 * 1024, // 5MB
            'magic_bytes' => ['GIF87a', 'GIF89a'],
            'description' => 'GIF Image'
        ],
        'webp' => [
            'mime_types' => ['image/webp'],
            'max_size' => 10 * 1024 * 1024, // 10MB
            'magic_bytes' => ['RIFF', 'WEBP'],
            'description' => 'WebP Image'
        ]
    ];

    /**
     * GEFÄHRLICHE Formate (NICHT erlaubt)
     */
    public const DANGEROUS_TYPES = [
        // Ausführbare Dateien
        'exe', 'bat', 'cmd', 'com', 'scr', 'msi', 'dll', 'sys',

        // Scripts
        'js', 'php', 'py', 'sh', 'vbs', 'ps1', 'rb', 'pl',

        // Legacy Office (Makro-fähig)
        'doc', 'xls', 'ppt', 'xlsm', 'docm', 'pptm',

        // Archive (können Malware verstecken)
        'zip', 'rar', '7z', 'tar', 'gz', 'bz2',

        // System/Config-Dateien
        'reg', 'ini', 'cfg', 'conf',

        // Andere gefährliche Formate
        'swf', 'jar', 'class', 'deb', 'rpm'
    ];

    // =============================================
    // VALIDATION METHODS
    // =============================================

    /**
     * Hauptvalidierung für hochgeladene Datei
     */
    public static function validate(UploadedFile $file): array
    {
        $result = [
            'valid' => false,
            'errors' => [],
            'warnings' => [],
            'file_info' => []
        ];

        try {
            // 1. Basic File Info sammeln
            $extension = strtolower($file->getClientOriginalExtension());
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            $originalName = $file->getClientOriginalName();

            $result['file_info'] = [
                'extension' => $extension,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'original_name' => $originalName,
                'human_size' => self::formatBytes($fileSize)
            ];

            // 2. Gefährliche Dateitypen prüfen
            if (in_array($extension, self::DANGEROUS_TYPES)) {
                $result['errors'][] = "File type '{$extension}' is not allowed for security reasons";
                return $result;
            }

            // 3. Erlaubte Dateitypen prüfen
            $allowedTypes = array_merge(self::SAFE_DOCUMENTS, self::SAFE_IMAGES);

            if (!isset($allowedTypes[$extension])) {
                $result['errors'][] = "File type '{$extension}' is not supported";
                $result['warnings'][] = "Supported types: " . implode(', ', array_keys($allowedTypes));
                return $result;
            }

            $typeConfig = $allowedTypes[$extension];

            // 4. MIME-Type validieren
            if (!in_array($mimeType, $typeConfig['mime_types'])) {
                $result['errors'][] = "Invalid MIME type '{$mimeType}' for {$extension} file";
                return $result;
            }

            // 5. Dateigröße prüfen
            if ($fileSize > $typeConfig['max_size']) {
                $humanMaxSize = self::formatBytes($typeConfig['max_size']);
                $result['errors'][] = "File size ({$result['file_info']['human_size']}) exceeds maximum allowed size ({$humanMaxSize}) for {$extension} files";
                return $result;
            }

            // 6. Magic Bytes prüfen (File-Header)
            if (!empty($typeConfig['magic_bytes'])) {
                if (!self::validateMagicBytes($file, $typeConfig['magic_bytes'])) {
                    $result['errors'][] = "File header does not match expected format for {$extension} file";
                    return $result;
                }
            }

            // 7. Zusätzliche Content-Validierung
            $contentValidation = self::validateFileContent($file, $extension);
            if (!$contentValidation['valid']) {
                $result['errors'] = array_merge($result['errors'], $contentValidation['errors']);
                return $result;
            }

            // 8. Alles OK
            $result['valid'] = true;
            $result['file_info']['type_description'] = $typeConfig['description'];

        } catch (\Exception $e) {
            $result['errors'][] = "Validation error: " . $e->getMessage();
            Log::error("File validation error", [
                'file' => $originalName ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Magic Bytes (File-Header) validieren
     */
    private static function validateMagicBytes(UploadedFile $file, array $expectedBytes): bool
    {
        try {
            $handle = fopen($file->getRealPath(), 'rb');
            if (!$handle) {
                return false;
            }

            $header = fread($handle, 32); // Erste 32 Bytes lesen
            fclose($handle);

            foreach ($expectedBytes as $expected) {
                if (str_starts_with($header, $expected)) {
                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            Log::warning("Magic bytes validation failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Content-spezifische Validierung
     */
    private static function validateFileContent(UploadedFile $file, string $extension): array
    {
        $result = ['valid' => true, 'errors' => []];

        try {
            switch ($extension) {
                case 'pdf':
                    $result = self::validatePdfContent($file);
                    break;

                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                case 'webp':
                    $result = self::validateImageContent($file);
                    break;

                case 'txt':
                case 'csv':
                    $result = self::validateTextContent($file);
                    break;

                case 'docx':
                case 'xlsx':
                case 'pptx':
                    $result = self::validateOfficeContent($file);
                    break;
            }

        } catch (\Exception $e) {
            $result = [
                'valid' => false,
                'errors' => ["Content validation failed: " . $e->getMessage()]
            ];
        }

        return $result;
    }

    /**
     * PDF Content validieren
     */
    private static function validatePdfContent(UploadedFile $file): array
    {
        $result = ['valid' => true, 'errors' => []];

        try {
            $content = file_get_contents($file->getRealPath());

            // Basic PDF struktur prüfen
            if (!str_contains($content, '%PDF-')) {
                $result['valid'] = false;
                $result['errors'][] = "Invalid PDF structure";
                return $result;
            }

            // Verdächtige JavaScript/ActionScript prüfen
            $suspiciousPatterns = [
                '/\/JavaScript/i',
                '/\/JS/i',
                '/\/Action/i',
                '/<script/i',
                '/eval\(/i'
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $result['valid'] = false;
                    $result['errors'][] = "PDF contains potentially dangerous JavaScript content";
                    break;
                }
            }

        } catch (\Exception $e) {
            $result['valid'] = false;
            $result['errors'][] = "PDF validation failed";
        }

        return $result;
    }

    /**
     * Image Content validieren
     */
    private static function validateImageContent(UploadedFile $file): array
    {
        $result = ['valid' => true, 'errors' => []];

        try {
            // Versuche das Bild zu laden (validiert Format)
            $imageInfo = getimagesize($file->getRealPath());

            if ($imageInfo === false) {
                $result['valid'] = false;
                $result['errors'][] = "Invalid or corrupted image file";
                return $result;
            }

            // Bildgröße-Limits prüfen
            $maxDimension = 8000; // 8000px max
            if ($imageInfo[0] > $maxDimension || $imageInfo[1] > $maxDimension) {
                $result['valid'] = false;
                $result['errors'][] = "Image dimensions too large (max {$maxDimension}px)";
            }

        } catch (\Exception $e) {
            $result['valid'] = false;
            $result['errors'][] = "Image validation failed";
        }

        return $result;
    }

    /**
     * Text Content validieren
     */
    private static function validateTextContent(UploadedFile $file): array
    {
        $result = ['valid' => true, 'errors' => []];

        try {
            $content = file_get_contents($file->getRealPath());

            // Auf verdächtige Script-Tags prüfen
            $suspiciousPatterns = [
                '/<script/i',
                '/<\?php/i',
                '/<%/i',
                '/javascript:/i',
                '/vbscript:/i'
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $result['valid'] = false;
                    $result['errors'][] = "Text file contains potentially dangerous script content";
                    break;
                }
            }

        } catch (\Exception $e) {
            $result['valid'] = false;
            $result['errors'][] = "Text validation failed";
        }

        return $result;
    }

    /**
     * Office Content validieren (docx, xlsx, pptx)
     */
    private static function validateOfficeContent(UploadedFile $file): array
    {
        $result = ['valid' => true, 'errors' => []];

        try {
            // Office-Dateien sind ZIP-Archive - grundlegende ZIP-Struktur prüfen
            $zip = new \ZipArchive();
            $res = $zip->open($file->getRealPath());

            if ($res !== TRUE) {
                $result['valid'] = false;
                $result['errors'][] = "Invalid Office document structure";
                return $result;
            }

            // Auf verdächtige Dateien im Archiv prüfen
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($extension, ['exe', 'bat', 'cmd', 'vbs', 'js'])) {
                    $result['valid'] = false;
                    $result['errors'][] = "Office document contains suspicious embedded file: {$filename}";
                    break;
                }
            }

            $zip->close();

        } catch (\Exception $e) {
            $result['valid'] = false;
            $result['errors'][] = "Office document validation failed";
        }

        return $result;
    }

    // =============================================
    // HELPER METHODS
    // =============================================

    /**
     * Bytes in menschenlesbare Größe formatieren
     */
    public static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Alle erlaubten Dateitypen abrufen
     */
    public static function getAllowedExtensions(): array
    {
        return array_keys(array_merge(self::SAFE_DOCUMENTS, self::SAFE_IMAGES));
    }

    /**
     * Erlaubte MIME-Types abrufen
     */
    public static function getAllowedMimeTypes(): array
    {
        $mimeTypes = [];
        $allTypes = array_merge(self::SAFE_DOCUMENTS, self::SAFE_IMAGES);

        foreach ($allTypes as $typeConfig) {
            $mimeTypes = array_merge($mimeTypes, $typeConfig['mime_types']);
        }

        return array_unique($mimeTypes);
    }

    /**
     * Max. Dateigröße für bestimmten Typ abrufen
     */
    public static function getMaxSizeForType(string $extension): ?int
    {
        $allTypes = array_merge(self::SAFE_DOCUMENTS, self::SAFE_IMAGES);
        return $allTypes[$extension]['max_size'] ?? null;
    }

    /**
     * Ist Dateityp erlaubt?
     */
    public static function isAllowedType(string $extension): bool
    {
        return in_array(strtolower($extension), self::getAllowedExtensions());
    }

    /**
     * Validierungs-Regeln für Laravel Request
     */
    public static function getValidationRules(): array
    {
        $maxSize = max(array_column(array_merge(self::SAFE_DOCUMENTS, self::SAFE_IMAGES), 'max_size'));
        $allowedExtensions = implode(',', self::getAllowedExtensions());
        $allowedMimes = implode(',', self::getAllowedMimeTypes());

        return [
            'required',
            'file',
            'max:' . ($maxSize / 1024), // Laravel erwartet KB
            'mimes:' . $allowedMimes,
            function ($attribute, $value, $fail) {
                if ($value instanceof UploadedFile) {
                    $validation = self::validate($value);
                    if (!$validation['valid']) {
                        $fail(implode(', ', $validation['errors']));
                    }
                }
            }
        ];
    }
}
