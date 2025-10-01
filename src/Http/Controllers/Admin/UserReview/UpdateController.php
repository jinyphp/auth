<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserReview;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * 관리자 - 사용자 리뷰 수정 처리 컨트롤러
 *
 * 진입 경로:
 * Route::put('/admin/auth/user/reviews/{id}') → UpdateController::__invoke()
 */
class UpdateController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserReview.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $updateConfig = $jsonConfig['update'] ?? [];

        $this->actions = [
            'validation' => $updateConfig['validation'] ?? [],
            'routes' => [
                'success' => $updateConfig['redirect']['success'] ?? 'admin.auth.user.reviews.show',
                'error' => $updateConfig['redirect']['error'] ?? 'admin.auth.user.reviews.edit',
            ],
            'messages' => [
                'success' => $updateConfig['messages']['success'] ?? '리뷰 정보가 성공적으로 업데이트되었습니다.',
                'error' => $updateConfig['messages']['error'] ?? '리뷰 정보 업데이트에 실패했습니다.',
            ],
        ];
    }

    /**
     * 사용자 리뷰 수정 처리
     */
    public function __invoke(Request $request, $id)
    {
        $review = \DB::table('user_reviews')->where('id', $id)->first();

        if (!$review) {
            return redirect()->route('admin.auth.user.reviews.index')
                ->with('error', '리뷰를 찾을 수 없습니다.');
        }

        $validator = Validator::make($request->all(), $this->actions['validation']);

        if ($validator->fails()) {
            return redirect()
                ->route($this->actions['routes']['error'], $id)
                ->withErrors($validator)
                ->withInput();
        }

        \DB::table('user_reviews')->where('id', $id)->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'updated_at' => now(),
        ]);

        return redirect()
            ->route($this->actions['routes']['success'], $id)
            ->with('success', $this->actions['messages']['success']);
    }
}
