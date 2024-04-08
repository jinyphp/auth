<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class _RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                // 사용자권한 redirect 페이지
                /*
                $user = Auth::user();
                $role_ids = DB::table('role_user')->where('user_id', $user->id)->orderBy('role_id',"asc")->get();
                if($role_ids) {
                    $role = DB::table('roles')->where('id', $role_ids[0]->role_id)->first();
                    if($role->redirect) {
                        // 1.역할 dashboard로 이동
                        return redirect($role->redirect);
                    }
                }
                */

                // 2.auth 설정 dashboard로 이동
                /*
                $dashboard = config("jiny.auth.setting.home");
                if($dashboard) {
                    return redirect($dashboard);
                }
                */


                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
