<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Abstracts\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class Message extends BaseModel
{
    protected $fillable = [
        'subject',
        'body',
        'model_type',
        'model_id',
        'user_id',
        'message_type',
        'parent_id',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'is_active' => true,
        'message_type' => 'note'
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get the model that this message belongs to (polymorphic)
     */
    public function messageable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }

    /**
     * Get the user who created this message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserExtended::class);
    }

    /**
     * Get the parent message (for threading)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get child messages (replies)
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    // ===== SCOPES =====

    /**
     * Scope to filter by message type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('message_type', $type);
    }

    /**
     * Scope to get messages for a specific model
     */
    public function scopeForModel(Builder $query, Model $model): Builder
    {
        return $query->where('model_type', get_class($model))
            ->where('model_id', $model->id);
    }

    /**
     * Scope to get recent messages
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get thread messages (parent + replies)
     */
    public function scopeThread(Builder $query, ?int $parentId = null): Builder
    {
        return $query->where(function ($q) use ($parentId) {
            $q->where('id', $parentId)
                ->orWhere('parent_id', $parentId);
        });
    }

    // ===== HELPER METHODS =====

    /**
     * Check if this message has replies
     */
    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    /**
     * Check if this is a reply to another message
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Archive this message
     */
    public function archive(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Restore archived message
     */
    public function restore(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Get the model instance this message belongs to
     */
    public function getModel(): ?Model
    {
        if (!$this->model_type || !$this->model_id) {
            return null;
        }

        try {
            return $this->model_type::find($this->model_id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create a reply to this message
     */
    public function reply(array $attributes): self
    {
        return static::create(array_merge($attributes, [
            'parent_id' => $this->id,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
        ]));
    }

    // ===== STATIC HELPER METHODS =====

    /**
     * Create a message for a specific model
     */
    public static function createForModel(Model $model, array $attributes): self
    {
        return static::create(array_merge($attributes, [
            'model_type' => get_class($model),
            'model_id' => $model->id,
        ]));
    }

    /**
     * Get messages for a specific model
     */
    public static function forModel(Model $model): Builder
    {
        return static::where('model_type', get_class($model))
            ->where('model_id', $model->id);
    }

    /**
     * Get message counts by type
     */
    public static function getTypeCounts(Model $model): array
    {
        return static::forModel($model)
            ->active()
            ->groupBy('message_type')
            ->selectRaw('message_type, count(*) as count')
            ->pluck('count', 'message_type')
            ->toArray();
    }
}
