<?php

namespace Jiny\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ValidationService
{
    /**
     * 예약된 이메일/도메인 확인
     */
    public function checkReservedEmail($email)
    {
        // 예약된 이메일 확인
        $reservedEmails = DB::table('user_reserved')
            ->where('type', 'email')
            ->where('is_active', true)
            ->pluck('word')
            ->toArray();

        if (in_array($email, $reservedEmails)) {
            return [
                'valid' => false,
                'message' => '사용할 수 없는 이메일 주소입니다.'
            ];
        }

        // 예약된 도메인 확인
        $domain = substr(strrchr($email, "@"), 1);
        $reservedDomains = DB::table('user_reserved')
            ->where('type', 'domain')
            ->where('is_active', true)
            ->pluck('word')
            ->toArray();

        if (in_array($domain, $reservedDomains)) {
            return [
                'valid' => false,
                'message' => '사용할 수 없는 이메일 도메인입니다.'
            ];
        }

        // 임시 이메일 도메인 차단
        $tempEmailDomains = [
            'tempmail.com',
            '10minutemail.com',
            'guerrillamail.com',
            'mailinator.com',
            'throwaway.email'
        ];

        if (in_array($domain, $tempEmailDomains)) {
            return [
                'valid' => false,
                'message' => '임시 이메일은 사용할 수 없습니다.'
            ];
        }

        return ['valid' => true];
    }

