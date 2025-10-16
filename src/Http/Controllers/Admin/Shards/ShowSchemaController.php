<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\ShardTableService;

/**
 * 샤드 테이블 스키마 조회 컨트롤러
 */
class ShowSchemaController extends Controller
{
    protected $shardTableService;

    public function __construct(ShardTableService $shardTableService)
    {
        $this->shardTableService = $shardTableService;
    }

    /**
     * 테이블 스키마 정보 반환 (AJAX)
     */
    public function __invoke(Request $request)
    {
        $tableName = $request->input('table_name');

        if (!$tableName) {
            return response()->json(['error' => '테이블명이 필요합니다.'], 400);
        }

        $schema = $this->shardTableService->getTableSchema($tableName);
        $driver = \DB::connection()->getDriverName();

        return response()->json([
            'table_name' => $tableName,
            'driver' => $driver,
            'columns' => $schema,
        ]);
    }
}
