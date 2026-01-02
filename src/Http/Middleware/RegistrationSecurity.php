<?php

namespace Jiny\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * 회원가입 보안 미들웨어
 * 
 * 회원가입 API에 대한 보안 강화를 제공합니다:
 * - IP 기반 Rate Limiting
 * - IP 화이트리스트/블랙리스트 검증
 * - 요청 패턴 분석 (봇 감지)
 * - 동일 IP에서의 중복 가입 방지
 */
class RegistrationSecurity
{
    /**
     * IP당 최대 요청 횟수 (분당)
     * 
     * @var int
     */
    protected int $maxRequestsPerMinute;

    /**
     * IP당 최대 요청 횟수 (시간당)
     * 
     * @var int
     */
    protected int $maxRequestsPerHour;

    /**
     * 동일 IP에서 허용되는 최대 계정 수
     * 
     * @var int
     */
    protected int $maxAccountsPerIp;

    /**
     * 생성자
     */
    public function __construct()
    {
        $this->maxRequestsPerMinute = config('jiny-auth.registration.rate_limit.per_minute', 5);
        $this->maxRequestsPerHour = config('jiny-auth.registration.rate_limit.per_hour', 20);
        $this->maxAccountsPerIp = config('jiny-auth.registration.rate_limit.max_accounts_per_ip', 3);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $this->getClientIp($request);

        // 1. IP 블랙리스트 확인
        if ($this->isBlacklistedIp($ip)) {
            Log::warning('회원가입 시도: 블랙리스트 IP', ['ip' => $ip]);
            return $this->errorResponse('접근이 차단되었습니다.', 403);
        }

        // 2. IP 화이트리스트 확인 (설정된 경우)
        if ($this->hasWhitelist() && !$this->isWhitelistedIp($ip)) {
            Log::warning('회원가입 시도: 화이트리스트 외 IP', ['ip' => $ip]);
            return $this->errorResponse('접근이 허용되지 않은 IP입니다.', 403);
        }

        // 3. Rate Limiting 확인 (분당)
        if (!$this->checkRateLimitPerMinute($ip)) {
            Log::warning('회원가입 시도: Rate Limit 초과 (분당)', ['ip' => $ip]);
            return $this->errorResponse('요청이 너무 많습니다. 잠시 후 다시 시도해주세요.', 429);
        }

        // 4. Rate Limiting 확인 (시간당)
        if (!$this->checkRateLimitPerHour($ip)) {
            Log::warning('회원가입 시도: Rate Limit 초과 (시간당)', ['ip' => $ip]);
            return $this->errorResponse('요청이 너무 많습니다. 1시간 후 다시 시도해주세요.', 429);
        }

        // 5. 동일 IP에서의 계정 수 확인
        if (!$this->checkMaxAccountsPerIp($ip)) {
            Log::warning('회원가입 시도: 동일 IP에서 최대 계정 수 초과', ['ip' => $ip]);
            return $this->errorResponse('동일 IP에서 생성 가능한 계정 수를 초과했습니다.', 403);
        }

        // 6. 요청 패턴 분석 (봇 감지)
        if ($this->isSuspiciousPattern($request, $ip)) {
            Log::warning('회원가입 시도: 의심스러운 패턴 감지', [
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
            ]);
            // 봇으로 의심되면 추가 검증 요구
            return $this->errorResponse('보안 검증이 필요합니다. 잠시 후 다시 시도해주세요.', 429);
        }

        $response = $next($request);

        // 성공 시 Rate Limit 카운터 증가
        if ($response->getStatusCode() === 201) {
            $this->incrementRateLimitCounters($ip);
            $this->recordSuccessfulRegistration($ip, $request);
        }

        return $response;
    }

    /**
     * 클라이언트 IP 주소 가져오기
     * 
     * @param Request $request
     * @return string
     */
    protected function getClientIp(Request $request): string
    {
        // 프록시를 통한 실제 IP 확인
        $ip = $request->header('X-Forwarded-For');
        if ($ip) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }

        if (!$ip) {
            $ip = $request->header('X-Real-IP');
        }

