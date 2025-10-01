<?php

namespace Jiny\Auth\Http\Controllers\Admin\AccountLockout;

use App\Http\Controllers\Controller;
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

    public function __construct(AccountLockoutService $lockoutService)
    {
        $this->lockoutService = $lockoutService;
        $this->middleware(['auth', 'admin']);

        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/AccountLockout.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.lockout.index',
            'title' => $indexConfig['title'] ?? '계정 잠금 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '잠긴 계정 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 20,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'created_at',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'desc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
            'filter_status' => $indexConfig['filter']['status'] ?? true,
            'filter_requires_admin' => $indexConfig['filter']['requires_admin'] ?? true,
            'filter_level' => $indexConfig['filter']['level'] ?? true,
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
        $statistics = $this->lockoutService->getLockoutStatistics();

        return view($this->config['view'], compact('lockouts', 'statistics'));
    }
}