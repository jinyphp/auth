<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserLogs;

/**
 * 관리자 - 사용자 로그 목록 컨트롤러
 */
class IndexController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserLogs.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.user-logs.index',
            'title' => $indexConfig['title'] ?? '사용자 로그 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '사용자 활동 로그 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 20,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'created_at',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'desc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
            'filter_action' => $indexConfig['filter']['action'] ?? true,
        ];
    }

    /**
     * 사용자 로그 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserLogs::query()->with('user');

        // 검색 필터
        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 액션 필터
        if ($this->config['filter_action'] && $request->filled('action')) {
            $query->where('action', $request->get('action'));
        }

        // 정렬
        $sortBy = $request->get('sort_by', $this->config['sort_column']);
        $sortOrder = $request->get('sort_order', $this->config['sort_order']);
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $logs = $query->paginate($this->config['per_page'])->withQueryString();

        return view($this->config['view'], compact('logs'));
    }
}