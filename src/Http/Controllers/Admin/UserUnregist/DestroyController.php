<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserUnregist;

/**
 * 관리자: 탈퇴 요청 기록 삭제 처리
 *
 * user_unregist 테이블에서 실제 레코드를 삭제합니다.
 * 이는 상태 변경이 아닌 완전한 기록 삭제입니다.
 */
class DestroyController extends Controller
{
    /**
     * 탈퇴 요청 기록 삭제
     *
     * 처리 단계:
     * 1. 세션으로 관리자 여부를 확인합니다.
     * 2. 전달받은 id로 user_unregist를 확인합니다.
     * 3. user_unregist 기록을 삭제합니다.
     *
     * @param Request $request
     * @param int $id 탈퇴 요청 ID
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, $id)
    {
        // 1. 세션으로 관리자 여부를 확인합니다.
        if (!Auth::check()) {
            return $this->errorResponse($request, '로그인이 필요합니다.', 401);
        }

        // 2. 전달받은 id로 user_unregist를 확인합니다.
        $unregistRequest = UserUnregist::findOrFail($id);

        try {
            // 3. user_unregist 기록을 삭제합니다.
            $unregistRequest->delete();

            return $this->successResponse($request, '탈퇴 요청 기록이 삭제되었습니다.');
        } catch (\Throwable $e) {
            return $this->errorResponse($request, '기록 삭제 중 오류가 발생했습니다: ' . $e->getMessage(), 500);
        }
    }

    /**
     * JSON / Redirect 성공 응답
     *
     * @param Request $request
     * @param string $message
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function successResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('admin.user-unregist.index')
            ->with('success', $message);
    }

    /**
     * JSON / Redirect 오류 응답
     *
     * @param Request $request
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function errorResponse(Request $request, string $message, int $status = 400)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()->withErrors(['error' => $message]);
    }
}

