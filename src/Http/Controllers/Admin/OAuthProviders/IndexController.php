<?php

namespace Jiny\Auth\Http\Controllers\Admin\OAuthProviders;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserOAuthProvider;

/**
 * 관리자 - OAuth 프로바이더 목록 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/oauth/providers') → IndexController::__invoke()
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
        $configPath = __DIR__ . '/OAuthProviders.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $indexConfig = $jsonConfig['index'] ?? [];

        $this->config = [
            'view' => $indexConfig['view'] ?? 'jiny-auth::admin.oauth-providers.index',
            'title' => $indexConfig['title'] ?? 'OAuth 프로바이더 관리',
            'subtitle' => $indexConfig['subtitle'] ?? '소셜 로그인 프로바이더 목록',
            'per_page' => $indexConfig['pagination']['per_page'] ?? 15,
            'sort_column' => $jsonConfig['table']['sort']['column'] ?? 'name',
            'sort_order' => $jsonConfig['table']['sort']['order'] ?? 'asc',
            'relations' => $jsonConfig['table']['relations'] ?? [],
        ];
    }

    /**
     * OAuth 프로바이더 목록 표시
     */
    public function __invoke(Request $request)
    {
        $query = UserOAuthProvider::query();

        // 관계 로드 (withCount)
        if (isset($this->config['relations']['oauth_accounts'])) {
            $relation = $this->config['relations']['oauth_accounts'];
            if ($relation['method'] === 'withCount') {
                $query->withCount($relation['name']);
            }
        }

        // 정렬 및 페이지네이션
        $providers = $query->orderBy($this->config['sort_column'], $this->config['sort_order'])
                           ->paginate($this->config['per_page']);

        return view($this->config['view'], compact('providers'));
    }
}