<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Phones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 기본 전화번호 설정
 */
class SetPrimaryController extends Controller
{
    public function __invoke(Request $request, int $phoneId)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            // 전화번호 찾기
            $phone = $this->findPhone($phoneId, $user->uuid);

            if (!$phone) {
                return redirect()->back()
                    ->with('error', '전화번호를 찾을 수 없습니다.');
            }

            // 기존 기본 전화번호 해제
            $this->clearPrimaryPhone($user->uuid);

            // 새 기본 전화번호 설정
            DB::table($phone['table'])
                ->where('id', $phoneId)
                ->update(['is_primary' => 1, 'updated_at' => now()]);

            \Log::info('Primary phone set', [
                'user_id' => $user->id,
                'phone_id' => $phoneId,
            ]);

            return redirect()->back()
                ->with('success', '기본 전화번호가 설정되었습니다.');
        } catch (\Exception $e) {
            \Log::error('Set primary phone failed', [
                'user_id' => $user->id,
                'phone_id' => $phoneId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '기본 전화번호 설정 중 오류가 발생했습니다.');
        }
    }

    protected function findPhone(int $phoneId, string $userUuid)
    {
        for ($i = 1; $i <= 16; $i++) {
            $tableName = 'phones_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                if (\Schema::hasTable($tableName)) {
                    $phone = DB::table($tableName)
                        ->where('id', $phoneId)
                        ->where('user_uuid', $userUuid)
                        ->first();

                    if ($phone) {
                        return ['phone' => $phone, 'table' => $tableName];
                    }
                }
            } catch (\Exception $e) {
                // 테이블 접근 실패 시 무시
            }
        }

        return null;
    }

    protected function clearPrimaryPhone(string $userUuid)
    {
        for ($i = 1; $i <= 16; $i++) {
            $tableName = 'phones_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                if (\Schema::hasTable($tableName)) {
                    DB::table($tableName)
                        ->where('user_uuid', $userUuid)
                        ->update(['is_primary' => 0]);
                }
            } catch (\Exception $e) {
                // 무시
            }
        }
    }
}
