<?php

namespace Jiny\Auth\Tests\Feature\Api;

use Jiny\Auth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * OAuth 프로바이더 목록을 조회할 수 있다
     */
    public function can_get_oauth_providers_list()
    {
        $response = $this->getJson(route('api.oauth.v1.providers'));

        $response->assertStatus(200);
    }

    /**
     * @test
     * OAuth 인증 시작 엔드포인트에 접근할 수 있다
     */
    public function can_access_oauth_authorize_endpoint()
    {
        $response = $this->getJson(route('api.oauth.v1.authorize', 'google'));

        $this->assertContains($response->status(), [200, 302]);
    }

    /**
     * @test
     * OAuth 콜백 엔드포인트에 접근할 수 있다
     */
    public function can_access_oauth_callback_endpoint()
    {
        $response = $this->getJson(route('api.oauth.v1.callback', 'google'));

        $this->assertContains($response->status(), [200, 302, 400, 422]);
    }

    /**
     * @test
     * 소셜 계정 연동은 인증이 필요하다
     */
    public function linking_provider_requires_authentication()
    {
        $response = $this->postJson(route('api.oauth.v1.link', 'google'));

        $response->assertStatus(401);
    }

    /**
     * @test
     * 소셜 계정 연동 해제는 인증이 필요하다
     */
    public function unlinking_provider_requires_authentication()
    {
        $response = $this->deleteJson(route('api.oauth.v1.unlink', 'google'));

        $response->assertStatus(401);
    }

    /**
     * @test
     * 연동된 계정 목록 조회는 인증이 필요하다
     */
    public function getting_linked_providers_requires_authentication()
    {
        $response = $this->getJson(route('api.oauth.v1.linked'));

        $response->assertStatus(401);
    }
}