    /**
     * 블랙리스트 확인
     */
    public function checkBlacklist($email, $ip)
    {
        // 이메일 블랙리스트 확인
        $emailBlocked = DB::table('user_blacklist')
            ->where('type', 'email')
            ->where('value', $email)
            ->where(function($query) {
                $query->whereNull('blocked_until')
                    ->orWhere('blocked_until', '>', now());
            })
            ->exists();

        if ($emailBlocked) {
            return [
                'valid' => false,
                'message' => '차단된 이메일입니다.'
            ];
        }

        // IP 블랙리스트 확인
        $ipBlocked = DB::table('user_blacklist')
            ->where('type', 'ip')
            ->where('value', $ip)
            ->where(function($query) {
                $query->whereNull('blocked_until')
                    ->orWhere('blocked_until', '>', now());
            })
            ->exists();

        if ($ipBlocked) {
            return [
                'valid' => false,
                'message' => '차단된 IP 주소입니다.'
            ];
        }

        // IP 범위 블랙리스트 확인
        $ipRanges = DB::table('user_blacklist')
            ->where('type', 'ip_range')
            ->where(function($query) {
                $query->whereNull('blocked_until')
                    ->orWhere('blocked_until', '>', now());
            })
            ->get();

        foreach ($ipRanges as $range) {
            if ($this->ipInRange($ip, $range->value)) {
                return [
                    'valid' => false,
                    'message' => '차단된 IP 범위입니다.'
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * 비밀번호 규칙 검증
     */
    public function validatePasswordRules($password)
    {
        $rules = config('admin.auth.password_rules', [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => true,
        ]);

        // 최소 길이
        if (strlen($password) < $rules['min_length']) {
            return [
                'valid' => false,
                'message' => "비밀번호는 최소 {$rules['min_length']}자 이상이어야 합니다."
            ];
        }

        // 대문자 포함
        if ($rules['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            return [
                'valid' => false,
                'message' => '비밀번호에 대문자를 포함해야 합니다.'
            ];
        }

        // 소문자 포함
        if ($rules['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            return [
                'valid' => false,
                'message' => '비밀번호에 소문자를 포함해야 합니다.'
            ];
        }

        // 숫자 포함
        if ($rules['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            return [
                'valid' => false,
                'message' => '비밀번호에 숫자를 포함해야 합니다.'
            ];
        }

        // 특수문자 포함
        if ($rules['require_symbols'] && !preg_match('/[@$!%*#?&]/', $password)) {
            return [
                'valid' => false,
                'message' => '비밀번호에 특수문자(@$!%*#?&)를 포함해야 합니다.'
            ];
        }

        // 일반적인 비밀번호 체크
        $commonPasswords = [
            'password', '12345678', 'qwerty', '123456789', 'letmein',
            'football', 'iloveyou', 'admin', 'welcome', 'monkey'
        ];

        if (in_array(strtolower($password), $commonPasswords)) {
            return [
                'valid' => false,
                'message' => '너무 일반적인 비밀번호는 사용할 수 없습니다.'
            ];
        }

        // 연속된 문자 체크
        if ($this->hasConsecutiveChars($password)) {
            return [
                'valid' => false,
                'message' => '연속된 문자나 숫자는 사용할 수 없습니다.'
            ];
        }

        return ['valid' => true];
    }

    /**
     * 약관 동의 검증
     */
    public function validateTermsAgreement($termsIds)
    {
        if (empty($termsIds)) {
            return [
                'valid' => false,
                'message' => '약관에 동의해주세요.'
            ];
        }

        // 필수 약관 확인
        $mandatoryTerms = DB::table('user_terms')
            ->where('is_mandatory', true)
            ->where('effective_date', '<=', now())
            ->where(function($query) {
                $query->whereNull('expired_date')
                    ->orWhere('expired_date', '>', now());
            })
            ->pluck('id')
            ->toArray();

        $agreedTerms = is_array($termsIds) ? $termsIds : [$termsIds];

        foreach ($mandatoryTerms as $mandatoryId) {
            if (!in_array($mandatoryId, $agreedTerms)) {
                return [
                    'valid' => false,
                    'message' => '필수 약관에 모두 동의해야 합니다.'
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * reCAPTCHA 검증
     */
    public function validateCaptcha($response)
    {
        if (empty($response)) {
            return [
                'valid' => false,
                'message' => '보안 검증이 필요합니다.'
            ];
        }

        $secret = config('admin.auth.recaptcha.secret_key');

        $result = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $response,
            'remoteip' => request()->ip(),
        ]);

        $data = $result->json();

        if (!$data['success']) {
            return [
                'valid' => false,
                'message' => '보안 검증에 실패했습니다.'
            ];
        }

        // reCAPTCHA v3인 경우 점수 확인
        if (isset($data['score'])) {
            $minScore = config('admin.auth.recaptcha.min_score', 0.5);
            if ($data['score'] < $minScore) {
                return [
                    'valid' => false,
                    'message' => '보안 검증 점수가 낮습니다.'
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * 사용자명 예약어 확인
     */
    public function checkReservedUsername($username)
    {
        $reserved = DB::table('user_reserved')
            ->where('type', 'username')
            ->where('word', $username)
            ->where('is_active', true)
            ->exists();

        if ($reserved) {
            return [
                'valid' => false,
                'message' => '사용할 수 없는 사용자명입니다.'
            ];
        }

        // 시스템 예약어
        $systemReserved = [
            'admin', 'root', 'system', 'support', 'help',
            'api', 'www', 'mail', 'ftp', 'test'
        ];

        if (in_array(strtolower($username), $systemReserved)) {
            return [
                'valid' => false,
                'message' => '시스템 예약어는 사용할 수 없습니다.'
            ];
        }

        return ['valid' => true];
    }

    /**
     * 연속된 문자 체크
     */
    protected function hasConsecutiveChars($password)
    {
        $sequences = [
            'abcdefghijklmnopqrstuvwxyz',
            '01234567890',
            'qwertyuiop',
            'asdfghjkl',
            'zxcvbnm'
        ];

        $lowerPassword = strtolower($password);

        foreach ($sequences as $sequence) {
            for ($i = 0; $i < strlen($sequence) - 2; $i++) {
                $sub = substr($sequence, $i, 3);
                if (strpos($lowerPassword, $sub) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * IP 범위 확인
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
        } elseif (strpos($range, '-') !== false) {
            // IP range
            list($start, $end) = explode('-', $range);
            $ip_long = ip2long($ip);
            return $ip_long >= ip2long($start) && $ip_long <= ip2long($end);
        }

        return $ip == $range;
    }

    /**
     * 이메일 도메인 유효성 확인 (MX 레코드)
     */
    public function validateEmailDomain($email)
    {
        $domain = substr(strrchr($email, "@"), 1);

        // 캐시 확인
        $cacheKey = "email_domain_valid_{$domain}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // MX 레코드 확인
        $valid = checkdnsrr($domain, 'MX');

        // 결과 캐시 (24시간)
        Cache::put($cacheKey, $valid, 86400);

        return $valid;
    }

    /**
     * 연령 제한 확인
     */
    public function validateAge($birthDate, $minAge = 14)
    {
        if (!$birthDate) {
            return ['valid' => true]; // 생년월일 선택사항
        }

        $age = \Carbon\Carbon::parse($birthDate)->age;

        if ($age < $minAge) {
            return [
                'valid' => false,
                'message' => "{$minAge}세 이상만 가입할 수 있습니다.",
                'requires_parent_consent' => $age < 14
            ];
        }

        return ['valid' => true];
    }
}