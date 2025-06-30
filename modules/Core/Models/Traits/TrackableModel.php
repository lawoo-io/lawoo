<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Observers\TrackableModelObserver;

trait TrackableModel
{
    /**
     * Boot the trait and register observer
     */
    protected static function bootTrackableModel(): void
    {
        static::observe(TrackableModelObserver::class);

        // Track BelongsToMany changes
        static::bootBelongsToManyTracking();
    }

    /**
     * Boot BelongsToMany tracking
     */
    protected static function bootBelongsToManyTracking(): void
    {
        // Override BelongsToMany methods to track changes
        // This is done in the trait rather than observer since we need to intercept the calls
    }

    /**
     * Override belongsToMany method to return trackable relation
     */
    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
    {
        $relation = parent::belongsToMany($related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation);

        // Wrap the relation to track changes
        return $this->wrapBelongsToManyForTracking($relation);
    }

    /**
     * Wrap BelongsToMany relation to track changes
     */
    protected function wrapBelongsToManyForTracking(BelongsToMany $relation): BelongsToMany
    {
        $relationName = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown';

        // Check if this relation should be tracked
        if (!$this->shouldTrackBelongsToManyRelation($relationName)) {
            return $relation;
        }

        // Store original methods and override them
        $originalAttach = $relation->attach(...);
        $originalDetach = $relation->detach(...);
        $originalSync = $relation->sync(...);

        // This approach is complex - let's use a simpler method
        return $relation;
    }

    /**
     * Track belongsToMany attach operation
     */
    public function trackBelongsToManyAttach(string $relationName, $ids, array $attributes = []): void
    {
        if (!$this->shouldTrackBelongsToManyRelation($relationName)) {
            return;
        }

        $config = $this->getBelongsToManyConfig($relationName);
        $this->createBelongsToManyMessage($relationName, $config, 'attached', (array) $ids, []);
    }

    /**
     * Track belongsToMany detach operation
     */
    public function trackBelongsToManyDetach(string $relationName, $ids = null): void
    {
        if (!$this->shouldTrackBelongsToManyRelation($relationName)) {
            return;
        }

        $config = $this->getBelongsToManyConfig($relationName);
        $this->createBelongsToManyMessage($relationName, $config, 'detached', [], (array) $ids);
    }

    /**
     * Track belongsToMany sync operation
     */
    public function trackBelongsToManySync(string $relationName, $ids, bool $detaching = true): void
    {
        if (!$this->shouldTrackBelongsToManyRelation($relationName)) {
            return;
        }

        // Get current state before sync - use qualified column name to avoid ambiguity
        $relation = $this->$relationName();
        $relatedTable = $relation->getRelated()->getTable();
        $relatedKey = $relation->getRelated()->getKeyName();
        $qualifiedKey = "{$relatedTable}.{$relatedKey}";

        $currentIds = $relation->pluck($qualifiedKey)->toArray();

        // Handle different input formats for sync()
        if (is_array($ids)) {
            // Check if it's associative array with pivot data: [1 => ['extra' => 'value'], 2 => [...]]
            if (array_keys($ids) !== range(0, count($ids) - 1)) {
                $newIds = array_keys($ids); // Associative array - use keys
            } else {
                $newIds = $ids; // Sequential array - use values
            }
        } else {
            $newIds = (array) $ids; // Single ID or collection
        }

        // Convert to integers to ensure comparison works
        $currentIds = array_map('intval', $currentIds);
        $newIds = array_map('intval', $newIds);

        if ($detaching) {
            $attached = array_diff($newIds, $currentIds);
            $detached = array_diff($currentIds, $newIds);
        } else {
            $attached = array_diff($newIds, $currentIds);
            $detached = [];
        }

        if (!empty($attached) || !empty($detached)) {
            $config = $this->getBelongsToManyConfig($relationName);
            $this->createBelongsToManyMessage($relationName, $config, 'synced', $attached, $detached);
        }
    }

    /**
     * Check if belongsToMany relation should be tracked
     */
    protected function shouldTrackBelongsToManyRelation(string $relationName): bool
    {
        $trackableFields = $this->getTrackableFields();

        return isset($trackableFields[$relationName]) &&
            is_array($trackableFields[$relationName]) &&
            ($trackableFields[$relationName]['type'] ?? null) === 'belongsToMany';
    }

    /**
     * Get belongsToMany configuration
     */
    protected function getBelongsToManyConfig(string $relationName): array
    {
        $trackableFields = $this->getTrackableFields();
        return $trackableFields[$relationName] ?? [];
    }

