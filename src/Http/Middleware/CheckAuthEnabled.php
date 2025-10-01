<?php

namespace Jiny\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuthEnabled
{
    /**
     * 인증 시스템 활성화 확인 미들웨어
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // config에서 로그인 활성화 여부 확인
        if (!config('admin.auth.login.enable', true)) {
            // 인증 시스템이 비활성화된 경우

            // API 요청인 경우
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '인증 시스템이 일시적으로 중단되었습니다.',
                    'status' => 'maintenance'
                ], 503);
            }

            // 웹 요청인 경우
            return response()->view('auth::maintenance', [], 503);
        }

        // 회원가입 라우트인 경우 회원가입 활성화 확인
        if ($request->routeIs('register*')) {
            if (!config('admin.auth.register.enable', true)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '회원가입이 일시적으로 중단되었습니다.',
                        'status' => 'registration_disabled'
                    ], 503);
                }

                return redirect()->route('login')
                    ->with('warning', '현재 회원가입이 중단되었습니다. 기존 회원은 로그인하실 수 있습니다.');
            }
        }

        // IP 화이트리스트 확인 (관리자 설정이 있는 경우)
        if (config('admin.auth.ip_whitelist.enable', false)) {
            $allowedIps = config('admin.auth.ip_whitelist.ips', []);

            if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
                // CIDR 범위 확인
                $allowed = false;
                foreach ($allowedIps as $ip) {
                    if ($this->ipInRange($request->ip(), $ip)) {
                        $allowed = true;
                        break;
                    }
                }

                if (!$allowed) {
                    abort(403, '접속이 허용되지 않은 IP입니다.');
                }
            }
        }

        // 유지보수 모드 확인
        if (config('admin.auth.maintenance_mode', false)) {
            // 유지보수 제외 IP 확인
            $excludedIps = config('admin.auth.maintenance_exclude_ips', []);

            if (!in_array($request->ip(), $excludedIps)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '시스템 유지보수 중입니다.',
                        'status' => 'maintenance'
                    ], 503);
                }

                return response()->view('auth::maintenance', [
                    'message' => config('admin.auth.maintenance_message', '시스템 유지보수 중입니다.')
                ], 503);
            }
        }

        return $next($request);
    }

    /**
     * IP가 특정 범위에 포함되는지 확인
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    protected function ipInRange($ip, $range)
    {
        if (strpos($range, '/') !== false) {
            // CIDR notation
            list($subnet, $bits) = explode('/', $range);
            $ip_long = ip2long($ip);
            $subnet_long = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet_long &= $mask;
            return ($ip_long & $mask) == $subnet_long;
        }

        return $ip == $range;
    }
}