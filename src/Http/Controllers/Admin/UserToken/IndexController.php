<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserToken;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Facades\Shard;
use Jiny\Auth\Services\JwtAuthService;
use App\Models\User;

/**
 * 관리자: JWT 토큰 목록 조회 컨트롤러
 *
 * JWT 로그인 시 발급된 토큰들의 목록을 조회하고 관리합니다.
 * jwt_tokens 테이블에서 토큰 정보를 조회하며, 사용자 정보는 샤딩 여부에 따라 조회합니다.
 *
 * 주요 기능:
 * - 토큰 목록 조회 및 필터링
 * - 단일 토큰 폐기
 * - 사용자의 모든 토큰 폐기 (특정 사용자 로그인 해제)
 */
class IndexController extends Controller
{
    /**
     * JWT 인증 서비스
     *
     * @var JwtAuthService
     */
    protected $jwtService;

    /**
     * 생성자: JWT 인증 서비스 주입
     *
     * @param JwtAuthService $jwtService
     */
    public function __construct(JwtAuthService $jwtService)
    {
        $this->jwtService = $jwtService;
    }
    /**
     * JWT 토큰 목록 표시
     *
     * 처리 흐름:
     * 1. jwt_tokens 테이블에서 토큰 목록 조회 (페이지네이션)
     * 2. 각 토큰의 user_id로 사용자 정보 조회 (샤딩 지원)
     * 3. 통계 정보 계산 (전체, 활성, 폐기, 만료)
     * 4. 필터링 및 검색 기능 제공
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function __invoke(Request $request)
    {
        // jwt_tokens 테이블 존재 여부 확인
        $tableExists = DB::getSchemaBuilder()->hasTable('jwt_tokens');

        if (!$tableExists) {
            // 테이블이 없으면 빈 결과와 안내 메시지 반환
            return view('jiny-auth::admin.user-token.index', [
                'tokens' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1),
                'stats' => [
                    'total' => 0,
                    'active' => 0,
                    'revoked' => 0,
                    'expired' => 0,
                    'access_tokens' => 0,
                    'refresh_tokens' => 0,
                ],
                'table_exists' => false,
                'error_message' => 'jwt_tokens 테이블이 존재하지 않습니다. migration을 실행해주세요.',
            ]);
        }

        try {
            // 통계 데이터 조회
            $stats = $this->getStatistics();

            // 토큰 목록 조회 쿼리 생성
            $query = DB::table('jwt_tokens');

            // 필터 적용
            $this->applyFilters($query, $request);

            // 정렬 (기본값: 최신순)
            $sortBy = $request->get('sort_by', 'issued_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 페이지네이션
            $perPage = $request->get('per_page', 20);
            $tokens = $query->paginate($perPage)->withQueryString();

            // 각 토큰에 사용자 정보 추가
            $tokens->getCollection()->transform(function ($token) {
                $token->user = $this->getUserInfo($token->user_id);
                return $token;
            });

            return view('jiny-auth::admin.user-token.index', [
                'tokens' => $tokens,
                'stats' => $stats,
                'table_exists' => true,
            ]);
        } catch (\Exception $e) {
            // 에러 발생 시 로그 기록 및 빈 결과 반환
            \Log::error('JWT Token List Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('jiny-auth::admin.user-token.index', [
                'tokens' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1),
                'stats' => [
                    'total' => 0,
                    'active' => 0,
                    'revoked' => 0,
                    'expired' => 0,
                    'access_tokens' => 0,
                    'refresh_tokens' => 0,
                ],
                'table_exists' => true,
                'error_message' => '토큰 목록을 조회하는 중 오류가 발생했습니다: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * 토큰 통계 정보 조회
     *
     * 전체 토큰 수, 활성 토큰 수, 폐기된 토큰 수, 만료된 토큰 수를 계산합니다.
     *
     * @return array
     */
    protected function getStatistics()
    {
        // 테이블 존재 여부 확인
        if (!DB::getSchemaBuilder()->hasTable('jwt_tokens')) {
            return [
                'total' => 0,
                'active' => 0,
                'revoked' => 0,
                'expired' => 0,
                'access_tokens' => 0,
                'refresh_tokens' => 0,
            ];
        }

        try {
            $now = now();

            return [
                'total' => DB::table('jwt_tokens')->count(),
                'active' => DB::table('jwt_tokens')
                    ->where('revoked', false)
                    ->where('expires_at', '>', $now)
                    ->count(),
                'revoked' => DB::table('jwt_tokens')
                    ->where('revoked', true)
                    ->count(),
                'expired' => DB::table('jwt_tokens')
                    ->where('revoked', false)
                    ->where('expires_at', '<=', $now)
                    ->count(),
                'access_tokens' => DB::table('jwt_tokens')
                    ->where('token_type', 'access')
                    ->count(),
                'refresh_tokens' => DB::table('jwt_tokens')
                    ->where('token_type', 'refresh')
                    ->count(),
            ];
        } catch (\Exception $e) {
            \Log::error('JWT Token Statistics Error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total' => 0,
                'active' => 0,
                'revoked' => 0,
                'expired' => 0,
                'access_tokens' => 0,
                'refresh_tokens' => 0,
            ];
        }
    }

