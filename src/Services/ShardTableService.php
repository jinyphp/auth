<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Jiny\Auth\Models\ShardTable;

/**
 * 샤드 테이블 관리 서비스
 */
class ShardTableService
{
    /**
     * 샤드 테이블 목록 조회
     */
    public function getShardTableList(string $tableName): array
    {
        $shardTable = ShardTable::where('table_name', $tableName)->first();

        if (!$shardTable) {
            return [];
        }

        $shards = [];
        $prefix = $shardTable->table_prefix ?: $tableName . '_';

        for ($i = 1; $i <= $shardTable->shard_count; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $shardTableName = $prefix . $shardNumber;

            $exists = Schema::hasTable($shardTableName);
            $recordCount = $exists ? DB::table($shardTableName)->count() : 0;

            $shards[] = [
                'shard_id' => $i,
                'table_name' => $shardTableName,
                'exists' => $exists,
                'record_count' => $recordCount,
                'status' => $exists ? 'active' : 'not_created',
            ];
        }

        return $shards;
    }

    /**
     * 특정 샤드 테이블 생성
     */
    public function createShard(ShardTable $shardTable, int $shardId): bool
    {
        $shardTableName = $shardTable->getShardTableName($shardId);

        if (Schema::hasTable($shardTableName)) {
            return false;
        }

        // 스키마 정의가 있으면 사용, 없으면 기본 스키마
        if ($shardTable->schema_definition && is_array($shardTable->schema_definition)) {
            $this->createTableFromSchema($shardTableName, $shardTable->schema_definition);
        } else {
            $this->createDefaultTable($shardTableName, $shardTable->table_name);
        }

        return true;
    }

