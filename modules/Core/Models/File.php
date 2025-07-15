<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Modules\Core\Services\FileValidator;

class File extends Model
{
    protected $table = 'files';

    protected $fillable = [
        'disk_name',
        'file_name',
        'file_size',
        'content_type',
        'title',
        'description',
        'field',
        'model_type',
        'model_id',
        'uploaded_by',
        'is_public',
        'sort_order',
        'metadata'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'is_public' => false,
        'sort_order' => 0
    ];

    // =============================================
    // SECURE FILE UPLOAD
    // =============================================

    /**
     * Sichere Datei-Upload mit Validierung
     */
    public static function createFromUpload(
        UploadedFile $uploadedFile,
        Model $model,
        ?string $field = null,
        string $type,
        ?array $additionalData = []
    ): self {
        // 1. Security-Validierung
        $validation = FileValidator::validate($uploadedFile);
        if (!$validation['valid']) {
            throw new \InvalidArgumentException('File validation failed: ' . implode(', ', $validation['errors']));
        }

        // 2. Eindeutigen disk_name generieren
        $diskName = self::generateDiskName($uploadedFile->getClientOriginalName(), $type, $model);

        // 3. Datei speichern
        $storedPath = $uploadedFile->storeAs( '', $diskName);
        if (!$storedPath) {
            throw new \RuntimeException('Failed to store uploaded file');
        }

        // 4. File-Record erstellen
        $fileData = array_merge([
            'disk_name' => $diskName,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_size' => $uploadedFile->getSize(),
            'content_type' => $uploadedFile->getMimeType(),
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'uploaded_by' => auth()->id(),
            'field' => $field,
            'metadata' => $validation['file_info']
        ], $additionalData);

        return self::create($fileData);
    }

    /**
     * Bulk-Upload für mehrere Dateien
     */
    public static function createMultipleFromUpload(
        array $uploadedFiles,
        Model $model,
        ?string $field = null,
        ?array $additionalData = []
    ): array {
        $createdFiles = [];
        $errors = [];

        foreach ($uploadedFiles as $index => $uploadedFile) {
            try {
                $file = self::createFromUpload($uploadedFile, $model, $field, $additionalData);
                $file->sort_order = $index + 1;
                $file->save();
                $createdFiles[] = $file;
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $uploadedFile->getClientOriginalName(),
                    'error' => $e->getMessage()
                ];
            }
        }

        if (!empty($errors)) {
            throw new \RuntimeException('Some files failed to upload: ' . json_encode($errors));
        }