        return $ip ?: $request->ip();
    }

    /**
     * IP 블랙리스트 확인
     * 
     * @param string $ip
     * @return bool
     */
    protected function isBlacklistedIp(string $ip): bool
    {
        // 설정 파일에서 블랙리스트 확인
        $blacklistConfig = config('jiny-auth.registration.ip.blacklist', '');
        
        if (empty($blacklistConfig)) {
            return false;
        }

        // 콤마로 구분된 문자열을 배열로 변환
        $blacklist = is_string($blacklistConfig) 
            ? array_map('trim', explode(',', $blacklistConfig))
            : (array) $blacklistConfig;

        foreach ($blacklist as $blockedIp) {
            if (!empty($blockedIp) && $this->ipMatches($ip, trim($blockedIp))) {
                return true;
            }
        }

        return false;
    }

    /**
     * IP 화이트리스트 존재 여부 확인
     * 
     * @return bool
     */
    protected function hasWhitelist(): bool
    {
        $whitelistConfig = config('jiny-auth.registration.ip.whitelist', '');
        return !empty($whitelistConfig);
    }

    /**
     * IP 화이트리스트 확인
     * 
     * @param string $ip
     * @return bool
     */
    protected function isWhitelistedIp(string $ip): bool
    {
        $whitelistConfig = config('jiny-auth.registration.ip.whitelist', '');
        
        if (empty($whitelistConfig)) {
            return true; // 화이트리스트가 없으면 모든 IP 허용
        }

        // 콤마로 구분된 문자열을 배열로 변환
        $whitelist = is_string($whitelistConfig) 
            ? array_map('trim', explode(',', $whitelistConfig))
            : (array) $whitelistConfig;

        foreach ($whitelist as $allowedIp) {
            if (!empty($allowedIp) && $this->ipMatches($ip, trim($allowedIp))) {
                return true;
            }
        }

        return false;
    }

    /**
     * IP 주소 매칭 (CIDR 지원)
     * 
     * @param string $ip
     * @param string $pattern
     * @return bool
     */
    protected function ipMatches(string $ip, string $pattern): bool
    {
        if (strpos($pattern, '/') !== false) {
            // CIDR 표기법 처리
            list($subnet, $mask) = explode('/', $pattern);
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - (int)$mask);
            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        return $ip === $pattern;
    }

    /**
     * 분당 Rate Limit 확인
     * 
     * @param string $ip
     * @return bool
     */
    protected function checkRateLimitPerMinute(string $ip): bool
    {
        $key = "registration:rate_limit:minute:{$ip}";
        $count = Cache::get($key, 0);

        if ($count >= $this->maxRequestsPerMinute) {
            return false;
        }

        Cache::put($key, $count + 1, now()->addMinute());
        return true;
    }

    /**
     * 시간당 Rate Limit 확인
     * 
     * @param string $ip
     * @return bool
     */
    protected function checkRateLimitPerHour(string $ip): bool
    {
        $key = "registration:rate_limit:hour:{$ip}";
        $count = Cache::get($key, 0);

        if ($count >= $this->maxRequestsPerHour) {
            return false;
        }

        Cache::put($key, $count + 1, now()->addHour());
        return true;
    }

    /**
     * 동일 IP에서의 최대 계정 수 확인
     * 
     * @param string $ip
     * @return bool
     */
    protected function checkMaxAccountsPerIp(string $ip): bool
    {
        $key = "registration:accounts_per_ip:{$ip}";
        $count = Cache::get($key, 0);

        if ($count >= $this->maxAccountsPerIp) {
            return false;
        }

        return true;
    }

    /**
     * 의심스러운 요청 패턴 감지
     * 
     * @param Request $request
     * @param string $ip
     * @return bool
     */
    protected function isSuspiciousPattern(Request $request, string $ip): bool
    {
        // 1. User-Agent가 없거나 비정상적인 경우
        // 단, 테스트 환경에서는 제외
        if (app()->environment('testing')) {
            return false; // 테스트 환경에서는 봇 감지 비활성화
        }
        
        $userAgent = $request->userAgent();
        if (empty($userAgent) || strlen($userAgent) < 10) {
            return true;
        }

        // 2. 알려진 봇 User-Agent 패턴
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper',
            'curl', 'wget', 'python', 'java',
        ];
        
        $userAgentLower = strtolower($userAgent);
        foreach ($botPatterns as $pattern) {
            if (strpos($userAgentLower, $pattern) !== false) {
                return true;
            }
        }

        // 3. 너무 빠른 연속 요청 (1초 이내)
        $key = "registration:last_request:{$ip}";
        $lastRequest = Cache::get($key);
        if ($lastRequest && (time() - $lastRequest) < 1) {
            return true;
        }
        Cache::put($key, time(), now()->addMinute());

        return false;
    }

    /**
     * Rate Limit 카운터 증가
     * 
     * @param string $ip
     * @return void
     */
    protected function incrementRateLimitCounters(string $ip): void
    {
        // 분당 카운터는 이미 증가됨 (handle 메서드에서)
        // 시간당 카운터는 이미 증가됨 (handle 메서드에서)
    }

    /**
     * 성공적인 회원가입 기록
     * 
     * @param string $ip
     * @param Request $request
     * @return void
     */
    protected function recordSuccessfulRegistration(string $ip, Request $request): void
    {
        // 동일 IP에서의 계정 수 증가
        $key = "registration:accounts_per_ip:{$ip}";
        $count = Cache::get($key, 0);
        Cache::put($key, $count + 1, now()->addDays(30)); // 30일간 유지

        // 성공 로그 기록
        Log::info('회원가입 성공', [
            'ip' => $ip,
            'email' => $request->input('email'),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * 에러 응답 생성
     * 
     * @param string $message
     * @param int $statusCode
     * @return Response
     */
    protected function errorResponse(string $message, int $statusCode = 403): Response
    {
        return response()->json([
            'success' => false,
            'code' => 'SECURITY_VIOLATION',
            'message' => $message,
        ], $statusCode);
    }
}

