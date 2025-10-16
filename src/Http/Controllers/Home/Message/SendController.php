<?php

namespace Jiny\Auth\Http\Controllers\Home\Message;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * 메시지 전송 처리
 */
class SendController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 유효성 검사
        $validator = Validator::make($request->all(), [
            'to_email' => 'required|email|exists:users,email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ], [
            'to_email.required' => '받는 사람을 입력해주세요.',
            'to_email.email' => '올바른 이메일 주소를 입력해주세요.',
            'to_email.exists' => '존재하지 않는 사용자입니다.',
            'subject.required' => '제목을 입력해주세요.',
            'message.required' => '내용을 입력해주세요.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('home.message.compose')
                ->withErrors($validator)
                ->withInput();
        }

        // 수신자 정보 가져오기 (이메일로 검색)
        $toUser = DB::table('users')
            ->where('email', $request->to_email)
            ->first();

        if (!$toUser) {
            return redirect()
                ->route('home.message.compose')
                ->with('error', '존재하지 않는 사용자입니다.')
                ->withInput();
        }

        // 메시지 저장
        DB::table('user_messages')->insert([
            'user_id' => $toUser->id,
            'user_uuid' => $toUser->uuid ?? null,
            'email' => $toUser->email,
            'name' => $toUser->name,
            'from_user_id' => $user->id,
            'from_user_uuid' => $user->uuid ?? null,
            'from_email' => $user->email,
            'from_name' => $user->name,
            'subject' => $request->subject,
            'message' => $request->message,
            'notice' => '0',
            'status' => 'sent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('home.message.index')
            ->with('success', '메시지가 전송되었습니다.');
    }
}
