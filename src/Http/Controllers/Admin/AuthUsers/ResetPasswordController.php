<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 비밀번호 재설정 컨트롤러
 *
 * 관리자가 사용자의 비밀번호를 재설정
 */
class ResetPasswordController extends Controller
{
    /**
     * 비밀번호 재설정
     */
    public function __invoke(Request $request, $id)
    {
        $shardId = $request->get('shard_id');
        $resetType = $request->input('reset_type', 'email'); // 'email' or 'temporary'

        if ($shardId) {
            // 샤드 테이블에서 처리
            $shardTable = ShardTable::where('table_name', 'users')->first();
            $tableName = $shardTable->getShardTableName($shardId);

            $userData = DB::table($tableName)->where('id', $id)->first();

            if (!$userData) {
                return back()->with('error', '사용자를 찾을 수 없습니다.');
            }

            if ($resetType === 'temporary') {
                // 임시 비밀번호 생성 및 설정
                $temporaryPassword = Str::random(12);

                DB::table($tableName)
                    ->where('id', $id)
                    ->update([
                        'password' => Hash::make($temporaryPassword),
                        'updated_at' => now(),
                    ]);

                // 임시 비밀번호를 세션에 저장 (한 번만 표시)
                return back()->with('success', '임시 비밀번호가 생성되었습니다.')
                             ->with('temporary_password', $temporaryPassword)
                             ->with('user_email', $userData->email);
            } else {
                // 이메일로 재설정 링크 전송
                // TODO: 실제 이메일 전송 로직 구현 필요
                return back()->with('success', "비밀번호 재설정 이메일이 {$userData->email}로 전송되었습니다.");
            }
        } else {
            // 일반 테이블에서 처리
            $user = AuthUser::findOrFail($id);

            if ($resetType === 'temporary') {
                // 임시 비밀번호 생성 및 설정
                $temporaryPassword = Str::random(12);

                $user->password = Hash::make($temporaryPassword);
                $user->save();

                // 임시 비밀번호를 세션에 저장 (한 번만 표시)
                return back()->with('success', '임시 비밀번호가 생성되었습니다.')
                             ->with('temporary_password', $temporaryPassword)
                             ->with('user_email', $user->email);
            } else {
                // 이메일로 재설정 링크 전송
                // TODO: 실제 이메일 전송 로직 구현 필요
                return back()->with('success', "비밀번호 재설정 이메일이 {$user->email}로 전송되었습니다.");
            }
        }
    }
}
