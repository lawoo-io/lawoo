<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Haupt-Tabelle als partitionierte Tabelle erstellen
        DB::statement("
            CREATE TABLE messages (
                id BIGSERIAL,
                subject VARCHAR(255),
                body TEXT,
                model_type VARCHAR(150) NOT NULL,
                model_id BIGINT NOT NULL,
                user_id BIGINT,
                message_type VARCHAR(50) DEFAULT 'note',
                parent_id BIGINT,
                is_active BOOLEAN DEFAULT TRUE,
                metadata JSONB,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT messages_pkey PRIMARY KEY (id)
            ) PARTITION BY RANGE (id)
        ");

        // 2. Erste Partition erstellen (1 bis 500.000)
        DB::statement("
            CREATE TABLE messages_chunk1 PARTITION OF messages
            FOR VALUES FROM (1) TO (500001)
        ");

        // 3. Indices für erste Partition erstellen
        $this->createPartitionIndices('messages_chunk1');

        // 4. Foreign Key Constraints (falls nötig)
        DB::statement("
            ALTER TABLE messages
            ADD CONSTRAINT fk_messages_user_id
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ");

        // 5. Function für automatische Chunk-Erstellung
        $this->createChunkFunction();

        // 6. Sequence für ID-Generierung anpassen (falls nötig)
        DB::statement("
            SELECT setval('messages_id_seq', 1, false)
        ");
    }

    public function down(): void
    {
        // Alle Chunk-Partitionen finden und löschen
        $chunks = DB::select("
            SELECT tablename
            FROM pg_tables
            WHERE tablename LIKE 'messages_chunk%'
            AND schemaname = 'public'
        ");

        foreach ($chunks as $chunk) {
            DB::statement("DROP TABLE IF EXISTS {$chunk->tablename} CASCADE");
        }

        // Haupt-Tabelle und Function löschen
        DB::statement("DROP TABLE IF EXISTS messages CASCADE");
        DB::statement("DROP FUNCTION IF EXISTS create_messages_chunk(integer)");
    }

    private function createPartitionIndices(string $partitionName): void
    {
        // Index für polymorphic relationship (model_type + model_id)
        DB::statement("
            CREATE INDEX idx_{$partitionName}_model
            ON {$partitionName} (model_type, model_id)
        ");

        // Index für User
        DB::statement("
            CREATE INDEX idx_{$partitionName}_user
            ON {$partitionName} (user_id)
            WHERE user_id IS NOT NULL
        ");

        // Index für Message-Type
        DB::statement("
            CREATE INDEX idx_{$partitionName}_type
            ON {$partitionName} (message_type)
        ");

        // Index für Parent-Messages (Threading)
        DB::statement("
            CREATE INDEX idx_{$partitionName}_parent
            ON {$partitionName} (parent_id)
            WHERE parent_id IS NOT NULL
        ");

        // Index für Zeitbereich-Queries
        DB::statement("
            CREATE INDEX idx_{$partitionName}_created_at
            ON {$partitionName} (created_at)
        ");

        // Index für aktive Messages (häufigste Queries)
        DB::statement("
            CREATE INDEX idx_{$partitionName}_active
            ON {$partitionName} (model_type, model_id, created_at DESC)
            WHERE is_active = true
        ");

        // Index für User bei aktiven Messages
        DB::statement("
            CREATE INDEX idx_{$partitionName}_user_active
            ON {$partitionName} (user_id, created_at DESC)
            WHERE user_id IS NOT NULL AND is_active = true
        ");

        // JSONB Index für Metadata (falls verwendet)
        DB::statement("
            CREATE INDEX idx_{$partitionName}_metadata
            ON {$partitionName} USING GIN (metadata)
            WHERE metadata IS NOT NULL
        ");
    }

    private function createChunkFunction(): void
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION create_messages_chunk(chunk_number integer)
            RETURNS void AS \$\$
            DECLARE
                partition_name text;
                start_id bigint;
                end_id bigint;
                chunk_size integer := 500000; -- Chunk-Größe
            BEGIN
                -- Berechne Partition-Namen und ID-Bereiche
                partition_name := 'messages_chunk' || chunk_number;
                start_id := ((chunk_number - 1) * chunk_size) + 1;
                end_id := (chunk_number * chunk_size) + 1;

                -- Prüfe ob Partition bereits existiert
                IF NOT EXISTS (
                    SELECT 1 FROM pg_tables
                    WHERE tablename = partition_name
                    AND schemaname = 'public'
                ) THEN
                    -- Erstelle Partition
                    EXECUTE format(
                        'CREATE TABLE %I PARTITION OF messages FOR VALUES FROM (%L) TO (%L)',
                        partition_name, start_id, end_id
                    );

                    -- Erstelle Indices für neue Partition
                    EXECUTE format(
                        'CREATE INDEX %I ON %I (model_type, model_id)',
                        'idx_' || partition_name || '_model', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (user_id) WHERE user_id IS NOT NULL',
                        'idx_' || partition_name || '_user', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (message_type)',
                        'idx_' || partition_name || '_type', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (parent_id) WHERE parent_id IS NOT NULL',
                        'idx_' || partition_name || '_parent', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (created_at)',
                        'idx_' || partition_name || '_created_at', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (model_type, model_id, created_at DESC) WHERE is_active = true',
                        'idx_' || partition_name || '_active', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (user_id, created_at DESC) WHERE user_id IS NOT NULL AND is_active = true',
                        'idx_' || partition_name || '_user_active', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I USING GIN (metadata) WHERE metadata IS NOT NULL',
                        'idx_' || partition_name || '_metadata', partition_name
                    );

                    RAISE NOTICE 'Created partition: % for IDs % to %',
                                 partition_name, start_id, end_id - 1;
                ELSE
                    RAISE NOTICE 'Partition % already exists', partition_name;
                END IF;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }
};
