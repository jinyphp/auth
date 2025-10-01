<?php

namespace Jiny\Auth\Http\Controllers\Admin\AccountDeletion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\AccountDeletionService;
use Jiny\Auth\Services\ShardingService;

/**
 * 관리자 - 탈퇴 신청 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/account-deletions/{id}') → ShowController::__invoke()
 */
class ShowController extends Controller
{
    protected $deletionService;
    protected $shardingService;
    protected $config;

    public function __construct(
        AccountDeletionService $deletionService,
        ShardingService $shardingService
    ) {
        $this->deletionService = $deletionService;
        $this->shardingService = $shardingService;
        $this->middleware(['auth', 'admin']);

        $this->loadConfig();
    }

    /**
     * Load configuration values into $this->config array
     */
    protected function loadConfig()
    {
        $this->config['detail_view'] = config('admin.auth.account_deletion.admin.detail_view', 'jiny-auth::admin.deletion.show');
    }

    /**
     * 탈퇴 신청 상세 표시
     */
    public function __invoke(Request $request, $id)
    {
        $deletion = \DB::table('account_deletions')->where('id', $id)->first();

        if (!$deletion) {
            return redirect()->route('admin.deletions.index')
                ->with('error', '탈퇴 신청을 찾을 수 없습니다.');
        }

        // 사용자 정보 조회 (삭제 전이면)
        $user = null;
        if (!$deletion->data_deleted) {
            if ($this->shardingService->isEnabled()) {
                $user = \Jiny\Auth\Models\ShardedUser::findByUuid($deletion->user_uuid);
            } else {
                $user = \App\Models\User::where('uuid', $deletion->user_uuid)->first();
            }
        }

        return view($this->config['detail_view'], compact('deletion', 'user'));
    }
}