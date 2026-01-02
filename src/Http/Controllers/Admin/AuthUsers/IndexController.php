<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Jiny\Auth\Services\ShardingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 관리자 - 사용자 목록 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth-users') → IndexController::__invoke()
 *
 * 메서드 호출 트리:
 * __invoke()
 * ├── [sharding_enabled = true] getShardedUsers()
 * │   ├── [shard_id 있음] getUsersFromSingleShard()
 * │   │   └── applyFilters()
 * │   └── [shard_id 없음] getUsersFromAllShards()
 * │       └── applyFilters()
 * └── [sharding_enabled = false] getNonShardedUsers()
 *     └── applyFilters()
 */
class IndexController extends Controller
{
    protected $config;

    public function __construct()
    {
        // Middleware removed - applied in routes
        $this->config = [
            'view' => 'jiny-auth::admin.auth-users.index',
            'title' => '사용자 관리',
            'subtitle' => '시스템 사용자 목록',
            'per_page' => 10,
            'sort_column' => 'created_at',
            'sort_order' => 'desc',
            'filter_search' => true,
            'filter_role' => true,
            'filter_status' => true,
        ];
    }

    /**
     * 사용자 목록 표시
     *
     * 샤딩 분기 처리:
     *
     * 1. sharding_enabled = FALSE (샤딩 비활성화)
     *    → getNonShardedUsers() 호출
     *    → 기본 'users' 테이블에서 목록 조회
     *    → 검색도 기본 'users' 테이블에서만 수행
     *
     * 2. sharding_enabled = TRUE (샤딩 활성화)
     *    → getShardedUsers() 호출
     *    → users_001, users_002, ..., users_010 샤드 테이블에서 조회
     *    → 검색 시 모든 샤드 테이블을 순회하며 검색
     */
    public function __invoke(Request $request)
    {
        // AuthUser.json에서 샤딩 설정 읽기
        $configPath = __DIR__ . '/AuthUser.json';
        $shardingEnabled = false;

        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            $shardingEnabled = $config['table']['sharding'] ?? false;
        }

        // shard_tables 테이블에서 샤드 정보 읽기
        $shardTable = ShardTable::where('table_name', 'users')->first();

        // 샤딩 통계
        // 컨테이너에서 싱글톤으로 등록된 ShardingService 인스턴스를 주입하여
        // 설정(shard.json) 및 상태를 일관되게 공유합니다.
        $shardingService = app(ShardingService::class);
        $shardStatistics = $shardingService->getShardStatistics();
        $selectedShard = $request->get('shard_id');

        // 샤딩 적용 여부에 따라 분기
        if ($shardingEnabled && $shardTable) {
            // 샤딩 활성화: users_001~010 샤드 테이블에서 조회
            $users = $this->getShardedUsers($request, $shardTable, $selectedShard);
        } else {
            // 샤딩 비활성화: users 기본 테이블에서 조회
            $users = $this->getNonShardedUsers($request);
        }

        // 전체 사용자 수 계산
        $totalUsers = $shardingEnabled
            ? ($shardStatistics['total_users'] ?? 0)
            : $users->total();

        // 역할별 분포 통계 계산
        $roleStats = [
            'admin' => 0,
            'user' => 0,
            'other' => 0
        ];

        if ($shardingEnabled && $shardTable) {
            // 모든 활성 샤드 테이블에서 집계
            for ($i = 1; $i <= $shardTable->shard_count; $i++) {
                $tName = $shardTable->getShardTableName($i);
                if (DB::getSchemaBuilder()->hasTable($tName)) {
                    $stats = DB::table($tName)
                        ->select('utype', DB::raw('count(*) as count'))
                        ->groupBy('utype')
                        ->get();
                    
                    foreach ($stats as $stat) {
                        $utype = strtoupper($stat->utype ?? 'USR');
                        if ($utype === 'ADM' || $utype === 'ADMIN') {
                            $roleStats['admin'] += $stat->count;
                        } elseif ($utype === 'USR' || $utype === 'USER') {
                            $roleStats['user'] += $stat->count;
                        } else {
                            $roleStats['other'] += $stat->count;
                        }
                    }
                }
            }
        } else {
            // 단일 테이블에서 집계
            $stats = AuthUser::select('utype', DB::raw('count(*) as count'))
                ->groupBy('utype')
                ->get();

            foreach ($stats as $stat) {
                $utype = strtoupper($stat->utype ?? 'USR');
                if ($utype === 'ADM' || $utype === 'ADMIN') {
                    $roleStats['admin'] += $stat->count;
                } elseif ($utype === 'USR' || $utype === 'USER') {
                    $roleStats['user'] += $stat->count;
                } else {
                    $roleStats['other'] += $stat->count;
                }
            }
        }

