<?php

namespace Jiny\Auth\Http\Controllers\Admin\Shards;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 샤드 테이블 스키마 조회 컨트롤러
 */
class ShowSchemaController extends Controller
{
    /**
     * 테이블 스키마 정보 반환 (AJAX)
     */
    public function __invoke(Request $request)
    {
        $tableName = $request->input('table_name');

        if (!$tableName) {
            return response()->json(['error' => '테이블명이 필요합니다.'], 400);
        }

        // 데이터베이스 드라이버별로 컬럼 정보를 조회합니다.
        // doctrine/dbal 의존성 없이 가능한 범위에서 정보를 제공합니다.
        $driver = DB::connection()->getDriverName();
        $columns = [];

        try {
            if (!Schema::hasTable($tableName)) {
                return response()->json(['error' => "테이블 '{$tableName}' 이(가) 존재하지 않습니다."], 404);
            }

            switch ($driver) {
                case 'mysql':
                case 'mariadb':
                    // MySQL/MariaDB: SHOW COLUMNS
                    $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");
                    break;
                case 'pgsql':
                    // PostgreSQL: information_schema.columns
                    $columns = DB::select("
                        SELECT column_name AS field, data_type AS type, is_nullable, column_default
                        FROM information_schema.columns
                        WHERE table_name = ?
                        ORDER BY ordinal_position
                    ", [$tableName]);
                    break;
                case 'sqlite':
                    // SQLite: PRAGMA table_info
                    $columns = DB::select("PRAGMA table_info('{$tableName}')");
                    break;
                case 'sqlsrv':
                    // SQL Server: sys.columns + sys.types
                    $columns = DB::select("
                        SELECT c.name AS field, t.name AS type, c.is_nullable, c.column_id
                        FROM sys.columns c
                        JOIN sys.types t ON c.user_type_id = t.user_type_id
                        WHERE c.object_id = OBJECT_ID(?)
                        ORDER BY c.column_id
                    ", [$tableName]);
                    break;
                default:
                    // 지원하지 않는 드라이버: 컬럼명만 반환
                    $columnNames = Schema::getColumnListing($tableName);
                    $columns = array_map(fn($name) => ['field' => $name], $columnNames);
                    break;
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => '스키마 조회 중 오류가 발생했습니다.', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'table_name' => $tableName,
            'driver' => $driver,
            'columns' => $columns,
        ]);
    }
}
