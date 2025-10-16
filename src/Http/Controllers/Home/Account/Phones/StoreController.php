<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Phones;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 전화번호 추가
 */
class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 유효성 검사
        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'country_code' => 'required|string|max:5',
            'set_as_primary' => 'nullable|boolean',
        ]);

        try {
            // 샤드 ID 결정 (user의 shard_id 또는 기본값 1)
            $shardId = $user->shard_id ?? 1;
            $tableName = 'phones_' . str_pad($shardId, 3, '0', STR_PAD_LEFT);

            // 기본 전화번호로 설정하는 경우, 기존 기본 전화번호 해제
            if ($request->boolean('set_as_primary')) {
                $this->clearPrimaryPhone($user->uuid);
            }

            // 전화번호 추가
            $phoneId = DB::table($tableName)->insertGetId([
                'user_uuid' => $user->uuid,
                'phone_number' => $validated['phone_number'],
                'country_code' => $validated['country_code'],
                'is_verified' => 0,
                'is_primary' => $request->boolean('set_as_primary') ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('Phone number added', [
                'user_id' => $user->id,
                'phone_id' => $phoneId,
                'table' => $tableName,
            ]);

            return redirect()->route('home.account.phones')
                ->with('success', '전화번호가 추가되었습니다.');
        } catch (\Exception $e) {
            \Log::error('Phone add failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', '전화번호 추가 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * 기존 기본 전화번호 해제
     */
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
                // 테이블 접근 실패 시 무시
            }
        }
    }
}
