<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserTypes;

use App\Http\Controllers\Controller;
use Jiny\Auth\Models\UserType;
use Illuminate\Support\Facades\DB;

/**
 * 사용자 유형 기본 설정 토글 컨트롤러
 */
class ToggleDefaultController extends Controller
{
    /**
     * 기본 유형 설정/해제
     *
     * 하나의 유형만 기본으로 설정 가능
     */
    public function __invoke($id)
    {
        $userType = UserType::findOrFail($id);

        DB::transaction(function () use ($userType) {
            // 모든 유형의 is_default를 false로 설정
            UserType::query()->update(['is_default' => false]);

            // 선택된 유형만 true로 설정
            $userType->update(['is_default' => true]);
        });

        return redirect()->route('admin.auth.user.types.index')
            ->with('success', "'{$userType->description}' 유형이 기본으로 설정되었습니다.");
    }
}