    /**
     * 스키마 정의로 테이블 생성
     */
    protected function createTableFromSchema(string $tableName, array $schema): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($schema) {
            $table->id();

            foreach ($schema as $column) {
                $this->addColumnFromDefinition($table, $column);
            }

            $table->timestamps();
        });
    }

    /**
     * 컬럼 정의 추가
     */
    protected function addColumnFromDefinition(Blueprint $table, array $column): void
    {
        $name = $column['name'];
        $type = $column['type'] ?? 'string';
        $nullable = $column['nullable'] ?? false;
        $default = $column['default'] ?? null;
        $unique = $column['unique'] ?? false;
        $index = $column['index'] ?? false;

        $col = match($type) {
            'string' => $table->string($name, $column['length'] ?? 255),
            'text' => $table->text($name),
            'integer' => $table->integer($name),
            'bigInteger' => $table->bigInteger($name),
            'boolean' => $table->boolean($name),
            'date' => $table->date($name),
            'datetime' => $table->dateTime($name),
            'timestamp' => $table->timestamp($name),
            'json' => $table->json($name),
            default => $table->string($name),
        };

        if ($nullable) {
            $col->nullable();
        }

        if ($default !== null) {
            $col->default($default);
        }

        if ($unique) {
            $col->unique();
        }

        if ($index) {
            $table->index($name);
        }
    }

    /**
     * 기본 테이블 생성 (테이블명에 따라)
     */
    protected function createDefaultTable(string $tableName, string $baseTableName): void
    {
        switch ($baseTableName) {
            case 'users':
                $this->createUsersTable($tableName);
                break;
            case 'profiles':
                $this->createProfilesTable($tableName);
                break;
            case 'addresses':
                $this->createAddressesTable($tableName);
                break;
            case 'phones':
                $this->createPhonesTable($tableName);
                break;
            case 'user_avata':
                $this->createUserAvataTable($tableName);
                break;
            default:
                $this->createGenericTable($tableName);
                break;
        }
    }

    /**
     * Users 샤드 테이블 생성
     */
    protected function createUsersTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            // 추가 필드
            $table->string('uuid')->nullable()->unique();
            $table->integer('shard_id')->nullable();
            $table->string('username')->nullable();
            $table->string('utype')->default('USR');
            $table->string('grade')->nullable();
            $table->string('isAdmin')->default('0');
            $table->string('account_status')->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->integer('login_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();

            // 인덱스
            $table->index('email');
            $table->index('uuid');
            $table->index('username');
            $table->index('shard_id');
        });
    }

    /**
     * Profiles 샤드 테이블 생성
     */
    protected function createProfilesTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid');
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('language')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();

            $table->index('user_uuid');
        });
    }

    /**
     * Addresses 샤드 테이블 생성
     */
    protected function createAddressesTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid');
            $table->string('type')->default('shipping'); // shipping, billing
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code');
            $table->string('country');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('user_uuid');
        });
    }

    /**
     * Phones 샤드 테이블 생성
     */
    protected function createPhonesTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid');
            $table->string('phone_number');
            $table->string('country_code')->default('+82');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('user_uuid');
            $table->index('phone_number');
        });
    }

    /**
     * UserAvata 샤드 테이블 생성
     */
    protected function createUserAvataTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->default('1');
            $table->string('user_uuid');
            $table->string('selected')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('manager_id')->default(0);

            $table->index('user_uuid');
            $table->index('selected');
            $table->index('created_at');
        });
    }

    /**
     * 범용 테이블 생성
     */
    protected function createGenericTable(string $tableName): void
    {
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('user_uuid')->index();
            $table->text('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * 모든 샤드 테이블 생성
     */
    public function createAllShards(ShardTable $shardTable): array
    {
        $results = [];

        for ($i = 1; $i <= $shardTable->shard_count; $i++) {
            $created = $this->createShard($shardTable, $i);
            $results[$i] = $created ? 'created' : 'already_exists';
        }

        return $results;
    }

    /**
     * 특정 샤드 테이블 삭제
     */
    public function deleteShard(ShardTable $shardTable, int $shardId): bool
    {
        $shardTableName = $shardTable->getShardTableName($shardId);

        if (!Schema::hasTable($shardTableName)) {
            return false;
        }

        Schema::dropIfExists($shardTableName);
        return true;
    }

    /**
     * 모든 샤드 테이블 삭제
     */
    public function deleteAllShards(ShardTable $shardTable): array
    {
        $results = [];

        // 실제 DB에 존재하는 모든 샤드 테이블 찾기
        $prefix = $shardTable->table_prefix ?: $shardTable->table_name . '_';
        $existingShards = $this->findExistingShardTables($prefix);

        // 존재하는 모든 샤드 테이블 삭제
        foreach ($existingShards as $shardTableName) {
            Schema::dropIfExists($shardTableName);
            $results[] = 'deleted';
        }

        return $results;
    }

    /**
     * 실제 DB에 존재하는 샤드 테이블 찾기
     */
    protected function findExistingShardTables(string $prefix): array
    {
        $driver = DB::connection()->getDriverName();
        $allTables = [];

        switch ($driver) {
            case 'sqlite':
                $results = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE '{$prefix}%'");
                $allTables = array_map(fn($r) => $r->name, $results);
                break;
            case 'mysql':
                $database = DB::connection()->getDatabaseName();
                $results = DB::select("SHOW TABLES LIKE '{$prefix}%'");
                $key = "Tables_in_{$database}";
                $allTables = array_map(fn($r) => $r->$key, $results);
                break;
            case 'pgsql':
                $results = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename LIKE '{$prefix}%'");
                $allTables = array_map(fn($r) => $r->tablename, $results);
                break;
        }

        return $allTables;
    }

    /**
     * 샤드 통계 정보
     */
    public function getShardStatistics(ShardTable $shardTable): array
    {
        $shards = $this->getShardTableList($shardTable->table_name);

        return [
            'total_shards' => count($shards),
            'active_shards' => count(array_filter($shards, fn($s) => $s['exists'])),
            'total_records' => array_sum(array_column($shards, 'record_count')),
            'shards' => $shards,
        ];
    }

    /**
     * 테이블 스키마 정보 조회
     */
    public function getTableSchema(string $tableName): array
    {
        if (!Schema::hasTable($tableName)) {
            return [];
        }

        $driver = DB::connection()->getDriverName();
        $columns = [];

        switch ($driver) {
            case 'sqlite':
                $columns = $this->getSqliteSchema($tableName);
                break;
            case 'mysql':
                $columns = $this->getMysqlSchema($tableName);
                break;
            case 'pgsql':
                $columns = $this->getPgsqlSchema($tableName);
                break;
            default:
                $columns = $this->getGenericSchema($tableName);
        }

        return $columns;
    }

    /**
     * SQLite 스키마 조회
     */
    protected function getSqliteSchema(string $tableName): array
    {
        $results = DB::select("PRAGMA table_info({$tableName})");
        $columns = [];

        foreach ($results as $column) {
            $columns[] = [
                'name' => $column->name,
                'type' => $column->type,
                'nullable' => $column->notnull == 0,
                'default' => $column->dflt_value,
                'key' => $column->pk == 1 ? 'PRI' : '',
            ];
        }

        return $columns;
    }

    /**
     * MySQL 스키마 조회
     */
    protected function getMysqlSchema(string $tableName): array
    {
        $results = DB::select("SHOW COLUMNS FROM {$tableName}");
        $columns = [];

        foreach ($results as $column) {
            $columns[] = [
                'name' => $column->Field,
                'type' => $column->Type,
                'nullable' => $column->Null === 'YES',
                'default' => $column->Default,
                'key' => $column->Key,
            ];
        }

        return $columns;
    }

    /**
     * PostgreSQL 스키마 조회
     */
    protected function getPgsqlSchema(string $tableName): array
    {
        $results = DB::select("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_name = ?
            ORDER BY ordinal_position
        ", [$tableName]);

        $columns = [];

        foreach ($results as $column) {
            $columns[] = [
                'name' => $column->column_name,
                'type' => $column->data_type,
                'nullable' => $column->is_nullable === 'YES',
                'default' => $column->column_default,
                'key' => '',
            ];
        }

        return $columns;
    }

    /**
     * 범용 스키마 조회 (Laravel Schema 사용)
     */
    protected function getGenericSchema(string $tableName): array
    {
        $columnNames = Schema::getColumnListing($tableName);
        $columns = [];

        foreach ($columnNames as $columnName) {
            $columns[] = [
                'name' => $columnName,
                'type' => Schema::getColumnType($tableName, $columnName),
                'nullable' => true,
                'default' => null,
                'key' => '',
            ];
        }

        return $columns;
    }
}
