<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Modules\Core\Models\File;

trait HasFiles
{
    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Alle Files dieses Models
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'model', 'model_type', 'model_id')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    /**
     * Files für bestimmtes Field
     */
    public function filesForField(string $field): MorphMany
    {
        return $this->files()->where('field', $field);
    }

    /**
     * Einzelnes Bild (field = 'image')
     */

    public function image(): MorphOne
    {
        return $this->morphOne(File::class, 'model', 'model_type', 'model_id')
            ->where('field', 'image');
    }

    /**
     * Mehrere Bilder (field = 'images')
     */
    public function images(): MorphMany
    {
        return $this->filesForField('images');
    }

    /**
     * Einzelnes Dokument (field = 'document')
     */
    public function document(): MorphOne
    {
        return $this->morphOne(File::class, 'model', 'model_type', 'model_id')
            ->where('field', 'document');
    }

    /**
     * Mehrere Dokumente (field = 'documents')
     */
    public function documents(): MorphMany
    {
        return $this->filesForField('documents');
    }

    /**
     * Anhänge/Attachments (field = 'attachments')
     */
    public function attachments(): MorphMany
    {
        return $this->filesForField('attachments');
    }

    // =============================================
    // FILE UPLOAD METHODS
    // =============================================

    /**
     * Einzelne Datei hinzufügen
     */
    public function addFile(
        UploadedFile $uploadedFile,
        ?string $field = null,
        string $type = null,
        array $additionalData = []
    ): File {
        return File::createFromUpload($uploadedFile, $this, $field, $type, $additionalData);
    }

    /**
     * Mehrere Dateien hinzufügen (Bulk-Upload)
     */
    public function addFiles(
        array $uploadedFiles,
        ?string $field = null,
        array $additionalData = []
    ): array {
        return File::createMultipleFromUpload($uploadedFiles, $this, $field, $additionalData);
    }

    /**
     * File für bestimmtes Field ersetzen (alte löschen, neue hochladen)
     */
    public function replaceFile(
        UploadedFile $uploadedFile,
        string $field,
        string $type = null,
        array $additionalData = []
    ): File {
        // Alte Files löschen
        $this->removeFilesForField($field);

        // Neue Datei hinzufügen
        return $this->addFile($uploadedFile, $field, $type, $additionalData);
    }

    /**
     * Bild für bestimmtes Field ersetzen (alte löschen, neue hochladen)
     */
    public function replaceImage(
        UploadedFile $uploadedFile,
        string $field,
        string $type,
        array $additionalData = []
    ): File {
        return $this->replaceFile($uploadedFile, $field, $type, $additionalData);
    }

    /**
     * Hauptbild setzen (ersetzt vorhandenes Image im 'image' field)
     */
    public function setImage(UploadedFile $uploadedFile, string $field, string $type, array $additionalData = []): File
    {
        return $this->replaceFile($uploadedFile, $field, $type, $additionalData);
    }

    // =============================================
    // FILE RETRIEVAL METHODS
    // =============================================

    /**
     * Erstes Bild abrufen (aus 'image' field)
     */
    public function getImage(): ?File
    {
        return $this->image()->first();
    }

    /**
     * Bild-URL abrufen (mit Fallback)
     */
    public function getImageUrl(?string $fallback = null): ?string
    {
        $image = $this->getImage();
        return $image ? $image->getUrl() : $fallback;
    }

    /**
     * Hat dieses Model ein Hauptbild?
     */
    public function hasImage(): bool
    {
        return $this->image()->exists();
    }

    /**
     * Alle Bilder abrufen (aus 'images' field + image-Content-Types)
     */
    public function getAllImages(): \Illuminate\Database\Eloquent\Collection
    {
        // Bilder aus 'images' field + alle Files mit image/* Content-Type
        return $this->files()
            ->where(function($query) {
                $query->where('field', 'images')
                    ->orWhere('field', 'image')
                    ->orWhere('content_type', 'like', 'image/%');
            })
            ->get();
    }

    /**
     * Erstes Dokument abrufen (aus 'document' field)
     */
    public function getDocument(): ?File
    {
        return $this->document()->first();
    }

    /**
     * Alle Dokumente abrufen (aus 'documents' + 'document' fields)
     */
    public function getAllDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->files()
            ->whereIn('field', ['document', 'documents'])
            ->get();
    }


    /**
     * Nur Bilder abrufen (alle image/* Content-Types)
     */
    public function getImages(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->files()->where('content_type', 'like', 'image/%')->get();
    }

    /**
     * Nur PDFs abrufen
     */
    public function getPdfs(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->files()->where('content_type', 'application/pdf')->get();
    }

    /**
     * Nur Office-Dokumente abrufen
     */
    public function getOfficeDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->files()->whereIn('content_type', [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv'
        ])->get();
    }

    /**
     * Files für bestimmtes Field abrufen
     */
    public function getFilesForField(string $field): \Illuminate\Database\Eloquent\Collection
    {
        return $this->filesForField($field)->get();
    }

    /**
     * Öffentliche Files abrufen
     */
    public function getPublicFiles(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->files()->where('is_public', true)->get();
    }

    /**
     * Private Files abrufen
     */
    public function getPrivateFiles(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->files()->where('is_public', false)->get();
    }

    // =============================================
    // FILE MANAGEMENT METHODS
    // =============================================

    /**
     * File löschen (nach ID)
     */
    public function removeFile(int $fileId): bool
    {
        $file = $this->files()->find($fileId);
        return $file ? $file->deleteFile() : false;
    }

    /**
     * Alle Files für bestimmtes Field löschen
     */
    public function removeFilesForField(string $field): int
    {
        $files = $this->filesForField($field)->get();
        $deletedCount = 0;

        foreach ($files as $file) {
            if ($file->deleteFile()) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Hauptbild löschen (aus 'image' field)
     */
    public function removeImage(): bool
    {
        return $this->removeFilesForField('image') > 0;
    }

    /**
     * Alle Bilder löschen (aus 'images' field)
     */
    public function removeImages(): int
    {
        return $this->removeFilesForField('images');
    }

    /**
     * Alle Dokumente löschen (aus 'documents' + 'document' fields)
     */
    public function removeDocuments(): int
    {
        $documentsDeleted = $this->removeFilesForField('documents');
        $documentDeleted = $this->removeFilesForField('document');
        return $documentsDeleted + $documentDeleted;
    }

    /**
     * Alle Files dieses Models löschen
     */
    public function removeAllFiles(): int
    {
        $files = $this->files()->get();
        $deletedCount = 0;

        foreach ($files as $file) {
            if ($file->deleteFile()) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    // =============================================
    // FILE STATISTICS & INFO
    // =============================================

    /**
     * Anzahl aller Files
     */
    public function getFileCount(): int
    {
        return $this->files()->count();
    }

    /**
     * Anzahl Files für bestimmtes Field
     */
    public function getFileCountForField(string $field): int
    {
        return $this->filesForField($field)->count();
    }

    /**
     * Gesamtgröße aller Files (in Bytes)
     */
    public function getTotalFileSize(): int
    {
        return $this->files()->sum('file_size');
    }

    /**
     * Menschenlesbare Gesamtgröße
     */
    public function getHumanTotalFileSize(): string
    {
        return File::formatBytes($this->getTotalFileSize());
    }

    /**
     * File-Statistiken abrufen
     */
    public function getFileStats(): array
    {
        $files = $this->files()->get();

        $stats = [
            'total_count' => $files->count(),
            'total_size' => $files->sum('file_size'),
            'human_total_size' => File::formatBytes($files->sum('file_size')),
            'by_type' => [],
            'by_field' => [],
            'public_count' => $files->where('is_public', true)->count(),
            'private_count' => $files->where('is_public', false)->count(),
        ];

        // Nach Content-Type gruppieren
        $byType = $files->groupBy('content_type');
        foreach ($byType as $contentType => $typeFiles) {
            $stats['by_type'][$contentType] = [
                'count' => $typeFiles->count(),
                'size' => $typeFiles->sum('file_size'),
                'human_size' => File::formatBytes($typeFiles->sum('file_size'))
            ];
        }

        // Nach Field gruppieren
        $byField = $files->groupBy('field');
        foreach ($byField as $field => $fieldFiles) {
            $fieldName = $field ?: 'no_field';
            $stats['by_field'][$fieldName] = [
                'count' => $fieldFiles->count(),
                'size' => $fieldFiles->sum('file_size'),
                'human_size' => File::formatBytes($fieldFiles->sum('file_size'))
            ];
        }

        return $stats;
    }

    // =============================================
    // FILE ACCESS CONTROL
    // =============================================

    /**
     * Kann User auf Files dieses Models zugreifen?
     * (Override in Models für spezifische Logic)
     */
    public function canAccessFiles($user = null): bool
    {
        // Standard: Wenn User das Model bearbeiten kann
        if (method_exists($this, 'canEdit')) {
            return $this->canEdit($user);
        }

        // Fallback: Nur Owner/Creator
        if (isset($this->user_id)) {
            return $user && $this->user_id === $user->id;
        }

        if (isset($this->created_by)) {
            return $user && $this->created_by === $user->id;
        }

        // Default: Kein Zugriff
        return false;
    }

    // =============================================
    // ELOQUENT EVENTS (bei Bedarf)
    // =============================================

    /**
     * Boot-Method für automatische File-Bereinigung
     * (Muss in verwendenden Models aufgerufen werden)
     */
    public static function bootHasFiles(): void
    {
        // Beim Löschen des Models: Alle Files auch löschen
//        static::deleting(function ($model) {
//            if (method_exists($model, 'removeAllFiles')) {
//                $model->removeAllFiles();
//            }
//        });
        static::deleting(function ($model) {
            $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($model));

            if ($usesSoftDeletes && method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return;
            }

            $model->removeAllFiles();
        });
    }


    // =============================================
    // VALIDATION HELPERS
    // =============================================

    /**
     * Validierungs-Regeln für File-Upload
     */
    public function getFileValidationRules(?string $field = null): array
    {
        $rules = [];

        // Field-spezifische Anpassungen
        if (in_array($field, ['image', 'images'])) {
            // Bilder
            $rules[] = 'image';
            $rules[] = 'mimes:jpg,jpeg,png,gif,webp';
            $rules[] = 'max:5120'; // 5MB
        }

        if (in_array($field, ['document', 'documents'])) {
            // Dokumente
            $rules[] = 'file';
            $rules[] = 'mimes:pdf,docx,xlsx,pptx,txt,csv';
            $rules[] = 'max:51200'; // 50MB
        }

        if ($field === 'attachments') {
            // Kombinierte Anhänge: Bilder + Dokumente
            $rules[] = 'file';
            $rules[] = 'mimes:jpg,jpeg,png,gif,webp,pdf,docx,xlsx,pptx,txt,csv';
            $rules[] = 'max:51200'; // 50MB
        }

        return $rules;
    }

    public function getFileValidationDescription(?string $field = null): string
    {
        $rules = $this->getFileValidationRules($field);

        $mimes = null;
        $max = null;

        foreach ($rules as $rule) {
            if (str_starts_with($rule, 'mimes:')) {
                $mimes = explode(',', str_replace('mimes:', '', $rule));
            }
            if (str_starts_with($rule, 'max:')) {
                $max = (int) str_replace('max:', '', $rule);
            }
        }

        // 🔠 Endungen formatiert
        $extensions = $mimes ? strtoupper(implode(', ', $mimes)) : '';

        // 📏 Größe in MB
        $sizeMb = $max ? round($max / 1024, 1) : null;

        // 🗣️ Mehrsprachiger Text
        if ($extensions && $sizeMb) {
            return __('validation.file_description_with_size', [
                'types' => $extensions,
                'size'  => $sizeMb,
            ]);
        }

        if ($extensions) {
            return __('validation.file_description_types_only', [
                'types' => $extensions,
            ]);
        }

        return '';
    }

    /**
     * Maximale Anzahl Files für Field prüfen
     */
    public function canAddMoreFiles(?string $field = null, int $maxFiles = 10): bool
    {
        if ($field === 'image') {
            return $this->getFileCountForField('image') === 0; // Nur ein Hauptbild
        }

        $currentCount = $field ? $this->getFileCountForField($field) : $this->getFileCount();
        return $currentCount < $maxFiles;
    }
}
