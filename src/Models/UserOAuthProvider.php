<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserOAuthProvider extends Model
{
    use HasFactory;

    protected $table = 'user_oauth_providers';

    protected $fillable = [
        'enable',
        'name',
        'provider',
        'client_id',
        'client_secret',
        'redirect_url',
        'callback_url',
        'logout_url',
        'icon',
        'color',
        'users'
    ];

    protected $casts = [
        'users' => 'integer',
    ];

    /**
     * 소셜 계정 관계
     */
    public function oauthAccounts()
    {
        return $this->hasMany(UserOAuth::class, 'provider', 'provider');
    }

    /**
     * 활성화된 프로바이더 조회
     */
    public static function getEnabled()
    {
        return static::where('enable', 'yes')->get();
    }

    /**
     * 프로바이더 찾기
     */
    public static function findByProvider($provider)
    {
        return static::where('provider', $provider)->first();
    }

    /**
     * 활성화 상태 확인
     */
    public function isEnabled()
    {
        return $this->enable === 'yes';
    }

    /**
     * 사용자 수 증가
     */
    public function incrementUserCount()
    {
        $this->increment('users');
        return $this;
    }

    /**
     * 사용자 수 감소
     */
    public function decrementUserCount()
    {
        if ($this->users > 0) {
            $this->decrement('users');
        }
        return $this;
    }

    /**
     * 설정 배열 반환
     */
    public function getConfig()
    {
        return [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect' => $this->callback_url ?: url('/auth/callback/' . $this->provider),
        ];
    }

    /**
     * 아이콘 URL 가져오기
     */
    public function getIconUrl()
    {
        if ($this->icon && str_starts_with($this->icon, 'http')) {
            return $this->icon;
        }

        // 기본 아이콘
        $defaultIcons = [
            'google' => 'https://www.google.com/favicon.ico',
            'facebook' => 'https://www.facebook.com/favicon.ico',
            'github' => 'https://github.com/favicon.ico',
            'kakao' => 'https://t1.kakaocdn.net/kakaocorp/kakaocorp/admin/5f9c58c2017800001.png',
            'naver' => 'https://www.naver.com/favicon.ico',
        ];

        return $defaultIcons[$this->provider] ?? null;
    }
}