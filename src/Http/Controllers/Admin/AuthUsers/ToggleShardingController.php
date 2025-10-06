<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 샤딩 활성화/비활성화 토글 컨트롤러
 *
 * Route::post('/admin/auth/users/toggle-sharding') → ToggleShardingController::__invoke()
 */
class ToggleShardingController extends Controller
{
    /**
     * AuthUser.json 파일 경로
     */
    protected function getConfigPath(): string
    {
        return __DIR__ . '/AuthUser.json';
    }

    /**
     * 샤딩 상태 토글
     */
    public function __invoke(Request $request)
    {
        $configPath = $this->getConfigPath();

        if (!file_exists($configPath)) {
            return redirect()->back()->with('error', '설정 파일을 찾을 수 없습니다.');
        }

        // JSON 파일 읽기
        $config = json_decode(file_get_contents($configPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->with('error', 'JSON 파일을 파싱할 수 없습니다.');
        }

        // 현재 상태 토글
        $currentStatus = $config['table']['sharding'] ?? false;
        $newStatus = !$currentStatus;
        $config['table']['sharding'] = $newStatus;

        // JSON 파일 저장 (pretty print)
        $jsonContent = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($configPath, $jsonContent) === false) {
            return redirect()->back()->with('error', '설정 파일을 저장할 수 없습니다.');
        }

        $message = $newStatus
            ? '샤딩이 활성화되었습니다.'
            : '샤딩이 비활성화되었습니다.';

        return redirect()->route('admin.auth.users.index')->with('success', $message);
    }
}
