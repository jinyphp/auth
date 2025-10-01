<?php

namespace Jiny\Auth\Http\Controllers\Admin\Terms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserTerms;

/**
 * 관리자 - 이용약관 목록 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/terms') → IndexController::__invoke()
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
        $configPath = __DIR__ . '/Terms.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.terms.index',
            'title' => $indexConfig['title'] ?? '이용약관 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '시스템 이용약관 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 10,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'pos',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'asc',
            'sort_secondary_column' => $jsonConfig['table']['sort']['secondary']['column'] ?? 'created_at',
            'sort_secondary_order' => $jsonConfig['table']['sort']['secondary']['order'] ?? 'desc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
            'filter_status' => $indexConfig['filter']['status'] ?? true,
        ];
    }

    /**
     * 이용약관 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserTerms::query();

        // 검색 필터
        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($this->config['filter_status'] && $request->filled('status') && $request->get('status') !== 'all') {
            $query->where('enable', $request->get('status') === 'active' ? 1 : 0);
        }

        // 정렬
        $query->orderBy($this->config['sort_column'], $this->config['sort_order'])
              ->orderBy($this->config['sort_secondary_column'], $this->config['sort_secondary_order']);

        // 페이지네이션
        $terms = $query->paginate($this->config['per_page'])->withQueryString();

        return view($this->config['view'], compact('terms'));
    }
}