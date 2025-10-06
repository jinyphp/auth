<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 샤딩 테이블 관리 모델
 */
class ShardTable extends Model
{
    protected $table = 'shard_tables';

    protected $fillable = [
        'table_name',
        'table_prefix',
        'description',
        'schema_definition',
        'is_active',
        'sharding_enabled',
        'shard_count',
        'shard_key',
        'strategy',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sharding_enabled' => 'boolean',
        'shard_count' => 'integer',
        'schema_definition' => 'array',
    ];

    /**
     * 활성화된 샤드 테이블만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 샤드 테이블 목록 생성
     */
    public function getShardTableNames(): array
    {
        $tables = [];
        $prefix = $this->table_prefix ?: $this->table_name . '_';

        for ($i = 1; $i <= $this->shard_count; $i++) {
            $shardNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            $tables[] = $prefix . $shardNumber;
        }

        return $tables;
    }

    /**
     * 특정 샤드 테이블명 가져오기
     */
    public function getShardTableName(int $shardId): string
    {
        $prefix = $this->table_prefix ?: $this->table_name . '_';
        $shardNumber = str_pad($shardId, 3, '0', STR_PAD_LEFT);
        return $prefix . $shardNumber;
    }
}
