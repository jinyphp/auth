<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Phones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 전화번호 삭제
 */
class DeleteController extends Controller
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

            // 전화번호 삭제
            DB::table($phone['table'])
                ->where('id', $phoneId)
                ->delete();

            \Log::info('Phone deleted', [
                'user_id' => $user->id,
                'phone_id' => $phoneId,
            ]);

            return redirect()->back()
                ->with('success', '전화번호가 삭제되었습니다.');
        } catch (\Exception $e) {
            \Log::error('Phone delete failed', [
                'user_id' => $user->id,
                'phone_id' => $phoneId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', '전화번호 삭제 중 오류가 발생했습니다.');
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
                // 무시
            }
        }

        return null;
    }
}
