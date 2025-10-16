<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserApproval\Logs;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 사용자 승인 로그 관리 컨트롤러
 *
 * 회원가입 승인/거부 이력을 조회하고 관리하는 기능을 제공합니다.
 * 라우트: /admin/auth/logs/approval
 */
class UserApprovalLogsController extends Controller
{
    // 라우트에서 admin 미들웨어가 적용되므로 컨트롤러에서는 제거

    /**
     * 승인 로그 목록 표시
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // user_approval_logs 테이블 존재 여부 확인
        if (!Schema::hasTable('user_approval_logs')) {
            return view('jiny-auth::admin.user-approval.logs.index', [
                'logs' => collect([]),
                'stats' => $this->getEmptyStats(),
                'filters' => $this->getDefaultFilters(),
                'error' => 'user_approval_logs 테이블이 존재하지 않습니다. 마이그레이션을 실행해주세요.',
                'title' => '사용자 승인 로그',
                'subtitle' => '회원가입 승인/거부 이력을 관리합니다',
            ]);
        }

        // 필터링 파라미터
        $action = $request->get('action');
        $adminUserId = $request->get('admin_user_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);

        // 쿼리 빌더 시작
        $query = DB::table('user_approval_logs')
            ->orderBy('processed_at', 'desc')
            ->orderBy('created_at', 'desc');

        // 필터 적용
        if ($action) {
            $query->where('action', $action);
        }

        if ($adminUserId) {
            $query->where('admin_user_id', $adminUserId);
        }

        if ($dateFrom) {
            $query->whereDate('processed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('processed_at', '<=', $dateTo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('admin_user_name', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        // 페이지네이션과 함께 데이터 조회
        $logs = $query->paginate($perPage)->appends($request->query());

        // 통계 데이터
        $stats = $this->getApprovalStats($dateFrom, $dateTo);

        // 필터 옵션
        $filters = $this->getFilterOptions();

        return view('jiny-auth::admin.user-approval.logs.index', [
            'logs' => $logs,
            'stats' => $stats,
            'filters' => $filters,
            'currentFilters' => [
                'action' => $action,
                'admin_user_id' => $adminUserId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
                'per_page' => $perPage,
            ],
            'title' => '사용자 승인 로그',
            'subtitle' => '회원가입 승인/거부 이력을 관리합니다',
        ]);
    }

    /**
     * 승인 로그 상세 정보
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        if (!Schema::hasTable('user_approval_logs')) {
            abort(404, 'user_approval_logs 테이블이 존재하지 않습니다.');
        }

        $log = DB::table('user_approval_logs')->where('id', $id)->first();

        if (!$log) {
            abort(404, '승인 로그를 찾을 수 없습니다.');
        }

        // 관련 사용자 정보 조회 (가능한 경우)
        $user = null;
        if ($log->user_id) {
            try {
                $user = DB::table('users')->where('id', $log->user_id)->first();
            } catch (\Exception $e) {
                // 사용자 조회 실패해도 진행
            }
        }

        // 관련 관리자 정보 조회 (가능한 경우)
        $admin = null;
        if ($log->admin_user_id) {
            try {
                $admin = DB::table('users')
                    ->where('id', $log->admin_user_id)
                    ->where(function ($q) {
                        $q->where('utype', 'ADM')
                          ->orWhere('isAdmin', true);
                    })
                    ->first();
            } catch (\Exception $e) {
                // 관리자 조회 실패해도 진행
            }
        }

        return view('jiny-auth::admin.user-approval.logs.show', [
            'log' => $log,
            'user' => $user,
            'admin' => $admin,
            'title' => '승인 로그 상세',
            'subtitle' => '승인 로그 상세 정보를 확인합니다',
        ]);
    }

    /**
     * 승인 통계 조회
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    protected function getApprovalStats($dateFrom = null, $dateTo = null)
    {
        if (!Schema::hasTable('user_approval_logs')) {
            return $this->getEmptyStats();
        }

        try {
            $query = DB::table('user_approval_logs');

            if ($dateFrom) {
                $query->whereDate('processed_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('processed_at', '<=', $dateTo);
            }

            $stats = $query->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN action = "auto_approved" THEN 1 ELSE 0 END) as auto_approved,
                SUM(CASE WHEN action = "approved" THEN 1 ELSE 0 END) as manual_approved,
                SUM(CASE WHEN action = "rejected" THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN action = "pending" THEN 1 ELSE 0 END) as pending
            ')->first();

            return [
                'total' => $stats->total ?? 0,
                'auto_approved' => $stats->auto_approved ?? 0,
                'manual_approved' => $stats->manual_approved ?? 0,
                'rejected' => $stats->rejected ?? 0,
                'pending' => $stats->pending ?? 0,
                'approved_total' => ($stats->auto_approved ?? 0) + ($stats->manual_approved ?? 0),
            ];

        } catch (\Exception $e) {
            return $this->getEmptyStats();
        }
    }

    /**
     * 빈 통계 반환
     *
     * @return array
     */
    protected function getEmptyStats()
    {
        return [
            'total' => 0,
            'auto_approved' => 0,
            'manual_approved' => 0,
            'rejected' => 0,
            'pending' => 0,
            'approved_total' => 0,
        ];
    }

    /**
     * 필터 옵션 조회
     *
     * @return array
     */
    protected function getFilterOptions()
    {
        $options = [
            'actions' => [
                '' => '전체',
                'auto_approved' => '자동 승인',
                'approved' => '관리자 승인',
                'rejected' => '거부',
                'pending' => '대기',
            ],
            'admins' => [
                '' => '전체',
            ],
            'per_page_options' => [
                10 => '10개씩',
                20 => '20개씩',
                50 => '50개씩',
                100 => '100개씩',
            ],
        ];

        // 관리자 목록 조회 (가능한 경우)
        try {
            if (Schema::hasTable('user_approval_logs')) {
                $admins = DB::table('user_approval_logs')
                    ->whereNotNull('admin_user_id')
                    ->whereNotNull('admin_user_name')
                    ->select('admin_user_id', 'admin_user_name')
                    ->distinct()
                    ->orderBy('admin_user_name')
                    ->get();

                foreach ($admins as $admin) {
                    $options['admins'][$admin->admin_user_id] = $admin->admin_user_name;
                }
            }
        } catch (\Exception $e) {
            // 관리자 목록 조회 실패해도 진행
        }

        return $options;
    }

    /**
     * 기본 필터 반환
     *
     * @return array
     */
    protected function getDefaultFilters()
    {
        return [
            'actions' => [
                '' => '전체',
                'auto_approved' => '자동 승인',
                'approved' => '관리자 승인',
                'rejected' => '거부',
                'pending' => '대기',
            ],
            'admins' => [
                '' => '전체',
            ],
            'per_page_options' => [
                10 => '10개씩',
                20 => '20개씩',
                50 => '50개씩',
                100 => '100개씩',
            ],
        ];
    }
}