    /**
     * 쿼리에 필터 적용
     *
     * 지원하는 필터:
     * - token_type: access 또는 refresh
     * - revoked: 폐기 여부 (true/false)
     * - expired: 만료 여부 (true/false)
     * - user_id: 특정 사용자의 토큰만 조회
     * - search: 사용자 이메일 또는 이름으로 검색
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param Request $request
     * @return void
     */
    protected function applyFilters($query, Request $request)
    {
        // 토큰 타입 필터
        if ($request->filled('token_type') && $request->token_type !== 'all') {
            $query->where('token_type', $request->token_type);
        }

        // 폐기 여부 필터
        if ($request->filled('revoked')) {
            $query->where('revoked', $request->revoked === 'true' ? true : false);
        }

        // 만료 여부 필터
        if ($request->filled('expired') && $request->expired === 'true') {
            $query->where('expires_at', '<=', now());
        } elseif ($request->filled('expired') && $request->expired === 'false') {
            $query->where('expires_at', '>', now());
        }

        // 사용자 ID 필터
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 검색 필터 (사용자 이메일 또는 이름으로 검색)
        if ($request->filled('search')) {
            $search = $request->search;
            // user_id로 사용자를 찾아서 필터링
            $userIds = $this->searchUserIds($search);
            if (!empty($userIds)) {
                $query->whereIn('user_id', $userIds);
            } else {
                // 검색 결과가 없으면 빈 결과 반환
                $query->whereRaw('1 = 0');
            }
        }
    }

