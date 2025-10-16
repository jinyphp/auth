<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserCountry;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserCountry;

/**
 * 관리자 - 국가 목록 컨트롤러
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
        $configPath = __DIR__ . '/UserCountry.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.user-country.index',
            'title' => $indexConfig['title'] ?? '국가 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '국가 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 20,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'name',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'asc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
        ];
    }

    /**
     * 국가 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserCountry::query();

        // 검색 필터
        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // 정렬
        $sortBy = $request->get('sort_by', $this->config['sort_column']);
        $sortOrder = $request->get('sort_order', $this->config['sort_order']);
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $countries = $query->paginate($this->config['per_page'])->withQueryString();

        return view($this->config['view'], compact('countries'));
    }
}