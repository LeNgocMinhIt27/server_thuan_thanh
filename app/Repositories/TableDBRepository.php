<?php

namespace App\Repositories;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TableDBRepository
{
    protected $connection = 'pgsql';
    public function getSchemaConnection()
    {
        return Schema::connection($this->connection);
    }
    public function getQueryBuilderConnection()
    {
        return DB::connection($this->connection);
    }
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function updateTable($old_table_name, $new_table_name)
    {
        $this->getSchemaConnection()->rename($old_table_name, $new_table_name);
    }
    public function createTable($table_name, callable $cb = null)
    {
        $schema = $this->getSchemaConnection();
        $schema->create($table_name, function (Blueprint $table) use ($cb) {
            $table->id();
            $table->timestamps();
            if (isset($cb)) {
                $cb($table);
            }
        });
    }
    public function deleteTable($table_name)
    {
        DB::statement("DROP TABLE IF EXISTS $table_name CASCADE");
    }
    public function deleteView($table_name)
    {
        DB::statement("DROP VIEW IF EXISTS $table_name CASCADE");
    }
    public function deleteMaterializedView($table_name)
    {
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS $table_name CASCADE");
    }
    public function createColumn($table_name, $column_info, $throw_when_exists = true)
    {
        $column_info = array_merge([
            'name' => '',
            'type' => 'string',
            'default_value' => null
        ], $column_info);
        $schema = $this->getSchemaConnection();
        if ($this->hasColumn($table_name, $column_info['name'])) {
            if ($throw_when_exists) {
                abort(400, "Column '{$column_info['name']}' already exist.");
            } else {
                return;
            }
        }
        if ('geometry' === $column_info['type']) {
            DB::statement('ALTER TABLE ' .  $table_name . ' ADD COLUMN ' . $column_info['name'] . ' geometry(Geometry,' . $column_info['srid'] ?? 4326 . ');');
        } else {
            $schema->table(
                $table_name,
                function (Blueprint $table) use ($column_info) {
                    $column = null;
                    if (in_array($column_info['type'], ['string', 'select'])) {
                        $column = $table->string($column_info['name']);
                    } elseif (in_array($column_info['type'], ['text', 'photo', 'image', 'file', 'select'])) {
                        $column = $table->text($column_info['name']);
                    } elseif (in_array($column_info['type'], ['integer', 'number'])) {
                        $column = $table->unsignedInteger($column_info['name']);
                    } elseif ('bigint' === $column_info['type']) {
                        $column = $table->unsignedBigInteger($column_info['name']);
                    } elseif ('double' === $column_info['type']) {
                        $column = $table->double($column_info['name']);
                    } elseif ('date' === $column_info['type']) {
                        $column = $table->timestamp($column_info['name']);
                    } elseif ('boolean' === $column_info['type']) {
                        $column = $table->boolean($column_info['name']);
                    } elseif ('geometry' === $column_info['type']) {
                        $column = $table->geometry($column_info['name'], $column_info['srid'] ?? 4326);
                    } elseif ('json' === $column_info['type']) {
                        $column = $table->json($column_info['name']);
                    }
                    if (empty($column)) {
                        return;
                    }
                    if (!isset($column['required']) || !$column['required']) {
                        $column->nullable();
                    }
                    if (isset($column_info['column_default'])) {
                        $column->default($column_info['column_default']);
                    }
                }
            );
        }
    }
    public function renameColumn($table_name, $column_info)
    {
        $schema = $this->getSchemaConnection();
        $column_info = array_merge(['name' => null, 'old_name' => null, 'type' => 'string', 'default_value' => null], $column_info);
        if ($column_info['old_name'] !== $column_info['name']) {
            if ($this->hasColumn($table_name, $column_info['name'])) {
                abort(400, "Column '{$column_info['name']}' already exist.");
            }
            $schema->table("\"{$table_name}\"", function (Blueprint $table) use ($column_info) {
                $table->renameColumn("`{$column_info['old_name']}`", "`{$column_info['name']}`");
            });
        }
    }
    public function updateColumn($table_name, $column_info)
    {
        $schema = $this->getSchemaConnection();
        $column_info = array_merge(['name' => null, 'old_name' => null, 'old_type' => null, 'type' => 'string', 'default_value' => null], $column_info);

        if (!$this->hasColumn($table_name, $column_info['old_name'])) {
            abort(400, "There is no column with name '{$column_info['old_name']}' on table '{$table_name}'.");
        }
        $this->renameColumn($table_name, $column_info);

        if ($column_info['old_type'] == $column_info['type']) {
            return;
        }
        $queryBuilder = $this->getQueryBuilderConnection();
        if ('string' === $column_info['type']) {
            $queryBuilder->statement("ALTER TABLE $table_name ALTER COLUMN \"{$column_info['name']}\" TYPE CHARACTER VARYING USING \"{$column_info['name']}\"::character varying");
        } elseif ('text' === $column_info['type']) {
            $queryBuilder->statement("ALTER TABLE $table_name ALTER COLUMN \"{$column_info['name']}\" TYPE TEXT USING \"{$column_info['name']}\"::text");
        } else if ('integer' === $column_info['type']) {
            $queryBuilder->statement("ALTER TABLE $table_name ALTER COLUMN \"{$column_info['name']}\" TYPE INTEGER USING \"{$column_info['name']}\"::integer");
        } else if ('bigint' === $column_info['type']) {
            $queryBuilder->statement("ALTER TABLE $table_name ALTER COLUMN \"{$column_info['name']}\" TYPE BIGINT USING \"{$column_info['name']}\"::bigint");
        } else if ('double' === $column_info['type']) {
            $queryBuilder->statement("ALTER TABLE $table_name ALTER COLUMN \"{$column_info['name']}\" TYPE DOUBLE PRECISION USING \"{$column_info['name']}\"::double precision");
        } else if ('date' === $column_info['type']) {
            $queryBuilder->statement("ALTER TABLE $table_name ALTER COLUMN \"{$column_info['name']}\" TYPE TIMESTAMP USING \"{$column_info['name']}\"::timestamp");
        }
    }
    public function hasColumn($table_name, $column_name)
    {
        if (strpos($table_name, '.') !== false) {
            [$schema, $table_name] = explode('.', $table_name);
        }
        $schema = $this->getSchemaConnection();
        return $schema->hasColumn($table_name, $column_name);
    }
    public function deleteColumn($table_name, $column_info)
    {
        $schema = $this->getSchemaConnection();
        $column_info = array_merge(['name' => null], $column_info);
        if (!$this->hasColumn($table_name, $column_info['name'])) {
            abort(400, "There is no column with name '{$column_info['name']}' on table '{$table_name}'.");
        }
        $schema->table($table_name, function (Blueprint $table) use ($column_info) {
            $table->dropColumn($column_info['name']);
        });
    }
}
