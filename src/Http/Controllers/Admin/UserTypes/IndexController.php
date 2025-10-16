<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserTypes;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserType;

/**
 * 관리자 - 사용자 유형 목록 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/types') → IndexController::__invoke()
 */
class IndexController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/UserTypes.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.user-types.index',
            'title' => $indexConfig['title'] ?? '사용자 유형 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '시스템 사용자 유형 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 10,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'created_at',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'desc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
            'filter_status' => $indexConfig['filter']['status'] ?? true,
        ];
    }

    /**
     * 사용자 유형 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserType::query();

        // 검색 필터
        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($this->config['filter_status'] && $request->filled('status') && $request->get('status') !== 'all') {
            $query->where('enable', $request->get('status') === 'active' ? 1 : 0);
        }

        // 정렬
        $sortBy = $request->get('sort_by', $this->config['sort_column']);
        $sortOrder = $request->get('sort_order', $this->config['sort_order']);
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $userTypes = $query->paginate($this->config['per_page'])->withQueryString();

        return view($this->config['view'], compact('userTypes'));
    }
}