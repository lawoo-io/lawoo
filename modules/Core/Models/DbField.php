<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Database field model representing a field in a database table.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $hint
 * @property bool $changed
 * @property bool $migrated
 * @property int $db_model_id
 * @property int|null $module_id
 * @property string|null $params
 * @property string|null $new_params
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static where(string $string, true $true)
 */
class DbField extends Model
{
    protected $fillable = [
        'name',
        'description',
        'hint',
        'params',
        'new_params',
        'db_model_id',
        'module_id',
    ];

    /**
     * Get the database model this field belongs to.
     */
    public function dbModel(): BelongsTo
    {
        return $this->belongsTo(DbModel::class);
    }

    /**
     * Get the module this field belongs to.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public static function createOrUpdate(int $dbModelId, array $fields, string $dbModelName, int $moduleId): void {
        foreach ($fields as $key => $params) {

            static::checkParams($key, $params, $dbModelName);

            $field = static::firstOrCreate(['name' => $key, 'db_model_id' => $dbModelId]);
            $field->module_id = $moduleId;
            $field->save();

            if (!$field->params) {
                $dbModel = DbModel::find($dbModelId);
                if ($dbModel) {
                    $dbModel->setMigrateOn();
                }

                $field->params = $params;
                $field->save();
            } elseif ($field->params !== $params && !$field->created) {
                $field->params = $params;
                $field->changed = true;
                $field->migrated = false;
                $field->save();
            } elseif ($field->params !== $params) {
                $field->new_params = $params;
                $field->changed = true;
                $field->migrated = false;
                $field->save();
            }
        }
    }

    public static function setToRemove(Object $dbFields, array $fields): void
    {
        foreach ($dbFields as $dbField) {
            if (!key_exists($dbField->name, $fields)) {
                $dbField->to_remove = true;
                $dbField->save();
            }
        }
    }

    public static function setToRemoveOld(int $dbModelId, array $fields, int $moduleId): void
    {
        $dbModel = DbModel::find($dbModelId);
        if ($dbModel) {
            foreach ($dbModel->dbFields()->where('module_id', $moduleId) as $dbField) {
                echo $dbField->name . "\n";
                if(!key_exists($dbField->name, $fields)) {
                    $dbField->to_remove = true;
                    $dbField->save();
                }

            }

        }
    }

    public static function removeFields($dbFields, int $moduleId): void
    {
        foreach ($dbFields as $dbField) {
            if ($dbField->to_remove && $dbField->module_id === $moduleId) {
                $dbField->delete();
            }
        }
    }

    public static function checkParams(string $fieldName, string $params, string $dbModelName): void
    {
        $params = explode('.', $params);

        // Check field type
        static::checkFieldType($params[0], $fieldName, $dbModelName);

        $baseType = array_shift($params);
        $options = $params;

        static::validateFieldOptions($baseType, $options, $fieldName, $dbModelName);
    }

    public static function checkFieldType(string $type, string $fieldName, string $dbModelName): bool
    {
        $laravel12FieldTypes = [
            'bigIncrements', 'bigInteger', 'binary', 'boolean', 'char', 'date',
            'dateTime', 'dateTimeTz', 'decimal', 'double', 'float', 'increments',
            'integer', 'ipAddress', 'json', 'jsonb', 'longText', 'macAddress',
            'mediumIncrements', 'mediumInteger', 'mediumText', 'morphs',
            'nullableMorphs', 'nullableTimestamps', 'nullableUuidMorphs',
            'rememberToken', 'smallIncrements', 'smallInteger', 'softDeletes',
            'softDeletesTz', 'string', 'text', 'time', 'timeTz', 'timestamp',
            'timestampTz', 'timestamps', 'timestampsTz', 'tinyIncrements',
            'tinyInteger', 'tinyText', 'uuid', 'uuidMorphs', 'year', 'enum',
            'foreignId', 'id',
        ];

        // Field types that MUST NOT have parameters
        $typesWithoutParams = [
            'foreignId', 'boolean', 'timestamps', 'softDeletes', 'uuid', 'id',
            'rememberToken', 'nullableTimestamps', 'timestampsTz', 'timestamps',
            'morphs', 'nullableMorphs', 'nullableUuidMorphs', 'softDeletesTz',
            'json', 'jsonb'
        ];

        // Parse base type and parameters
        $parts = explode('=', $type, 2);
        $baseType = trim($parts[0]);
        $params = isset($parts[1]) ? trim($parts[1]) : null;

        // Check if base type is valid
        if (!in_array($baseType, $laravel12FieldTypes)) {
            throw new \RuntimeException("$fieldName ($dbModelName): Field type '$baseType' is not supported.");
        }

        // ❗ Block if parameter is used for a type that does not accept it
        if ($params !== null && in_array($baseType, $typesWithoutParams, true)) {
            throw new \RuntimeException("$fieldName ($dbModelName): Field type '$baseType' must not have parameters.");
        }

        // Check required parameters for certain types
        switch ($baseType) {
            case 'enum':
                if (!$params || !preg_match('/^(\w+),\[(.+)\]$/', $params, $matches)) {
                    throw new \RuntimeException("$fieldName ($dbModelName): Enum field must be in format: enum=field,[option1,option2]");
                }

                $enumOptions = array_map('trim', explode(',', $matches[2]));
                if (count($enumOptions) < 2) {
                    throw new \RuntimeException("$fieldName ($dbModelName): Enum field must have at least two options.");
                }
                break;

            case 'decimal':
            case 'double':
            case 'float':
                if (!$params || !preg_match('/^\d+,\d+$/', $params)) {
                    throw new \RuntimeException("$fieldName ($dbModelName): Field type '$baseType' requires precision and scale, e.g. $baseType=8,2");
                }
                break;

            case 'char':
                if (!$params || !preg_match('/^\d+$/', $params)) {
                    throw new \RuntimeException("$fieldName ($dbModelName): Field type 'char' requires a length, e.g. char=4");
                }
                break;
        }

        return true;
    }

    public static function validateFieldOptions(string $baseType, array $options, string $fieldName = '', string $modelName = ''): bool
    {
        // General Laravel modifiers
        $validModifiers = [
            'nullable', 'unique', 'index', 'unsigned', 'comment', 'default',
            'useCurrent', 'primary', 'autoIncrement', 'first', 'after',
            'charset', 'collation', 'storedAs', 'virtualAs', 'generatedAs',
            'persisted', 'spatialIndex', 'constrained',
            'references', 'on', 'onDelete', 'onUpdate',
            'cascadeOnDelete', 'nullOnDelete',
        ];

        // Modifiers that require a parameter
        $modifiersRequiringValue = [
            'default', 'comment', 'after', 'charset', 'collation',
            'storedAs', 'virtualAs', 'generatedAs', 'references', 'on',
            'onDelete', 'onUpdate'
        ];

        // Values for onDelete/onUpdate allowed in PostgreSQL
        $fkActions = ['cascade', 'restrict', 'set null', 'no action'];

        // Incompatible combinations
        $incompatible = [
            'json' => ['unsigned', 'index', 'unique', 'foreign'],
            'text' => ['unsigned', 'default', 'foreign'],
            'boolean' => ['unsigned', 'default=null'],
            'enum' => ['unsigned'],
            'date' => ['unsigned'],
            'timestamp' => ['unsigned'],
        ];

        foreach ($options as $option) {
            $parts = explode('=', $option, 2);
            $modifier = trim($parts[0]);
            $value = $parts[1] ?? null;

            if (!in_array($modifier, $validModifiers)) {
                throw new \RuntimeException("Unsupported option '$modifier' for field '$fieldName' in model '$modelName'.");
            }

            // Check if modifier requires a value
            if (in_array($modifier, $modifiersRequiringValue) && $value === null) {
                throw new \RuntimeException("Option '$modifier' requires a value for field '$fieldName' in model '$modelName'.");
            }

            // Validate specific option values
            if (in_array($modifier, ['onDelete', 'onUpdate']) && !in_array(strtolower($value), $fkActions)) {
                throw new \RuntimeException("Invalid value '$value' for '$modifier'. Allowed: " . implode(', ', $fkActions));
            }

            // Field type compatibility check
            if (isset($incompatible[$baseType]) && in_array($modifier, $incompatible[$baseType])) {
                throw new \RuntimeException("Modifier '$modifier' is not compatible with field type '$baseType' (field: '$fieldName' in model '$modelName').");
            }
        }

        return true;
    }

    /**
     * Set migrated on
     */
    public function setMigrateOff(): void
    {
        if(!$this->created) $this->created = true;
        $this->changed = false;
        $this->migrated = true;
        if ($this->new_params) {
            $this->params = $this->new_params;
            $this->new_params = null;
        }
        $this->save();
    }
}
