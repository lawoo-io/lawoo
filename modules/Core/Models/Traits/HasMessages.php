<?php

namespace Modules\Core\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Models\Message;
use Modules\Core\Models\UserExtended;

trait HasMessages
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserExtended::class);
    }

    /**
     * Get all messages for this model
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'messageable', 'model_type', 'model_id');
    }

    /**
     * Get only active messages
     */
    public function activeMessages(): MorphMany
    {
        return $this->messages()->where('is_active', true);
    }

    /**
     * Get only archived messages
     */
    public function archivedMessages(): MorphMany
    {
        return $this->messages()->where('is_active', false);
    }

    /**
     * Get messages of specific type
     */
    public function messagesOfType(string $type): MorphMany
    {
        return $this->messages()->where('message_type', $type);
    }

    /**
     * Get recent messages (default: last 30 days)
     */
    public function recentMessages(int $days = 30): MorphMany
    {
        return $this->activeMessages()->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Add a message to this model
     */
    public function addMessage(array $attributes): Message
    {
        return $this->messages()->create(array_merge($attributes, [
            'user_id' => $attributes['user_id'] ?? auth()->id(),
        ]));
    }

    /**
     * Add a note (default message type)
     */
    public function addNote(string $subject, string $body, ?int $userId = null): Message
    {
        return $this->addMessage([
            'subject' => $subject,
            'body' => $body,
            'message_type' => 'note',
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Add an email message
     */
    public function addEmail(string $subject, string $body, ?int $userId = null, array $metadata = []): Message
    {
        return $this->addMessage([
            'subject' => $subject,
            'body' => $body,
            'message_type' => 'email',
            'user_id' => $userId ?? auth()->id(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Add a system message
     */
    public function addSystemMessage(string $subject, string $body, array $metadata = []): Message
    {
        return $this->addMessage([
            'subject' => $subject,
            'body' => $body,
            'message_type' => 'system',
            'user_id' => null, // System messages have no user
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get message counts by type
     */
    public function getMessageCounts(): array
    {
        return $this->activeMessages()
            ->selectRaw('message_type, count(*) as count')
            ->groupBy('message_type')
            ->pluck('count', 'message_type')
            ->toArray();
    }

    /**
     * Get total message count
     */
    public function getTotalMessageCount(): int
    {
        return $this->activeMessages()->count();
    }

    /**
     * Get latest message
     */
    public function getLatestMessage(): ?Message
    {
        return $this->activeMessages()->latest()->first();
    }

    /**
     * Check if model has messages
     */
    public function hasMessages(): bool
    {
        return $this->activeMessages()->exists();
    }

    /**
     * Check if model has messages of specific type
     */
    public function hasMessagesOfType(string $type): bool
    {
        return $this->messagesOfType($type)->where('is_active', true)->exists();
    }

    /**
     * Archive all messages for this model
     */
    public function archiveAllMessages(): int
    {
        return $this->activeMessages()->update(['is_active' => false]);
    }

    /**
     * Restore all archived messages for this model
     */
    public function restoreAllMessages(): int
    {
        return $this->archivedMessages()->update(['is_active' => true]);
    }

    /**
     * Delete all messages for this model (when model is deleted)
     */
    public function deleteAllMessages(): int
    {
        return $this->messages()->delete();
    }

    /**
     * Scope to include models with messages
     */
    public function scopeWithMessages(Builder $query): Builder
    {
        return $query->whereHas('messages', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope to include models with recent messages
     */
    public function scopeWithRecentMessages(Builder $query, int $days = 30): Builder
    {
        return $query->whereHas('messages', function ($q) use ($days) {
            $q->where('is_active', true)
                ->where('created_at', '>=', now()->subDays($days));
        });
    }

    /**
     * Boot the trait
     */
    protected static function bootHasMessages(): void
    {
        // Optional: Auto-delete messages when model is deleted
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                // Hard delete - remove all messages
                $model->deleteAllMessages();
            } else {
                // Soft delete - archive messages
                $model->archiveAllMessages();
            }
        });

        // Optional: Restore messages when model is restored
        static::restored(function ($model) {
            $model->restoreAllMessages();
        });
    }
}
