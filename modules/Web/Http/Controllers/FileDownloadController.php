<?php

namespace Modules\Web\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Core\Abstracts\BaseController;
use Modules\Core\Models\File;

class FileDownloadController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Private File Download mit RBAC-Integration
     */
    public function downloadPrivateFile(Request $request, $fileId)
    {
        // 1. Signed URL validieren
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired URL');
        }

        // 2. File finden
        $file = File::findOrFail($fileId);

        // 3. Nur private Files über diese Route
        if ($file->is_public) {
            return redirect(Storage::url($file->getStoragePath()));
        }

        // 4. Security Hash validieren
        $expectedHash = $file->getSecurityHash(auth()->user());
        if ($request->get('hash') !== $expectedHash) {
            abort(403, 'Invalid security token');
        }

        // 5. RBAC + Model-Berechtigung prüfen
        $this->authorizeFileAccess($file);

        // 6. File existiert?
        if (!$file->exists()) {
            abort(404, 'File not found');
        }

        // 7. Thumbnail-Request?
        if ($request->has('thumbnail')) {
            return $this->serveThumbnail($file, $request);
        }

        // 8. File ausliefern
        return $this->serveFile($file, $request->get('download') === '1');
    }

    /**
     * Thumbnail für Bilder generieren und ausliefern
     */
    protected function serveThumbnail(File $file, Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Nur für Bilder
        if (!$file->isImage()) {
            abort(400, __t('Thumbnails only available for images', 'Web'));
        }

        // Thumbnail-Parameter
        $width = min((int) $request->get('w', 200), 1000);   // Max 1000px
        $height = min((int) $request->get('h', 200), 1000);  // Max 1000px
        $quality = min((int) $request->get('q', 80), 100);   // Max 100%

        // Thumbnail-Pfad im gleichen Ordner wie Original
        $thumbnailPath = $file->getThumbnailPath($width, $height, $quality);

        // Thumbnail generieren falls nicht vorhanden
        if (!$file->thumbnailExists($width, $height, $quality)) {
            $this->generateThumbnail($file, $thumbnailPath, $width, $height, $quality);
        }

        // Thumbnail ausliefern
        return Storage::response($thumbnailPath, "thumb_{$file->file_name}", [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400', // 24h Cache
            'Expires' => now()->addDay()->format('D, d M Y H:i:s \G\M\T'),
        ]);
    }

    /**
     * Thumbnail generieren und im thumb/ Unterordner speichern
     */
    protected function generateThumbnail(File $file, string $thumbnailPath, int $width, int $height, int $quality): void
    {
        try {
            // thumb/ Verzeichnis erstellen falls nicht vorhanden
            $thumbDir = dirname($thumbnailPath);
            if (!Storage::exists($thumbDir)) {
                Storage::makeDirectory($thumbDir);
            }

            // Original-Bild laden
            $originalPath = $file->getStoragePath();
            $imageContent = Storage::get($originalPath);

            // Image-Resource erstellen
            $image = $this->createImageFromString($imageContent, $file->content_type);

            if (!$image) {
                throw new \Exception('Could not create image resource');
            }

            // Original-Dimensionen
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            // Thumbnail-Dimensionen berechnen (proportional)
            $dimensions = $this->calculateThumbnailDimensions(
                $originalWidth,
                $originalHeight,
                $width,
                $height
            );

            // Thumbnail erstellen
            $thumbnail = imagecreatetruecolor($dimensions['width'], $dimensions['height']);

            // Transparenz für PNG/GIF erhalten
            if (in_array($file->content_type, ['image/png', 'image/gif'])) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
                imagefill($thumbnail, 0, 0, $transparent);
            }

            // Bild skalieren
            imagecopyresampled(
                $thumbnail, $image,
                0, 0, 0, 0,
                $dimensions['width'], $dimensions['height'],
                $originalWidth, $originalHeight
            );

            // Thumbnail als JPEG speichern
            ob_start();
            imagejpeg($thumbnail, null, $quality);
            $thumbnailContent = ob_get_clean();

            // Im Storage speichern
            Storage::put($thumbnailPath, $thumbnailContent);

            // Thumbnail-Info in File metadata speichern
            $file->storeThumbnailInMetadata($width, $height, $quality, $thumbnailPath);

            // Memory cleanup
            imagedestroy($image);
            imagedestroy($thumbnail);

        } catch (\Exception $e) {
            // Fallback: Original-Bild verwenden
            Storage::copy($file->getStoragePath(), $thumbnailPath);

            // Auch bei Fallback in metadata speichern
            $file->storeThumbnailInMetadata($width, $height, $quality, $thumbnailPath);
        }
    }

    /**
     * Thumbnail-Dimensionen berechnen (proportional)
     */
    protected function calculateThumbnailDimensions(int $originalWidth, int $originalHeight, int $maxWidth, int $maxHeight): array
    {
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);

        return [
            'width' => (int) round($originalWidth * $ratio),
            'height' => (int) round($originalHeight * $ratio)
        ];
    }

    /**
     * Image-Resource aus String erstellen
     */
    protected function createImageFromString(string $imageContent, string $contentType)
    {
        switch ($contentType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromstring($imageContent);

            case 'image/png':
                return imagecreatefromstring($imageContent);

            case 'image/gif':
                return imagecreatefromstring($imageContent);

            case 'image/webp':
                return imagecreatefromstring($imageContent);

            default:
                return false;
        }
    }

    /**
     * RBAC + Model-basierte Berechtigung
     */
    protected function authorizeFileAccess(File $file): void
    {
        $user = auth()->user();

        // 1. Super Admin: Bypass alle Checks
        if ($user->isSuperAdmin()) {
            return;
        }

        // 2. File-Uploader: Immer erlaubt
        if ($file->uploaded_by === $user->id) {
            return;
        }

        // 3. RBAC-Permission prüfen (falls vorhanden)
        if ($this->checkRBACPermission($file, $user)) {
            return;
        }

        // 4. Kein Zugriff
        abort(403, 'Access denied to this file');
    }

    /**
     * RBAC-basierte Permission-Checks
     */
    protected function checkRBACPermission(File $file, $user): bool
    {
        // Spezifische Permission gefordert?
        $requiredPermission = request()->get('permission');
        if ($requiredPermission) {
            if (!$user->can($requiredPermission)) {
                abort(403, "Permission required: {$requiredPermission}");
            }
            return true;
        }

        // Generelle File-Download Permission
        if ($user->can('files.download')) {
            return true;
        }

        return false;
    }


    /**
     * File sicher ausliefern
     */
    protected function serveFile(File $file, bool $forceDownload = false): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => $file->content_type ?: 'application/octet-stream',
            'Content-Length' => $file->file_size,
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY'
        ];

        if ($forceDownload) {
            $headers['Content-Disposition'] = 'attachment; filename="' . $file->file_name . '"';
        } else {
            $headers['Content-Disposition'] = 'inline; filename="' . $file->file_name . '"';
        }

        return Storage::response($file->getStoragePath(), $file->file_name, $headers);
    }
}
