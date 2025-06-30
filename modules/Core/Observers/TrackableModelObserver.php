<?php

namespace Modules\Core\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Traits\HasMessages;
use Modules\Core\Models\Traits\TrackableModel;

class TrackableModelObserver
{
    /**
     * Store pending changes for models being updated
     * Using static property to avoid database save issues
     */
    private static array $pendingChanges = [];

    /**
     * Handle the model "updating" event.
     * This fires before the model is saved with changes
     */
    public function updating(Model $model): void
    {
        // Check if model has required traits
        if (!$this->hasRequiredTraits($model)) {
            return;
        }

        // Check if field tracking is enabled
        if (!$model->hasFieldTracking()) {
            return;
        }

        // Get trackable changes
        $changes = $model->getTrackableChanges();

        if (empty($changes)) {
            return;
        }

        // Store changes in static property to avoid database save
        static::$pendingChanges[$this->getModelKey($model)] = $changes;
    }

    /**
     * Handle the model "updated" event.
     * This fires after the model has been saved
     */
    public function updated(Model $model): void
    {
        $modelKey = $this->getModelKey($model);

        // Check if we have pending audit changes
        if (!isset(static::$pendingChanges[$modelKey])) {
            return;
        }

        $changes = static::$pendingChanges[$modelKey];

        try {
            // Create audit message
            $model->createAuditMessage($changes);

            Log::info('Audit message created for model update', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'changes_count' => count($changes),
                'fields' => array_keys($changes),
                'user_id' => auth()->id()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create audit message', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'error' => $e->getMessage(),
                'changes' => $changes
            ]);
        } finally {
            // Clean up pending changes
            unset(static::$pendingChanges[$modelKey]);
        }
    }

    /**
     * Handle the model "created" event.
     * Track when new records are created
     */
    public function created(Model $model): void
    {
        // Check if model has required traits
        if (!$this->hasRequiredTraits($model)) {
            return;
        }

        // Check if creation tracking is enabled
        if (!$this->shouldTrackCreation($model)) {
            return;
        }

        try {
            $model->addMessage([
                'subject' => $this->getCreationSubject($model),
                'body' => $this->getCreationBody($model),
                'message_type' => 'audit',
                'user_id' => auth()->id(),
                'metadata' => [
                    'action' => 'create',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toISOString()
                ]
            ]);

            Log::info('Creation audit message created', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'user_id' => auth()->id()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create creation audit message', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the model "deleted" event.
     * Track when records are deleted/archived
     */
    public function deleted(Model $model): void
    {
        // Check if model has required traits
        if (!$this->hasRequiredTraits($model)) {
            return;
        }

        // Check if deletion tracking is enabled
        if (!$this->shouldTrackDeletion($model)) {
            return;
        }

        try {
            $isForceDelete = method_exists($model, 'isForceDeleting') && $model->isForceDeleting();

            $model->addMessage([
                'subject' => $isForceDelete ? 'Record permanently deleted' : 'Record deleted',
                'body' => $this->getDeletionBody($model, $isForceDelete),
                'message_type' => 'audit',
                'user_id' => auth()->id(),
                'metadata' => [
                    'action' => $isForceDelete ? 'force_delete' : 'delete',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toISOString()
                ]
            ]);

            Log::info('Deletion audit message created', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'force_delete' => $isForceDelete,
                'user_id' => auth()->id()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create deletion audit message', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the model "restored" event.
     * Track when soft-deleted records are restored
     */
    public function restored(Model $model): void
    {
        // Check if model has required traits
        if (!$this->hasRequiredTraits($model)) {
            return;
        }

        try {
            $model->addMessage([
                'subject' => 'Record restored',
                'body' => $this->getRestorationBody($model),
                'message_type' => 'audit',
                'user_id' => auth()->id(),
                'metadata' => [
                    'action' => 'restore',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toISOString()
                ]
            ]);

            Log::info('Restoration audit message created', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'user_id' => auth()->id()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create restoration audit message', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if model has required traits
     */
    private function hasRequiredTraits(Model $model): bool
    {
        $traits = class_uses_recursive($model);

        return in_array(TrackableModel::class, $traits) &&
            in_array(HasMessages::class, $traits);
    }

    /**
     * Check if creation should be tracked
     */
    private function shouldTrackCreation(Model $model): bool
    {
        // Check if model has trackCreation property
        if (property_exists($model, 'trackCreation')) {
            return $model->trackCreation;
        }

        // Default: track creation if field tracking is enabled
        return $model->hasFieldTracking();
    }

    /**
     * Check if deletion should be tracked
     */
    private function shouldTrackDeletion(Model $model): bool
    {
        // Check if model has trackDeletion property
        if (property_exists($model, 'trackDeletion')) {
            return $model->trackDeletion;
        }

        // Default: track deletion if field tracking is enabled
        return $model->hasFieldTracking();
    }

    /**
     * Get creation message subject
     */
    private function getCreationSubject(Model $model): string
    {
        $modelName = class_basename($model);
        return "{$modelName} created";
    }

    /**
     * Get creation message body
     */
    private function getCreationBody(Model $model): string
    {
        $modelName = class_basename($model);
        $identifier = $this->getModelIdentifier($model);

        return "New {$modelName} record created" . ($identifier ? ": {$identifier}" : "");
    }

    /**
     * Get deletion message body
     */
    private function getDeletionBody(Model $model, bool $isForceDelete): string
    {
        $modelName = class_basename($model);
        $identifier = $this->getModelIdentifier($model);
        $action = $isForceDelete ? 'permanently deleted' : 'deleted';

        return "{$modelName} record {$action}" . ($identifier ? ": {$identifier}" : "");
    }

    /**
     * Get restoration message body
     */
    private function getRestorationBody(Model $model): string
    {
        $modelName = class_basename($model);
        $identifier = $this->getModelIdentifier($model);

        return "{$modelName} record restored from deletion" . ($identifier ? ": {$identifier}" : "");
    }

    /**
     * Get unique model key for tracking
     */
    private function getModelKey(Model $model): string
    {
        return get_class($model) . ':' . $model->id . ':' . spl_object_id($model);
    }

    /**
     * Get model identifier for messages (name, title, etc.)
     */
    private function getModelIdentifier(Model $model): ?string
    {
        $identifierFields = ['name', 'title', 'email', 'username', 'code'];

        foreach ($identifierFields as $field) {
            if (isset($model->{$field}) && !empty($model->{$field})) {
                return $model->{$field};
            }
        }

        return "ID: {$model->id}";
    }
}
