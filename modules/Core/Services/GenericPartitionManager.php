<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenericPartitionManager
{
    private string $tableName;
    private int $chunkSize;
    private string $cacheKey;
    private int $cacheTime = 3600; // 1 hour

    public function __construct(string $tableName, ?int $chunkSize = null)
    {
        $this->tableName = $tableName;
        $this->chunkSize = $chunkSize ?? $this->getConfiguredChunkSize($tableName);
        $this->cacheKey = "{$tableName}.current_chunk";
    }

    /**
     * Create a partition manager for a specific table
     */
    public static function for(string $tableName, ?int $chunkSize = null): self
    {
        return new self($tableName, $chunkSize);
    }

    /**
     * Ensure partition exists for a specific record ID
     */
    public function ensurePartitionExists(?int $recordId = null): void
    {
        $recordId = $recordId ?? $this->getNextRecordId();
        $chunkNumber = $this->calculateChunkNumber($recordId);

        if (!$this->partitionExists($chunkNumber)) {
            $this->createChunk($chunkNumber);
        }
    }

    /**
     * Get the current chunk number for new records
     */
    public function getCurrentChunk(): int
    {
        return Cache::remember($this->cacheKey, $this->cacheTime, function () {
            return $this->calculateCurrentChunk();
        });
    }

    /**
     * Check if we need to create a new chunk soon (90% full)
     */
    public function shouldCreateNextChunk(): bool
    {
        $currentChunk = $this->getCurrentChunk();
        $usage = $this->getChunkUsage($currentChunk);

        return $usage['percentage'] >= 90;
    }

    /**
     * Create the next chunk if needed
     */
    public function createNextChunkIfNeeded(): bool
    {
        if ($this->shouldCreateNextChunk()) {
            $nextChunk = $this->getCurrentChunk() + 1;
            $this->createChunk($nextChunk);
            return true;
        }

        return false;
    }

    /**
     * Get usage statistics for a specific chunk
     */
    public function getChunkUsage(int $chunkNumber): array
    {
        $partitionName = $this->getPartitionName($chunkNumber);

        if (!$this->partitionExists($chunkNumber)) {
            return [
                'table' => $this->tableName,
                'chunk' => $chunkNumber,
                'partition_name' => $partitionName,
                'count' => 0,
                'max' => $this->chunkSize,
                'percentage' => 0,
                'remaining' => $this->chunkSize,
                'exists' => false
            ];
        }

        $count = $this->getChunkRecordCount($chunkNumber);

        return [
            'table' => $this->tableName,
            'chunk' => $chunkNumber,
            'partition_name' => $partitionName,
            'count' => $count,
            'max' => $this->chunkSize,
            'percentage' => round(($count / $this->chunkSize) * 100, 1),
            'remaining' => $this->chunkSize - $count,
            'exists' => true
        ];
    }

    /**
     * Get overview of all chunks for this table
     */
    public function getAllChunksStats(): array
    {
        $chunks = $this->getAllChunks();
        $stats = [];

        foreach ($chunks as $chunkNumber) {
            $stats[] = $this->getChunkUsage($chunkNumber);
        }

        return $stats;
    }

    /**
     * Get table summary statistics
     */
    public function getTableStats(): array
    {
        $chunks = $this->getAllChunksStats();

        $totalRecords = array_sum(array_column($chunks, 'count'));
        $totalChunks = count($chunks);
        $currentChunk = $this->getCurrentChunk();

        return [
            'table' => $this->tableName,
            'total_chunks' => $totalChunks,
            'total_records' => $totalRecords,
            'current_chunk' => $currentChunk,
            'chunk_size' => $this->chunkSize,
            'chunks' => $chunks
        ];
    }

    /**
     * Calculate which chunk number an ID belongs to
     */
    private function calculateChunkNumber(int $recordId): int
    {
        return (int) ceil($recordId / $this->chunkSize);
    }

    /**
     * Calculate the current active chunk
     */
    private function calculateCurrentChunk(): int
    {
        $maxId = $this->getMaxRecordId();

        if ($maxId === 0) {
            return 1; // First chunk
        }

        return $this->calculateChunkNumber($maxId);
    }

    /**
     * Get partition name for chunk number
     */
    private function getPartitionName(int $chunkNumber): string
    {
        return "{$this->tableName}_chunk{$chunkNumber}";
    }

    /**
     * Check if a partition exists
     */
    private function partitionExists(int $chunkNumber): bool
    {
        $partitionName = $this->getPartitionName($chunkNumber);

        $result = DB::selectOne("
            SELECT EXISTS (
                SELECT 1 FROM pg_tables
                WHERE tablename = ? AND schemaname = 'public'
            ) as exists
        ", [$partitionName]);

        return (bool) $result->exists;
    }

    /**
     * Create a new chunk partition
     */
    private function createChunk(int $chunkNumber): void
    {
        $functionName = "create_{$this->tableName}_chunk";

        try {
            DB::select("SELECT {$functionName}(?)", [$chunkNumber]);

            // Clear cache since we have a new chunk
            Cache::forget($this->cacheKey);

            Log::info("Created partition chunk", [
                'table' => $this->tableName,
                'chunk_number' => $chunkNumber,
                'partition_name' => $this->getPartitionName($chunkNumber),
                'id_range' => $this->getChunkIdRange($chunkNumber)
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to create partition chunk", [
                'table' => $this->tableName,
                'chunk_number' => $chunkNumber,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get the next record ID that will be generated
     */
    private function getNextRecordId(): int
    {
        $sequenceName = "{$this->tableName}_id_seq";

        try {
            $result = DB::selectOne("SELECT last_value + 1 as next_id FROM {$sequenceName}");
            return $result->next_id ?? 1;
        } catch (\Exception $e) {
            return 1; // Fallback for first record
        }
    }

    /**
     * Get the maximum record ID
     */
    private function getMaxRecordId(): int
    {
        try {
            $result = DB::selectOne("SELECT COALESCE(MAX(id), 0) as max_id FROM {$this->tableName}");
            return $result->max_id ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get record count for a specific chunk
     */
    private function getChunkRecordCount(int $chunkNumber): int
    {
        $partitionName = $this->getPartitionName($chunkNumber);

        try {
            $result = DB::selectOne("SELECT COUNT(*) as count FROM {$partitionName}");
            return $result->count ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get all existing chunk numbers for this table
     */
    private function getAllChunks(): array
    {
        $pattern = "{$this->tableName}_chunk%";

        $partitions = DB::select("
            SELECT tablename
            FROM pg_tables
            WHERE tablename LIKE ?
            AND schemaname = 'public'
            ORDER BY tablename
        ", [$pattern]);

        $chunks = [];
        $tablePrefix = "{$this->tableName}_chunk";

        foreach ($partitions as $partition) {
            if (str_starts_with($partition->tablename, $tablePrefix)) {
                $chunkStr = substr($partition->tablename, strlen($tablePrefix));
                if (is_numeric($chunkStr)) {
                    $chunks[] = (int) $chunkStr;
                }
            }
        }

        sort($chunks);
        return $chunks;
    }

    /**
     * Get ID range for a chunk
     */
    private function getChunkIdRange(int $chunkNumber): array
    {
        $startId = (($chunkNumber - 1) * $this->chunkSize) + 1;
        $endId = $chunkNumber * $this->chunkSize;

        return [
            'start' => $startId,
            'end' => $endId,
            'range' => "{$startId} - {$endId}"
        ];
    }

    /**
     * Get configured chunk size for table
     */
    private function getConfiguredChunkSize(string $tableName): int
    {
        return config("partitioning.tables.{$tableName}.chunk_size", 500000);
    }

    /**
     * Clear all caches for this table
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Get table name
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Get chunk size configuration
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * Set chunk size (for testing or configuration)
     */
    public function setChunkSize(int $size): void
    {
        $this->chunkSize = $size;
        $this->clearCache();
    }
}
