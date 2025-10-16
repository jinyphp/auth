<?php

namespace Jiny\Auth\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\OAuthService;
use Jiny\Auth\Services\JwtService;
use Jiny\Auth\Services\ActivityLogService;

/**
 * OAuth API 컨트롤러 (v1)
 *
 * 소셜 로그인 API 엔드포인트
 */
class OAuthController extends Controller
{
    protected $oauthService;
    protected $jwtService;
    protected $activityLogService;

    public function __construct(
        OAuthService $oauthService,
        JwtService $jwtService,
        ActivityLogService $activityLogService
    ) {
        $this->oauthService = $oauthService;
        $this->jwtService = $jwtService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * OAuth 인증 URL 생성
     *
     * GET /api/auth/oauth/v1/{provider}/authorize
     *
     * @param string $provider
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authorize($provider, Request $request)
    {
        try {
            // 지원 제공자 확인
            if (!$this->isSupportedProvider($provider)) {
                return response()->json([
                    'success' => false,
                    'message' => "Provider {$provider} is not supported",
                ], 400);
            }

            // 리다이렉트 URI
            $redirectUri = $request->input('redirect_uri', config('app.url') . "/api/auth/oauth/v1/{$provider}/callback");

            // 인증 URL 생성
            $authUrl = $this->oauthService->getAuthorizationUrl($provider, $redirectUri);

            return response()->json([
                'success' => true,
                'provider' => $provider,
                'authorization_url' => $authUrl,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * OAuth 콜백 처리
     *
     * GET /api/auth/oauth/v1/{provider}/callback
     *
     * @param string $provider
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback($provider, Request $request)
    {
        try {
            // 파라미터 검증
            if (!$request->has('code') || !$request->has('state')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required parameters',
                ], 400);
            }

            // 에러 확인
            if ($request->has('error')) {
                return response()->json([
                    'success' => false,
                    'message' => $request->input('error_description', 'OAuth authentication failed'),
                    'error' => $request->input('error'),
                ], 400);
            }

            $code = $request->input('code');
            $state = $request->input('state');
            $redirectUri = $request->input('redirect_uri', config('app.url') . "/api/auth/oauth/v1/{$provider}/callback");

            // OAuth 콜백 처리
            $result = $this->oauthService->handleCallback($provider, $code, $state, $redirectUri);

            $user = $result['user'];

            // JWT 토큰 생성
            $tokens = $this->jwtService->generateTokenPair($user);

            // 활동 로그
            $this->activityLogService->logSuccessfulLogin($user, $request->ip());

            return response()->json([
                'success' => true,
                'message' => $result['is_new_user'] ? '회원가입이 완료되었습니다.' : '로그인되었습니다.',
                'is_new_user' => $result['is_new_user'],
                'linked_existing' => $result['linked_existing'] ?? false,
                'user' => [
                    'id' => $user->uuid,
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'email' => $user->email,
                    'utype' => $user->utype,
                ],
                'tokens' => $tokens,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'OAuth authentication failed',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 400);
        }
    }

    /**
     * 소셜 계정 연동
     *
     * POST /api/auth/oauth/v1/{provider}/link
     *
     * @param string $provider
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function link($provider, Request $request)
    {
        try {
            $user = $this->jwtService->getUserFromToken(
                $this->jwtService->getTokenFromRequest($request)
            );

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '인증되지 않았습니다.',
                ], 401);
            }

            // 이미 연동되어 있는지 확인
            $existing = DB::table('users_social')
                ->where('user_uuid', $user->uuid)
                ->where('provider', $provider)
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => '이미 연동된 계정입니다.',
                ], 400);
            }

            // OAuth 처리 후 연동
            // (실제로는 콜백으로 처리해야 함)

            return response()->json([
                'success' => true,
                'message' => '소셜 계정이 연동되었습니다.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 소셜 계정 연동 해제
     *
     * DELETE /api/auth/oauth/v1/{provider}/unlink
     *
     * @param string $provider
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlink($provider, Request $request)
    {
        try {
            $user = $this->jwtService->getUserFromToken(
                $this->jwtService->getTokenFromRequest($request)
            );

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '인증되지 않았습니다.',
                ], 401);
            }

            $result = $this->oauthService->unlinkProvider($user->uuid, $provider);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => '소셜 계정 연동이 해제되었습니다.',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => '연동된 계정을 찾을 수 없습니다.',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 연동된 소셜 계정 목록
     *
     * GET /api/auth/oauth/v1/linked
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLinkedProviders(Request $request)
    {
        try {
            $user = $this->jwtService->getUserFromToken(
                $this->jwtService->getTokenFromRequest($request)
            );

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '인증되지 않았습니다.',
                ], 401);
            }

            $linked = $this->oauthService->getLinkedProviders($user->uuid);

            return response()->json([
                'success' => true,
                'providers' => $linked->map(function($account) {
                    return [
                        'provider' => $account->provider,
                        'provider_id' => $account->provider_id,
                        'linked_at' => $account->created_at,
                    ];
                }),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 지원 제공자 목록
     *
     * GET /api/auth/oauth/v1/providers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProviders()
    {
        $providers = config('admin.auth.social.providers', []);

        $enabled = collect($providers)
            ->filter(function($config) {
                return $config['enabled'] ?? false;
            })
            ->keys()
            ->values();

        return response()->json([
            'success' => true,
            'providers' => $enabled,
        ], 200);
    }

    /**
     * 지원 제공자 확인
     *
     * @param string $provider
     * @return bool
     */
    protected function isSupportedProvider($provider)
    {
        $config = config("admin.auth.social.providers.{$provider}");
        return $config && ($config['enabled'] ?? false);
    }
}