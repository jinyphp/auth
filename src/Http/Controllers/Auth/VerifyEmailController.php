<?php

namespace Jiny\Auth\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // 이메일 검증 URL에서 제공된 인자를 가져옵니다.
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        $user_id = $request->id;

        if ($request->hasValidSignature()) {
            // 서명이 유효한 경우 실행할 코드
            DB::table('users')->where('id',$user_id)->update([
                'email_verified_at' => date("Y-m-d H:i:s")
            ]);
            return redirect("/login");
            //return '서명이 유효합니다.';
        } else {
            // 서명이 유효하지 않은 경우 실행할 코드
            abort(403, '서명이 유효하지 않습니다.');
        }

        /*
        dump($request->hasValidSignature());
        dump($request->id);
        dd($request);

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
        */

    }
}