        return $createdFiles;
    }

    // =============================================
    // FILE TYPE CONSTANTS (aus FileValidator)
    // =============================================

    /**
     * Erlaubte Dateitypen abrufen
     */
    public static function getAllowedExtensions(): array
    {
        return FileValidator::getAllowedExtensions();
    }

    /**
     * Erlaubte MIME-Types abrufen
     */
    public static function getAllowedMimeTypes(): array
    {
        return FileValidator::getAllowedMimeTypes();
    }

    /**
     * Ist Dateityp erlaubt?
     */
    public static function isAllowedType(string $extension): bool
    {
        return FileValidator::isAllowedType($extension);
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * File gehört zu einem User (Uploader)
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Polymorphic Relationship zu beliebigen Models
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    // =============================================
    // SCOPES
    // =============================================

    /**
     * Files für bestimmtes Model
     */
    public function scopeWhereModel(Builder $query, Model $model): Builder
    {
        return $query->where('model_type', get_class($model))
            ->where('model_id', $model->id);
    }

    /**
     * Files für bestimmte Model-Klasse und ID
     */
    public function scopeWhereModelType(Builder $query, string $modelType, int $modelId): Builder
    {
        return $query->where('model_type', $modelType)
            ->where('model_id', $modelId);
    }

    /**
     * Files für bestimmte Kategorie/Field
     */
    public function scopeWhereField(Builder $query, string $field): Builder
    {
        return $query->where('field', $field);
    }

    /**
     * Nur öffentliche Files
     */
    public function scopeWherePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Files nach Content-Type filtern
     */
    public function scopeWhereContentType(Builder $query, string $contentType): Builder
    {
        return $query->where('content_type', $contentType);
    }

    /**
     * Nur Bilder
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('content_type', 'like', 'image/%');
    }

    /**
     * Nur PDFs
     */
    public function scopePdfs(Builder $query): Builder
    {
        return $query->where('content_type', 'application/pdf');
    }

    /**
     * Sortiert nach sort_order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    // =============================================
    // FILE OPERATIONS
    // =============================================

    /**
     * Vollständiger Dateipfad im Storage
     */
    public function getStoragePath(): string
    {
        return $this->disk_name;
    }

    /**
     * Prüft ob Datei physisch existiert
     */
    public function exists(): bool
    {
        return Storage::exists($this->getStoragePath());
    }

    /**
     * Datei-Inhalt lesen
     */
    public function getContents(): string
    {
        if (!$this->exists()) {
            throw new \Exception("File not found: {$this->disk_name}");
        }

        return Storage::get($this->getStoragePath());
    }

    /**
     * Alle Thumbnails dieses Files löschen (physisch und aus metadata)
     */
    public function deleteThumbnails(): int
    {
        $deletedCount = 0;
        $thumbnails = $this->getThumbnailsFromMetadata();

        foreach ($thumbnails as $thumbnail) {
            if (isset($thumbnail['path']) && Storage::exists($thumbnail['path'])) {
                Storage::delete($thumbnail['path']);
                $deletedCount++;
            }
        }

        // Thumbnails aus metadata entfernen
        $metadata = $this->metadata ?? [];
        unset($metadata['thumbnails']);
        $this->update(['metadata' => $metadata]);

        // Leeres thumb/ Verzeichnis auch löschen
        $originalPath = $this->getStoragePath();
        $thumbDir = dirname($originalPath) . '/thumb';
        if (Storage::exists($thumbDir) && empty(Storage::files($thumbDir))) {
            Storage::deleteDirectory($thumbDir);
        }

        return $deletedCount;
    }

    /**
     * Datei löschen (physisch und DB-Record)
     */
    public function deleteFile(): bool
    {
        try {
            // Alle Thumbnais löschen
            $this->deleteThumbnails();

            // Physische Datei löschen
            if ($this->exists()) {
                Storage::delete($this->getStoragePath());
            }

            // DB-Record löschen
            return $this->delete();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * URL zur Datei generieren
     */
    public function getUrl(?string $permission = null, string $route = 'files.private', $hoursValid = 24, bool $download = false): string
    {
        if ($this->is_public) {
            return Storage::url($this->getStoragePath());
        }

        return self::getSignedUrl($route, $permission, $hoursValid, $download);
    }

    public function getSignedUrl(string $route, string $permission, int $hoursValid, bool $download): string
    {
        $user = auth()->user();
        if (!$permission || !$user->can($permission)) {
            abort(403, __t("Permission denied or required", "Web"));
        }

        $params = [
            'file' => $this->id,
            'hash' => $this->getSecurityHash($user),
        ];

        if ($download) {
            $params['download'] = true;
        }

        return URL::temporarySignedRoute(
            $route,
            now()->addHours($hoursValid),
            $params
        );
    }

    /**
     * Download-URL (forciert Download statt Inline)
     */
    public function getDownloadUrl(string $permission, string $route = 'files.private', int $hoursValid = 24): string
    {
        return self::getSignedUrl($route, $permission, $hoursValid, true);
    }

    /**
     * Thumbnail-Pfad im gleichen Ordner wie Original-Bild generieren
     */
    public function getThumbnailPath(int $width, int $height, int $quality = 80): string
    {
        $originalPath = $this->getStoragePath();
        $pathInfo = pathinfo($originalPath);

        // thumb/ Unterordner im gleichen Verzeichnis
        $thumbDir = $pathInfo['dirname'] . '/thumb';
        $filename = $pathInfo['filename'] . "_{$width}x{$height}_q{$quality}.jpg";

        return $thumbDir . '/' . $filename;
    }

    /**
     * Prüfen ob Thumbnail bereits existiert
     */
    public function thumbnailExists(int $width, int $height, int $quality = 80): bool
    {
        return Storage::exists($this->getThumbnailPath($width, $height, $quality));
    }

    /**
     * Thumbnail-URL mit verbesserter Signierung
     */
    public function getThumbnailUrl(string $permission, int $width = 200, int $height = 200, int $quality = 80, int $hoursValid = 24): string
    {
        if (!$this->isImage()) {
            return '';
        }

        $user = auth()->user();

        if (!$permission || !$user->can($permission)) {
            abort(403, __t("Permission denied or required", "Web"));
        }

        $params = [
            'file' => $this->id,
            'hash' => $this->getSecurityHash($user),
            'thumbnail' => '1',
            'w' => $width,
            'h' => $height,
            'q' => $quality
        ];

        return URL::temporarySignedRoute(
            'files.private',
            now()->addHours($hoursValid),
            $params
        );
    }

    /**
     * Thumbnail-Info in metadata speichern
     */
    public function storeThumbnailInMetadata(int $width, int $height, int $quality, string $thumbnailPath): void
    {
        $metadata = $this->metadata ?? [];

        // Thumbnails-Array initialisieren falls nicht vorhanden
        if (!isset($metadata['thumbnails'])) {
            $metadata['thumbnails'] = [];
        }

        // Thumbnail-Info hinzufügen
        $thumbnailKey = "{$width}x{$height}_q{$quality}";
        $metadata['thumbnails'][$thumbnailKey] = [
            'width' => $width,
            'height' => $height,
            'quality' => $quality,
            'path' => $thumbnailPath,
            'created_at' => now()->toDateTimeString()
        ];

        // Metadata aktualisieren
        $this->update(['metadata' => $metadata]);
    }

    /**
     * Alle Thumbnails aus metadata abrufen
     */
    public function getThumbnailsFromMetadata(): array
    {
        $metadata = $this->metadata ?? [];
        return $metadata['thumbnails'] ?? [];
    }

    /**
     * Spezifisches Thumbnail aus metadata abrufen
     */
    public function getThumbnailFromMetadata(int $width, int $height, int $quality): ?array
    {
        $thumbnails = $this->getThumbnailsFromMetadata();
        $thumbnailKey = "{$width}x{$height}_q{$quality}";

        return $thumbnails[$thumbnailKey] ?? null;
    }

    public function getSecurityHash($user): string
    {
        // Hash aus File-ID + User-ID + Secret für zusätzliche Sicherheit
        return hash('sha256', $this->id . $user->id . config('app.key'));
    }

    /**
     * Öffentliche URL (nur für öffentliche Dateien)
     */
    public function getPublicUrl(): ?string
    {
        return $this->is_public ? Storage::url($this->getStoragePath()) : null;
    }

    // =============================================
    // FILE INFO HELPERS
    // =============================================

    /**
     * Menschenlesbare Dateigröße
     */
    public function getHumanFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Ist es ein Bild?
     */
    public function isImage(): bool
    {
        return str_starts_with($this->content_type, 'image/');
    }

    /**
     * Ist es ein PDF?
     */
    public function isPdf(): bool
    {
        return $this->content_type === 'application/pdf';
    }

    /**
     * Ist es ein Video?
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->content_type, 'video/');
    }

    /**
     * Ist es ein Audio?
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->content_type, 'audio/');
    }

    /**
     * Datei-Extension ermitteln
     */
    public function getExtension(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Datei-Name ohne Extension
     */
    public function getBaseName(): string
    {
        return pathinfo($this->file_name, PATHINFO_FILENAME);
    }

    // =============================================
    // SECURITY & ACCESS CONTROL
    // =============================================

    /**
     * Kann User auf diese Datei zugreifen?
     */
    public function canAccess(?User $user = null): bool
    {
        // Öffentliche Dateien: Immer erlaubt
        if ($this->is_public) {
            return true;
        }

        // Kein User: Kein Zugriff auf private Dateien
        if (!$user) {
            return false;
        }

        // Super Admin: Immer erlaubt
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Uploader: Immer erlaubt
        if ($this->uploaded_by === $user->id) {
            return true;
        }

        // Model-basierte Berechtigung prüfen
        if ($this->model) {
            // Wenn Model ein "canAccessFiles" Method hat
            if (method_exists($this->model, 'canAccessFiles')) {
                return $this->model->canAccessFiles($user);
            }

            // Standard: Wenn User das Model bearbeiten kann
            if (method_exists($this->model, 'canEdit')) {
                return $this->model->canEdit($user);
            }
        }

        return false;
    }

    // =============================================
    // STATIC HELPER METHODS
    // =============================================

    /**
     * Generiere eindeutigen disk_name
     */
    public static function generateDiskName(string $originalName, string $type, Model $model, int $levels = 3, int $segmentLength = 2): string
    {
        $modelClass = class_basename($model);
        $modelName = Str::snake($modelClass);
        $modelId = $model->getKey();
        $datePath = now()->format('Y/m/d');

        $hash = bin2hex(random_bytes(20));

        $segments = [];
        for ($i = 0; $i < $levels; $i++) {
            $segments[] = substr($hash, $i * $segmentLength, $segmentLength);
        }

        $shardedPath = implode('/', $segments);
        $modelSlug = "{$modelName}-{$modelId}";

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $uuid = Str::uuid();

        if (settings('file_upload_sharding_enabled')){
            return $extension ? "uploads/{$type}/{$datePath}/{$shardedPath}/{$modelSlug}/{$uuid}.{$extension}" : "uploads/{$type}/{$datePath}/{$shardedPath}/{$modelSlug}/{$uuid}";
        } else {
            return $extension ? "uploads/{$type}/{$datePath}/{$modelSlug}/{$uuid}.{$extension}" : "uploads/{$type}/{$datePath}/{$modelSlug}/{$uuid}";
        }
    }

    /**
     * MIME-Type von Datei-Extension ermitteln (mit Fallback)
     */
    public static function getMimeTypeFromExtension(string $filename): string
    {
        // Zuerst FileValidator prüfen (sicherere Version)
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Wenn erlaubt, aus sicherer Liste nehmen
        if (self::isAllowedType($extension)) {
            $allTypes = array_merge(FileValidator::SAFE_DOCUMENTS, FileValidator::SAFE_IMAGES);
            if (isset($allTypes[$extension])) {
                return $allTypes[$extension]['mime_types'][0]; // Ersten MIME-Type nehmen
            }
        }

        // Fallback zu Standard-MIME-Types (nur für erlaubte Typen)
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    // =============================================
    // ELOQUENT EVENTS
    // =============================================

    protected static function boot()
    {
        parent::boot();

        // Beim Erstellen: Security-Validierung
        static::creating(function (File $file) {
            // Prüfe ob Dateityp erlaubt ist
            if (!self::isAllowedType($file->getExtension())) {
                throw new \InvalidArgumentException("File type '{$file->getExtension()}' is not allowed");
            }

            // Prüfe MIME-Type
            if (!in_array($file->content_type, self::getAllowedMimeTypes())) {
                throw new \InvalidArgumentException("MIME type '{$file->content_type}' is not allowed");
            }
        });

        // Beim Löschen des Models: Physische Datei auch löschen
        static::deleting(function (File $file) {
            if ($file->exists()) {
                Storage::delete($file->getStoragePath());
            }
        });
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================

    /**
     * Title Accessor: Fallback zu file_name
     */
    public function getTitleAttribute($value): string
    {
        return $value ?: $this->file_name;
    }

    /**
     * Human File Size Accessor
     */
    public function getHumanFileSizeAttribute(): string
    {
        return $this->getHumanFileSize();
    }

    /**
     * File URL Accessor
     */
    public function getUrlAttribute(): string
    {
        return $this->getUrl();
    }

    /**
     * Is Image Accessor
     */
    public function getIsImageAttribute(): bool
    {
        return $this->isImage();
    }

    /**
     * Prüft ob Datei den Sicherheitsstandards entspricht
     */
    public function isSecure(): bool
    {
        // Extension prüfen
        if (!self::isAllowedType($this->getExtension())) {
            return false;
        }

        // MIME-Type prüfen
        if (!in_array($this->content_type, self::getAllowedMimeTypes())) {
            return false;
        }

        // Dateigröße prüfen
        $maxSize = FileValidator::getMaxSizeForType($this->getExtension());
        if ($maxSize && $this->file_size > $maxSize) {
            return false;
        }

        return true;
    }

    /**
     * File neu validieren (falls Security-Rules geändert wurden)
     */
    public function revalidate(): array
    {
        if (!$this->exists()) {
            return ['valid' => false, 'errors' => ['File does not exist on disk']];
        }

        // Temporäre UploadedFile erstellen für Validierung
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        file_put_contents($tempPath, $this->getContents());

        $uploadedFile = new UploadedFile(
            $tempPath,
            $this->file_name,
            $this->content_type,
            null,
            true
        );

        $validation = FileValidator::validate($uploadedFile);

        fclose($tempFile);

        return $validation;
    }
}
