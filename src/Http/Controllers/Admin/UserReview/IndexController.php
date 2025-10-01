<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserReview;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserReview;

/**
 * 관리자 - 사용자 리뷰 목록 컨트롤러
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
        $configPath = __DIR__ . '/UserReview.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.user-review.index',
            'title' => $indexConfig['title'] ?? '사용자 리뷰 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '사용자 리뷰 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 20,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'created_at',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'desc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
            'filter_item' => $indexConfig['filter']['item'] ?? true,
            'filter_rank' => $indexConfig['filter']['rank'] ?? true,
            'filter_sort' => $indexConfig['filter']['sort'] ?? true,
        ];
    }

    /**
     * 사용자 리뷰 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserReview::query()->with('user');

        // 검색 필터
        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('review', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('item', 'like', "%{$search}%");
            });
        }

        // 항목 필터
        if ($this->config['filter_item'] && $request->filled('item')) {
            $query->where('item', $request->item);
        }

        // 등급 필터
        if ($this->config['filter_rank'] && $request->filled('rank')) {
            $query->where('rank', $request->rank);
        }

        // 정렬
        if ($this->config['filter_sort'] && $request->filled('sort')) {
            $sort = $request->get('sort');
            switch ($sort) {
                case 'popular':
                    $query->popular();
                    break;
                case 'high_rated':
                    $query->highRated();
                    break;
                default:
                    $query->latest();
            }
        } else {
            $sortBy = $request->get('sort_by', $this->config['sort_column']);
            $sortOrder = $request->get('sort_order', $this->config['sort_order']);
            $query->orderBy($sortBy, $sortOrder);
        }

        // 페이지네이션
        $reviews = $query->paginate($this->config['per_page'])->withQueryString();

        return view($this->config['view'], compact('reviews'));
    }
}