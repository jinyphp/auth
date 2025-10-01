<?php

namespace Jiny\Auth\Http\Controllers\Admin\AccountDeletion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\AccountDeletionService;

/**
 * 관리자 - 탈퇴 신청 목록 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/account-deletions') → IndexController::__invoke()
 */
class IndexController extends Controller
{
    protected $deletionService;
    protected $config;

    public function __construct(AccountDeletionService $deletionService)
    {
        $this->deletionService = $deletionService;
        $this->middleware(['auth', 'admin']);

        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/AccountDeletion.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.deletion.index',
            'title' => $indexConfig['title'] ?? '계정 탈퇴 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '탈퇴 신청 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 20,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'requested_at',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'desc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
            'filter_status' => $indexConfig['filter']['status'] ?? true,
        ];
    }

    /**
     * 탈퇴 신청 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = \DB::table('account_deletions')
            ->orderBy($this->config['sort_column'], $this->config['sort_order']);

        // 필터링
        if ($this->config['filter_status'] && $request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $deletions = $query->paginate($this->config['per_page']);
        $statistics = $this->deletionService->getDeletionStatistics();

        return view($this->config['view'], compact('deletions', 'statistics'));
    }
}