    /**
     * 검색어로 사용자 ID 목록 조회
     *
     * 이메일 또는 이름으로 사용자를 검색하여 user_id 목록을 반환합니다.
     * 샤딩이 활성화된 경우 모든 샤드에서 검색합니다.
     *
     * @param string $search
     * @return array
     */
    protected function searchUserIds($search)
    {
        $userIds = [];

        // 샤딩이 활성화된 경우
        if (Shard::isEnabled()) {
            // 모든 샤드에서 검색
            $shardTable = DB::table('shard_tables')->where('table_name', 'users')->first();
            if ($shardTable) {
                for ($i = 1; $i <= $shardTable->shard_count; $i++) {
                    $shardTableName = 'users_' . str_pad($i, 3, '0', STR_PAD_LEFT);
                    if (DB::getSchemaBuilder()->hasTable($shardTableName)) {
                        $users = DB::table($shardTableName)
                            ->where(function ($q) use ($search) {
                                $q->where('email', 'like', "%{$search}%")
                                  ->orWhere('name', 'like', "%{$search}%");
                            })
                            ->pluck('id')
                            ->toArray();
                        $userIds = array_merge($userIds, $users);
                    }
                }
            }
        } else {
            // 일반 users 테이블에서 검색
            $userIds = User::where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            })
            ->pluck('id')
            ->toArray();
        }

        return array_unique($userIds);
    }

    /**
     * 사용자 정보 조회
     *
     * user_id로 사용자 정보를 조회합니다.
     * 샤딩이 활성화된 경우 모든 샤드 테이블을 순회하며 조회합니다.
     *
     * 처리 순서:
     * 1. 기본 users 테이블에서 조회 시도
     * 2. 샤딩이 활성화된 경우 모든 샤드 테이블에서 조회 시도
     * 3. 찾은 사용자 정보를 표준 객체로 반환
     *
     * @param int|null $userId
     * @return object|null
     */
    protected function getUserInfo($userId)
    {
        if (!$userId) {
            return null;
        }

        // 1. 기본 users 테이블에서 먼저 조회
        $user = User::find($userId);
        if ($user) {
            return (object) [
                'id' => $user->id,
                'uuid' => $user->uuid ?? null,
                'name' => $user->name,
                'email' => $user->email,
            ];
        }

        // 2. 샤딩이 활성화된 경우 모든 샤드 테이블에서 조회
        if (Shard::isEnabled()) {
            try {
                // 샤드 테이블 정보 조회
                $shardTable = DB::table('shard_tables')->where('table_name', 'users')->first();
                if ($shardTable) {
                    // 모든 샤드 테이블을 순회하며 user_id로 조회
                    for ($i = 1; $i <= $shardTable->shard_count; $i++) {
                        $shardTableName = 'users_' . str_pad($i, 3, '0', STR_PAD_LEFT);

                        if (DB::getSchemaBuilder()->hasTable($shardTableName)) {
                            $userData = DB::table($shardTableName)
                                ->where('id', $userId)
                                ->first();

                            if ($userData) {
                                return (object) [
                                    'id' => $userData->id ?? null,
                                    'uuid' => $userData->uuid ?? null,
                                    'name' => $userData->name ?? null,
                                    'email' => $userData->email ?? null,
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // 샤드 조회 실패 시 로그 기록
                \Log::warning('Failed to get user from sharded tables', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * 단일 토큰 폐기
     *
     * 특정 토큰 ID로 토큰을 폐기합니다.
     * 폐기된 토큰은 더 이상 사용할 수 없습니다.
     *
     * 처리 흐름:
     * 1. 요청에서 token_id 추출
     * 2. JwtAuthService를 통해 토큰 폐기
     * 3. 성공/실패 응답 반환
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeToken(Request $request)
    {
        $request->validate([
            'token_id' => 'required|string',
        ]);

        try {
            $tokenId = $request->input('token_id');

            // 토큰 존재 여부 확인
            $token = DB::table('jwt_tokens')
                ->where('token_id', $tokenId)
                ->first();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => '토큰을 찾을 수 없습니다.',
                ], 404);
            }

            // 이미 폐기된 토큰인지 확인
            if ($token->revoked) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 폐기된 토큰입니다.',
                ], 400);
            }

            // 토큰 폐기 실행
            $result = $this->jwtService->revokeToken($tokenId);

            if ($result) {
                \Log::info('JWT token revoked by admin', [
                    'token_id' => $tokenId,
                    'user_id' => $token->user_id,
                    'admin_id' => auth()->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => '토큰이 성공적으로 폐기되었습니다.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '토큰 폐기에 실패했습니다.',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('JWT token revoke error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '토큰 폐기 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 사용자의 모든 토큰 폐기 (특정 사용자 로그인 해제)
     *
     * 특정 사용자의 모든 활성 토큰을 폐기하여 해당 사용자의 모든 세션을 종료합니다.
     * 이 기능은 사용자가 여러 디바이스에서 로그인한 경우 모든 디바이스에서 로그아웃시키는 데 사용됩니다.
     *
     * 처리 흐름:
     * 1. 요청에서 user_id 추출
     * 2. 해당 사용자의 모든 활성 토큰 조회
     * 3. JwtAuthService를 통해 모든 토큰 폐기
     * 4. 폐기된 토큰 수와 함께 성공 응답 반환
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAllUserTokens(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        try {
            $userId = $request->input('user_id');

            // 사용자 존재 여부 확인
            $user = $this->getUserInfo($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '사용자를 찾을 수 없습니다.',
                ], 404);
            }

            // 폐기 전 활성 토큰 수 확인
            $activeTokenCount = DB::table('jwt_tokens')
                ->where('user_id', $userId)
                ->where('revoked', false)
                ->where('expires_at', '>', now())
                ->count();

            if ($activeTokenCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => '폐기할 활성 토큰이 없습니다.',
                ], 400);
            }

            // 사용자의 모든 토큰 폐기 실행
            $result = $this->jwtService->revokeAllUserTokens($userId);

            if ($result) {
                \Log::info('All user JWT tokens revoked by admin', [
                    'user_id' => $userId,
                    'revoked_count' => $activeTokenCount,
                    'admin_id' => auth()->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "사용자의 모든 토큰({$activeTokenCount}개)이 성공적으로 폐기되었습니다.",
                    'revoked_count' => $activeTokenCount,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => '토큰 폐기에 실패했습니다.',
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('JWT token revoke all error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '토큰 폐기 중 오류가 발생했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }
}

