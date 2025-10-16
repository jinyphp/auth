<?php

namespace Jiny\Auth\Http\Controllers\Admin\AccountLockout;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 계정 잠금 해제 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/lockouts/{id}/unlock') → UnlockFormController::__invoke()
 */
class UnlockFormController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load configuration values into $this->config array
     */
    protected function loadConfig()
    {
        $this->config['unlock_view'] = config('admin.auth.lockout.admin.unlock_view', 'jiny-auth::admin.lockout.unlock');
    }

    /**
     * 잠금 해제 폼 표시
     */
    public function __invoke($id)
    {
        $lockout = \DB::table('account_lockouts')->where('id', $id)->first();

        if (!$lockout) {
            return redirect()->route('admin.lockouts.index')
                ->with('error', '잠금 정보를 찾을 수 없습니다.');
        }

        return view($this->config['unlock_view'], compact('lockout'));
    }
}