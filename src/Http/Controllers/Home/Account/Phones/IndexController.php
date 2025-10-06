<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Phones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 전화번호 관리 페이지
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 사용자의 전화번호 목록 가져오기
        $phones = $this->getUserPhones($user->uuid);

        // 기본 전화번호
        $primaryPhone = $phones->firstWhere('is_primary', 1);

        return view('jiny-auth::home.account.phones.index', [
            'user' => $user,
            'phones' => $phones,
            'primaryPhone' => $primaryPhone,
        ]);
    }

    /**
     * 사용자 전화번호 목록 가져오기
     *
     * @param string $userUuid
     * @return \Illuminate\Support\Collection
     */
    protected function getUserPhones(string $userUuid)
    {
        $phones = collect();

        // phones_001 ~ phones_016 테이블 검색
        for ($i = 1; $i <= 16; $i++) {
            $tableName = 'phones_' . str_pad($i, 3, '0', STR_PAD_LEFT);

            try {
                if (\Schema::hasTable($tableName)) {
                    $records = DB::table($tableName)
                        ->where('user_uuid', $userUuid)
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    $phones = $phones->merge($records);
                }
            } catch (\Exception $e) {
                \Log::debug("Table {$tableName} not accessible");
            }
        }

        return $phones;
    }
}
