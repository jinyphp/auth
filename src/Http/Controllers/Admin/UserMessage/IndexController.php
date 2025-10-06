<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserMessage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserMessage;

/**
 * 관리자 - 사용자 메시지 목록 컨트롤러
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
        $configPath = __DIR__ . '/UserMessage.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.user-message.index',
            'title' => $indexConfig['title'] ?? '사용자 메시지 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '사용자 메시지 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 20,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'created_at',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'desc',
            'filter_search' => $indexConfig['filter']['search'] ?? true,
            'filter_status' => $indexConfig['filter']['status'] ?? true,
            'filter_notice' => $indexConfig['filter']['notice'] ?? true,
        ];
    }

    /**
     * 사용자 메시지 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserMessage::query()->with(['user', 'fromUser']);

        // 검색 필터
        if ($this->config['filter_search'] && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('from_email', 'like', "%{$search}%");
            });
        }

        // 상태 필터
        if ($this->config['filter_status'] && $request->filled('status')) {
            if ($request->status == 'read') {
                $query->read();
            } elseif ($request->status == 'unread') {
                $query->unread();
            }
        }

        // 공지 필터
        if ($this->config['filter_notice'] && $request->filled('notice')) {
            $query->notice();
        }

        // 정렬
        $sortBy = $request->get('sort_by', $this->config['sort_column']);
        $sortOrder = $request->get('sort_order', $this->config['sort_order']);
        $query->orderBy($sortBy, $sortOrder);

        // 페이지네이션
        $messages = $query->paginate($this->config['per_page'])->withQueryString();

        return view($this->config['view'], compact('messages'));
    }
}