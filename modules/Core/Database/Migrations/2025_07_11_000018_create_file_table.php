<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Haupt-Tabelle als partitionierte Tabelle erstellen
        DB::statement("
            CREATE TABLE files (
                id BIGSERIAL,
                disk_name VARCHAR(255) NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_size BIGINT NOT NULL,
                content_type VARCHAR(255) NOT NULL,
                title VARCHAR(255),
                description TEXT,
                field VARCHAR(255),
                model_type VARCHAR(150) NOT NULL,
                model_id BIGINT NOT NULL,
                uploaded_by BIGINT,
                is_public BOOLEAN DEFAULT FALSE,
                sort_order INTEGER,
                metadata JSONB,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT files_pkey PRIMARY KEY (id)
            ) PARTITION BY RANGE (id)
        ");

        // 2. Erste Partition erstellen (1 bis 500.000)
        DB::statement("
            CREATE TABLE files_chunk1 PARTITION OF files
            FOR VALUES FROM (1) TO (500001)
        ");

        // 3. Indices für erste Partition erstellen
        $this->createPartitionIndices('files_chunk1');

        // 4. Foreign Key Constraints
        DB::statement("
            ALTER TABLE files
            ADD CONSTRAINT fk_files_uploaded_by
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
        ");

        // 5. Function für automatische Chunk-Erstellung
        $this->createChunkFunction();

        // 6. Sequence für ID-Generierung anpassen
        DB::statement("
            SELECT setval('files_id_seq', 1, false)
        ");
    }

    public function down(): void
    {
        // Alle Chunk-Partitionen finden und löschen
        $chunks = DB::select("
            SELECT tablename
            FROM pg_tables
            WHERE tablename LIKE 'files_chunk%'
            AND schemaname = 'public'
        ");

        foreach ($chunks as $chunk) {
            DB::statement("DROP TABLE IF EXISTS {$chunk->tablename} CASCADE");
        }

        // Haupt-Tabelle und Function löschen
        DB::statement("DROP TABLE IF EXISTS files CASCADE");
        DB::statement("DROP FUNCTION IF EXISTS create_files_chunk(integer)");
    }

    private function createPartitionIndices(string $partitionName): void
    {
        // Index für polymorphic relationship (model_type + model_id) - HÄUFIGSTER QUERY
        DB::statement("
            CREATE INDEX idx_{$partitionName}_model
            ON {$partitionName} (model_type, model_id)
        ");

        // Index für field-basierte Kategorisierung (avatar, contracts, etc.)
        DB::statement("
            CREATE INDEX idx_{$partitionName}_field
            ON {$partitionName} (field)
            WHERE field IS NOT NULL
        ");

        // Index für field + model combination (spezifische Kategorien pro Model)
        DB::statement("
            CREATE INDEX idx_{$partitionName}_field_model
            ON {$partitionName} (model_type, model_id, field)
            WHERE field IS NOT NULL
        ");

        // Index für Uploader
        DB::statement("
            CREATE INDEX idx_{$partitionName}_uploaded_by
            ON {$partitionName} (uploaded_by)
            WHERE uploaded_by IS NOT NULL
        ");

        // Index für öffentliche Dateien
        DB::statement("
            CREATE INDEX idx_{$partitionName}_public
            ON {$partitionName} (is_public, created_at DESC)
            WHERE is_public = true
        ");

        // Index für Content-Type (Datei-Typ-Filter)
        DB::statement("
            CREATE INDEX idx_{$partitionName}_content_type
            ON {$partitionName} (content_type)
        ");

        // Index für Zeitbereich-Queries
        DB::statement("
            CREATE INDEX idx_{$partitionName}_created_at
            ON {$partitionName} (created_at)
        ");

        // Index für Sortierung innerhalb Models
        DB::statement("
            CREATE INDEX idx_{$partitionName}_sort_order
            ON {$partitionName} (model_type, model_id, sort_order)
            WHERE sort_order IS NOT NULL
        ");

        // Index für Dateiname-Suche (unique disk_name pro System)
        DB::statement("
            CREATE UNIQUE INDEX idx_{$partitionName}_disk_name
            ON {$partitionName} (disk_name)
        ");

        // Index für User-Upload-History
        DB::statement("
            CREATE INDEX idx_{$partitionName}_user_uploads
            ON {$partitionName} (uploaded_by, created_at DESC)
            WHERE uploaded_by IS NOT NULL
        ");

        // JSONB Index für Metadata (falls verwendet)
        DB::statement("
            CREATE INDEX idx_{$partitionName}_metadata
            ON {$partitionName} USING GIN (metadata)
            WHERE metadata IS NOT NULL
        ");

        // Composite Index für häufige Admin-Queries (alle Dokumente eines Types)
        DB::statement("
            CREATE INDEX idx_{$partitionName}_admin_overview
            ON {$partitionName} (model_type, created_at DESC)
        ");
    }

    private function createChunkFunction(): void
    {
        DB::statement("
            CREATE OR REPLACE FUNCTION create_files_chunk(chunk_number integer)
            RETURNS void AS \$\$
            DECLARE
                partition_name text;
                start_id bigint;
                end_id bigint;
                chunk_size integer := 500000; -- Chunk-Größe: 500k Dokumente
            BEGIN
                -- Berechne Partition-Namen und ID-Bereiche
                partition_name := 'files_chunk' || chunk_number;
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
                        'CREATE TABLE %I PARTITION OF files FOR VALUES FROM (%L) TO (%L)',
                        partition_name, start_id, end_id
                    );

                    -- Erstelle alle Indices für neue Partition
                    EXECUTE format(
                        'CREATE INDEX %I ON %I (model_type, model_id)',
                        'idx_' || partition_name || '_model', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (field) WHERE field IS NOT NULL',
                        'idx_' || partition_name || '_field', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (model_type, model_id, field) WHERE field IS NOT NULL',
                        'idx_' || partition_name || '_field_model', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (uploaded_by) WHERE uploaded_by IS NOT NULL',
                        'idx_' || partition_name || '_uploaded_by', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (is_public, created_at DESC) WHERE is_public = true',
                        'idx_' || partition_name || '_public', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (content_type)',
                        'idx_' || partition_name || '_content_type', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (created_at)',
                        'idx_' || partition_name || '_created_at', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (model_type, model_id, sort_order) WHERE sort_order IS NOT NULL',
                        'idx_' || partition_name || '_sort_order', partition_name
                    );

                    EXECUTE format(
                        'CREATE UNIQUE INDEX %I ON %I (disk_name)',
                        'idx_' || partition_name || '_disk_name', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (uploaded_by, created_at DESC) WHERE uploaded_by IS NOT NULL',
                        'idx_' || partition_name || '_user_uploads', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I USING GIN (metadata) WHERE metadata IS NOT NULL',
                        'idx_' || partition_name || '_metadata', partition_name
                    );

                    EXECUTE format(
                        'CREATE INDEX %I ON %I (model_type, created_at DESC)',
                        'idx_' || partition_name || '_admin_overview', partition_name
                    );

                    RAISE NOTICE 'Created files partition: % for IDs % to %',
                                 partition_name, start_id, end_id - 1;
                ELSE
                    RAISE NOTICE 'Files partition % already exists', partition_name;
                END IF;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
    }
};