        return view($this->config['view'], compact('users', 'shardingEnabled', 'shardStatistics', 'selectedShard', 'totalUsers', 'roleStats'));
    }

    /**
     * 샤딩 테이블에서 사용자 조회
     *
     * @param Request $request
     * @param ShardTable $shardTable
     * @param int|null $selectedShard - 특정 샤드 ID (옵션)
     * @return LengthAwarePaginator
     *
     * 호출 트리:
     * ├── shard_id 파라미터 있음 → getUsersFromSingleShard()
     * │   (특정 샤드의 사용자 목록 표시)
     * └── shard_id 파라미터 없음
     *     ├── 검색어 있음 → getUsersFromAllShards()
     *     │   (모든 샤드에서 검색)
     *     └── 검색어 없음 → 빈 결과 반환
     *         (샤드 개요만 표시)
     */
    protected function getShardedUsers(Request $request, ShardTable $shardTable, $selectedShard = null)
    {
        $perPage = $this->config['per_page'];
        $page = $request->get('page', 1);

        // 특정 샤드만 조회
        if ($selectedShard) {
            return $this->getUsersFromSingleShard($request, $shardTable, $selectedShard);
        }

        // 검색어가 있으면 모든 샤드에서 검색
        if ($request->filled('search')) {
            return $this->getUsersFromAllShards($request, $shardTable);
        }

        // shard_id와 검색어가 모두 없으면 빈 결과 (샤드 개요만 표시)
        return new LengthAwarePaginator([], 0, $perPage);
    }

    /**
     * 모든 샤드에서 사용자 조회 (검색용)
     *
     * @param Request $request
     * @param ShardTable $shardTable
     * @return LengthAwarePaginator
     */
    protected function getUsersFromAllShards(Request $request, ShardTable $shardTable)
    {
        $perPage = $this->config['per_page'];
        $page = $request->get('page', 1);
        $allUsers = collect();

        // 모든 샤드 테이블을 순회
        for ($i = 1; $i <= $shardTable->shard_count; $i++) {
            $shardTableName = $shardTable->getShardTableName($i);

            if (!DB::getSchemaBuilder()->hasTable($shardTableName)) {
                continue;
            }

            $query = DB::table($shardTableName);

            // 필터 적용
            $this->applyFilters($query, $request);

            // 샤드 정보 추가
            $users = $query->get()->map(function($user) use ($i) {
                $user = (array)$user;
                $user['shard_id'] = $i;
                return (object)$user;
            });

            $allUsers = $allUsers->concat($users);
        }

        // 정렬
        $sortBy = $request->get('sort_by', $this->config['sort_column']);
        $sortOrder = $request->get('sort_order', $this->config['sort_order']);
        $allUsers = $sortOrder === 'desc'
            ? $allUsers->sortByDesc($sortBy)
            : $allUsers->sortBy($sortBy);

        // 페이지네이션
        $total = $allUsers->count();
        $offset = ($page - 1) * $perPage;
        $items = $allUsers->slice($offset, $perPage)->values();

        // AuthUser 모델로 변환
        $items = $items->map(fn($user) => AuthUser::hydrate([$user])->first());

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * 단일 샤드에서 사용자 조회
     *
     * @param Request $request
     * @param ShardTable $shardTable
     * @param int $shardId - 샤드 ID (1~10)
     * @return LengthAwarePaginator
     *
     * 처리 순서:
     * 1. 샤드 테이블명 생성 (예: users_001)
     * 2. 테이블 존재 여부 확인
     * 3. applyFilters() 호출하여 필터 적용
     * 4. 정렬 및 페이지네이션
     * 5. AuthUser 모델로 변환
     */
    protected function getUsersFromSingleShard(Request $request, ShardTable $shardTable, int $shardId)
    {
        $shardTableName = $shardTable->getShardTableName($shardId);

        if (!DB::getSchemaBuilder()->hasTable($shardTableName)) {
            return new LengthAwarePaginator([], 0, $this->config['per_page']);
        }

        $query = DB::table($shardTableName);

        // 필터 적용
        $this->applyFilters($query, $request);

        // 정렬
        $sortBy = $request->get('sort_by', $this->config['sort_column']);
        $sortOrder = $request->get('sort_order', $this->config['sort_order']);
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $users = $query->paginate($this->config['per_page'])->withQueryString();

        // AuthUser 모델로 변환
        $users->setCollection(
            $users->getCollection()->map(fn($user) => AuthUser::hydrate([$user])->first())
        );

        return $users;
    }

    /**
     * 일반 테이블에서 사용자 조회
     *
     * @param Request $request
     * @return LengthAwarePaginator
     *
     * 처리 순서:
     * 1. AuthUser 모델로 쿼리 빌더 생성
     * 2. applyFilters() 호출하여 필터 적용
     * 3. 정렬 및 페이지네이션
     *
     * 대상 테이블: users (단일 테이블)
     */
    protected function getNonShardedUsers(Request $request)
    {
        $query = AuthUser::query();

        // 필터 적용
        $this->applyFilters($query, $request);

        // 정렬
        $sortBy = $request->get('sort_by', $this->config['sort_column']);
        $sortOrder = $request->get('sort_order', $this->config['sort_order']);
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        return $query->paginate($this->config['per_page'])->withQueryString();
    }

    /**
     * 쿼리에 필터 적용
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return mixed
     *
     * 적용 필터:
     * 1. 검색: name, email, username LIKE 검색
     * 2. 역할: utype 필터 (admin → ADM, editor → EDI, user → USR)
     * 3. 상태: account_status 필터
     */
    protected function applyFilters($query, Request $request)
    {
        // 검색 필터
        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");

                // username이 null이 아닌 경우에만 검색
                if ($search) {
                    $q->orWhere(function($subQ) use ($search) {
                        $subQ->whereNotNull('username')
                             ->where('username', 'like', "%{$search}%");
                    });
                }
            });
        }

        // 역할 필터
        if ($this->config['filter_role'] && $request->filled('role') && $request->get('role') !== 'all') {
            $role = $request->get('role');
            $utype = match($role) {
                'admin' => 'ADM',
                'editor' => 'EDI',
                'user' => 'USR',
                default => null,
            };

            if ($utype) {
                $query->where('utype', $utype);
            }
        }

        // 상태 필터
        if ($this->config['filter_status'] && $request->filled('status') && $request->get('status') !== 'all') {
            $query->where('account_status', $request->get('status'));
        }

        return $query;
    }
}