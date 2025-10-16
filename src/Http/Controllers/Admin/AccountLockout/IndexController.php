<?php

namespace Jiny\Auth\Http\Controllers\Admin\AccountLockout;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\AccountLockoutService;

/**
 * 관리자 - 계정 잠금 목록 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/lockouts') → IndexController::__invoke()
 */
class IndexController extends Controller
{
    protected $lockoutService;
    protected $config;

    public function __construct()
    {
        $this->config = [
            'view' => 'jiny-auth::admin.lockout.index',
            'title' => '계정 잠금 관리',
            'subtitle' => '잠긴 계정 목록',
            'per_page' => 20,
            'sort_column' => 'created_at',
            'sort_order' => 'desc',
            'filter_search' => true,
            'filter_status' => true,
            'filter_requires_admin' => true,
            'filter_level' => true,
        ];
    }

    /**
     * 잠금 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = \DB::table('account_lockouts')
            ->orderBy('created_at', 'desc');

        // 필터링
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('requires_admin')) {
            $query->where('requires_admin_unlock', true);
        }

        if ($request->filled('level')) {
            $query->where('lockout_level', $request->level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $lockouts = $query->paginate($this->config['per_page']);
        $statistics = [
            'total' => \DB::table('account_lockouts')->count(),
            'active' => \DB::table('account_lockouts')->where('status', 'active')->count(),
            'pending' => \DB::table('account_lockouts')->where('status', 'pending')->count(),
        ];

        return view($this->config['view'], compact('lockouts', 'statistics'));
    }
}