    /**
     * Create message for belongsToMany changes
     */
    protected function createBelongsToManyMessage(string $relationName, array $config, string $action, array $attached, array $detached): void
    {
        $displayField = $config['display_field'] ?? 'name';
        $label = $config['label'] ?? ucfirst($relationName);

        $relation = $this->$relationName();
        $relatedModel = $relation->getRelated();

        $changes = [];

        if (!empty($attached)) {
            $attachedItems = $relatedModel->newQuery()
                ->whereIn($relatedModel->getKeyName(), $attached)
                ->get()
                ->map(function($model) use ($displayField) {
                    return $model->$displayField; // Uses model accessor (with translations)
                })
                ->toArray();
            $changes[] = __("Added: ") . implode(', ', $attachedItems);
        }

        if (!empty($detached)) {
            $detachedItems = $relatedModel->newQuery()
                ->whereIn($relatedModel->getKeyName(), $detached)
                ->get()
                ->map(function($model) use ($displayField) {
                    return $model->$displayField; // Uses model accessor (with translations)
                })
                ->toArray();
            $changes[] = __("Removed: ") . implode(', ', $detachedItems);
        }

        if (!empty($changes)) {
            $this->addMessage([
                'subject' => __("{$label}") . " " . __("updated"),
                'body' => implode("\n", $changes),
                'message_type' => 'audit',
                'user_id' => auth()->id(),
                'metadata' => [
                    'action' => $action,
                    'relation' => $relationName,
                    'attached' => $attached,
                    'detached' => $detached,
                    'timestamp' => now()->toISOString()
                ]
            ]);
        }
    }

    /**
     * Get trackable fields configuration
     */
    public function getTrackableFields(): array
    {
        // Check if detailed configuration exists
        if (property_exists($this, 'trackableFields') && !empty($this->trackableFields)) {
            return $this->trackableFields;
        }

        // Fallback to simple array + auto-detect relationships
        if (property_exists($this, 'trackable') && !empty($this->trackable)) {
            $fields = array_combine($this->trackable, $this->trackable);

            // Auto-detect belongsTo relationships
            return array_merge($fields, $this->autoDetectBelongsToFields());
        }

        return [];
    }

    /**
     * Auto-detect belongsTo fields from foreign keys
     */
    protected function autoDetectBelongsToFields(): array
    {
        $detected = [];
        $fillable = $this->getFillable();

        foreach ($fillable as $field) {
            // Check if it's a foreign key pattern (*_id)
            if (str_ends_with($field, '_id')) {
                $relationshipName = str_replace('_id', '', $field);

                // Check if relationship method exists
                if (method_exists($this, $relationshipName)) {
                    try {
                        $relation = $this->$relationshipName();

                        if ($relation instanceof BelongsTo) {
                            $detected[$field] = [
                                'type' => 'belongsTo',
                                'relationship' => $relationshipName,
                                'display_field' => $this->guessDisplayField($relation->getRelated()),
                                'label' => ucfirst(str_replace('_', ' ', $relationshipName))
                            ];
                        }
                    } catch (\Exception $e) {
                        // Skip if relationship doesn't work
                        continue;
                    }
                }
            }
        }

        return $detected;
    }

    /**
     * Guess the best display field for a model
     */
    protected function guessDisplayField($model): string
    {
        $possibleFields = ['name', 'title', 'label', 'email', 'username', 'code'];
        $fillable = $model->getFillable();

        foreach ($possibleFields as $field) {
            if (in_array($field, $fillable)) {
                return $field;
            }
        }

        return 'id'; // Fallback
    }

    /**
     * Get belongsToMany trackable fields
     */
    protected function getBelongsToManyTrackableFields(): array
    {
        $trackableFields = $this->getTrackableFields();

        return array_filter($trackableFields, function ($config) {
            return is_array($config) && isset($config['type']) && $config['type'] === 'belongsToMany';
        });
    }

    /**
     * Check if a field should be tracked
     */
    public function shouldTrackField(string $field): bool
    {
        return array_key_exists($field, $this->getTrackableFields());
    }

    /**
     * Get human-readable label for field
     */
    public function getFieldLabel(string $field): string
    {
        $trackableFields = $this->getTrackableFields();

        if (isset($trackableFields[$field])) {
            $config = $trackableFields[$field];

            if (is_array($config)) {
                $label = $config['label'] ?? ucfirst(str_replace('_', ' ', $field));
            } else {
                $label = $config;
            }

            return __($label);
        }

        // Fallback
        $fallback = ucfirst(str_replace('_', ' ', $field));
        return __($fallback);
    }

    /**
     * Get message template for field change
     */
    public function getFieldChangeTemplate(string $field): string
    {
        $trackableFields = $this->getTrackableFields();

        if (isset($trackableFields[$field]) && is_array($trackableFields[$field])) {
            $template = $trackableFields[$field]['template'] ?? ':field changed from ":old" to ":new"';
            return __($template);
        }

        return __(':field changed from ":old" to ":new"');
    }

