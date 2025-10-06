<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserLanguage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserLanguage;

/**
 * 관리자 - 언어 목록 컨트롤러
 */
class IndexController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = [
            'view' => 'jiny-auth::admin.user-language.index',
            'title' => '언어 관리',
            'subtitle' => '언어 목록',
            'per_page' => 20,
            'sort_column' => 'name',
            'sort_order' => 'asc',
            'filter_search' => true,
        ];
    }

    /**
     * 언어 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = \DB::table('site_languages');

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
        $languages = $query->paginate($this->config['per_page'])->withQueryString();

        return view($this->config['view'], compact('languages'));
    }
}