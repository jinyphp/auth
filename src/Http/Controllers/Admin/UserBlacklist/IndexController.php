<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserBlacklist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserBlacklist;

/**
 * 관리자 - 사용자 블랙리스트 목록 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/blacklist') → IndexController::__invoke()
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
        $configPath = __DIR__ . '/UserBlacklist.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.user-blacklist.index',
            'title' => $indexConfig['title'] ?? '사용자 블랙리스트 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '차단된 사용자 키워드 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 20,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'created_at',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'desc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
        ];
    }

    /**
     * 사용자 블랙리스트 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserBlacklist::query();

        // 검색 필터
        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('keyword', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 정렬 및 페이지네이션
        $blacklists = $query->orderBy($this->config['sort_column'], $this->config['sort_order'])
                            ->paginate($this->config['per_page'])
                            ->withQueryString();

        return view($this->config['view'], compact('blacklists'));
    }
}