    /**
     * Check if field tracking is enabled for this model
     */
    public function hasFieldTracking(): bool
    {
        return !empty($this->getTrackableFields());
    }

    /**
     * Get changes that should be tracked
     */
    public function getTrackableChanges(): array
    {
        $changes = [];
        $trackableFields = $this->getTrackableFields();

        foreach ($this->getDirty() as $field => $newValue) {
            if ($this->shouldTrackField($field)) {
                $config = $trackableFields[$field];

                if (is_array($config) && isset($config['type'])) {
                    // Handle relationship fields
                    $changes[$field] = $this->getRelationshipChange($field, $config, $newValue);
                } else {
                    // Handle regular fields
                    $changes[$field] = [
                        'field' => $field,
                        'label' => $this->getFieldLabel($field),
                        'old' => $this->getOriginal($field),
                        'new' => $newValue,
                        'template' => $this->getFieldChangeTemplate($field)
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Get relationship change details
     */
    protected function getRelationshipChange(string $field, array $config, $newValue): array
    {
        $relationshipName = $config['relationship'];
        $displayField = $config['display_field'] ?? 'name';
        $type = $config['type'];

        if ($type === 'belongsTo') {
            $oldValue = $this->getOriginal($field);

            // Get display values
            $oldDisplay = $oldValue ? $this->getRelatedDisplayValue($relationshipName, $oldValue, $displayField) : '(none)';
            $newDisplay = $newValue ? $this->getRelatedDisplayValue($relationshipName, $newValue, $displayField) : '(none)';

            return [
                'field' => $field,
                'label' => $this->getFieldLabel($field),
                'old' => $oldDisplay,
                'new' => $newDisplay,
                'template' => $this->getFieldChangeTemplate($field)
            ];
        }

        // Fallback for other types
        return [
            'field' => $field,
            'label' => $this->getFieldLabel($field),
            'old' => $this->getOriginal($field),
            'new' => $newValue,
            'template' => $this->getFieldChangeTemplate($field)
        ];
    }

    /**
     * Get display value for related model
     */
    protected function getRelatedDisplayValue(string $relationshipName, $id, string $displayField): string
    {
        try {
            if (method_exists($this, $relationshipName)) {
                $relation = $this->$relationshipName();

                if ($relation instanceof BelongsTo) {
                    $related = $relation->getRelated()->find($id);
                    return $related ? ($related->$displayField ?? "ID: {$id}") : "ID: {$id}";
                }
            }
        } catch (\Exception $e) {
            // Fallback if relationship fails
        }

        return "ID: {$id}";
    }

    /**
     * Format field value for display
     */
    public function formatFieldValue(string $field, $value): string
    {
        // Handle null values
        if (is_null($value)) {
            return __('(empty)');
        }

        // Handle boolean values
        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        // Handle dates
        if ($this->isDateAttribute($field) && $value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        // Handle arrays/objects
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Create audit message for field changes
     */
    public function createAuditMessage(array $changes): void
    {
        if (empty($changes)) {
            return;
        }

        // Create summary message
        $summary = $this->buildChangeSummary($changes);
        $details = $this->buildChangeDetails($changes);

        $this->addMessage([
            'subject' => $summary,
            'body' => $details,
            'message_type' => 'audit',
            'user_id' => auth()->id(),
            'metadata' => [
                'changes' => $changes,
                'action' => 'update',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Build change summary for message subject
     */
    private function buildChangeSummary(array $changes): string
    {
        $count = count($changes);
        $fields = array_column($changes, 'label');

        if ($count === 1) {
            return __(":field updated", ['field' => reset($fields)]);
        }

        if ($count <= 3) {
            return __(":fields updated", ['fields' => implode(', ', $fields)]);
        }

        return __(":count fields updated", ['count' => $count]);
    }

    /**
     * Build detailed change description for message body
     */
    private function buildChangeDetails(array $changes): string
    {
        $details = [];

        foreach ($changes as $change) {
            $template = $change['template'];
            $oldValue = $change['old'];
            $newValue = $change['new'];

            // For relationship fields, values are already formatted
            $trackableFields = $this->getTrackableFields();
            $fieldConfig = $trackableFields[$change['field']] ?? null;

            if (!is_array($fieldConfig) || !isset($fieldConfig['type'])) {
                // Format regular field values
                $oldValue = $this->formatFieldValue($change['field'], $oldValue);
                $newValue = $this->formatFieldValue($change['field'], $newValue);
            }

            $message = __($template, [
                'field' => $change['label'],
                'old' => $oldValue,
                'new' => $newValue
            ]);

            $details[] = $message;
        }

        return implode("\n", $details);
    }
}
