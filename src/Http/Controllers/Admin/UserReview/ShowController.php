<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserReview;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 사용자 리뷰 상세 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/user/reviews/{id}') → ShowController::__invoke()
 */
class ShowController extends Controller
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

        $showConfig = $jsonConfig['show'] ?? [];

        $this->config = [
            'view' => $showConfig['view'] ?? 'jiny-auth::admin.user-review.show',
            'title' => $showConfig['title'] ?? '리뷰 상세',
            'subtitle' => $showConfig['subtitle'] ?? '리뷰 정보',
        ];
    }

    /**
     * 사용자 리뷰 상세 정보 표시
     */
    public function __invoke($id)
    {
        $review = \DB::table('user_reviews')->where('id', $id)->first();

        if (!$review) {
            return redirect()->route('admin.auth.user.reviews.index')
                ->with('error', '리뷰를 찾을 수 없습니다.');
        }

        // 사용자 정보
        $user = \App\Models\User::find($review->user_id);

        return view($this->config['view'], compact('review', 'user'));
    }
}
