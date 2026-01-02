<?php

namespace Jiny\Auth\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Jiny\Auth\Services\UserStatusService;

/**
 * UserStatusService 단위 테스트
 *
 * 회원 상태 확인 서비스의 각 메서드를 테스트합니다.
 */
class UserStatusServiceTest extends TestCase
{
    /**
     * UserStatusService 인스턴스
     *
     * @var UserStatusService
     */
    protected $service;

    /**
     * 테스트 전 초기화
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserStatusService();
    }

    /**
     * 활성 계정 확인 테스트
     */
    public function testIsAccountActive()
    {
        $user = (object) [
            'account_status' => 'active',
        ];

        $this->assertTrue($this->service->isAccountActive($user));
    }

    /**
     * 비활성 계정 확인 테스트
     */
    public function testIsAccountInactive()
    {
        $user = (object) [
            'account_status' => 'inactive',
        ];

        $this->assertFalse($this->service->isAccountActive($user));
    }

    /**
     * 정지 계정 확인 테스트
     */
    public function testIsAccountSuspended()
    {
        $user = (object) [
            'account_status' => 'suspended',
        ];

        $this->assertTrue($this->service->isAccountSuspended($user));
    }

    /**
     * 차단 계정 확인 테스트
     */
    public function testIsAccountBanned()
    {
        $user = (object) [
            'account_status' => 'banned',
        ];

        $this->assertTrue($this->service->isAccountBanned($user));
    }

    /**
     * blocked 상태도 차단으로 인식하는지 테스트
     */
    public function testIsAccountBannedWithBlocked()
    {
        $user = (object) [
            'status' => 'blocked', // 기존 호환성
        ];

        $this->assertTrue($this->service->isAccountBanned($user));
    }

    /**
     * 이메일 인증 확인 테스트
     */
    public function testIsEmailVerified()
    {
        $user = (object) [
            'email_verified_at' => '2024-01-01 00:00:00',
        ];

        $this->assertTrue($this->service->isEmailVerified($user));
    }

    /**
     * 이메일 미인증 확인 테스트
     */
    public function testIsEmailNotVerified()
    {
        $user = (object) [
            'email_verified_at' => null,
        ];

        $this->assertFalse($this->service->isEmailVerified($user));
    }

    /**
     * 계정 삭제 확인 테스트
     */
    public function testIsAccountDeleted()
    {
        $user = (object) [
            'deleted_at' => '2024-01-01 00:00:00',
        ];

        $this->assertTrue($this->service->isAccountDeleted($user));
    }

    /**
     * 계정 미삭제 확인 테스트
     */
    public function testIsAccountNotDeleted()
    {
        $user = (object) [
            'deleted_at' => null,
        ];

        $this->assertFalse($this->service->isAccountDeleted($user));
    }

    /**
     * 승인된 계정 확인 테스트
     */
    public function testIsAccountApproved()
    {
        $user = (object) [
            'approval' => true,
        ];

        $this->assertTrue($this->service->isAccountApproved($user));
    }

    /**
     * 미승인 계정 확인 테스트
     */
    public function testIsAccountNotApproved()
    {
        $user = (object) [
            'approval' => false,
        ];

        $this->assertFalse($this->service->isAccountApproved($user));
    }

    /**
     * approval 필드가 없으면 승인된 것으로 간주하는지 테스트
     */
    public function testIsAccountApprovedWhenFieldMissing()
    {
        $user = (object) [
            'account_status' => 'active',
        ];

        $this->assertTrue($this->service->isAccountApproved($user));
    }

    /**
     * 전체 상태 검증 - 유효한 계정
     */
    public function testValidateUserStatusValid()
    {
        $user = (object) [
            'account_status' => 'active',
            'email_verified_at' => '2024-01-01 00:00:00',
            'deleted_at' => null,
            'approval' => true,
        ];

        $result = $this->service->validateUserStatus($user);

        $this->assertTrue($result['valid']);
    }

    /**
     * 전체 상태 검증 - 차단된 계정
     */
    public function testValidateUserStatusBanned()
    {
        $user = (object) [
            'account_status' => 'banned',
            'email_verified_at' => '2024-01-01 00:00:00',
            'deleted_at' => null,
        ];

        $result = $this->service->validateUserStatus($user);

        $this->assertFalse($result['valid']);
        $this->assertEquals('ACCOUNT_BANNED', $result['reason']);
    }

    /**
     * 전체 상태 검증 - 정지된 계정
     */
    public function testValidateUserStatusSuspended()
    {
        $user = (object) [
            'account_status' => 'suspended',
            'email_verified_at' => '2024-01-01 00:00:00',
            'deleted_at' => null,
        ];

        $result = $this->service->validateUserStatus($user);

        $this->assertFalse($result['valid']);
        $this->assertEquals('ACCOUNT_SUSPENDED', $result['reason']);
    }

    /**
     * 전체 상태 검증 - 삭제된 계정
     */
    public function testValidateUserStatusDeleted()
    {
        $user = (object) [
            'account_status' => 'active',
            'email_verified_at' => '2024-01-01 00:00:00',
            'deleted_at' => '2024-01-01 00:00:00',
        ];

        $result = $this->service->validateUserStatus($user);

        $this->assertFalse($result['valid']);
        $this->assertEquals('ACCOUNT_DELETED', $result['reason']);
    }

    /**
     * 전체 상태 검증 - 이메일 미인증 (설정에 따라)
     */
    public function testValidateUserStatusEmailNotVerified()
    {
        $user = (object) [
            'account_status' => 'active',
            'email_verified_at' => null,
            'deleted_at' => null,
        ];

        $result = $this->service->validateUserStatus($user);

        // require_email_verification이 true이면 실패해야 함
        $this->assertFalse($result['valid']);
        $this->assertEquals('EMAIL_NOT_VERIFIED', $result['reason']);
    }

    /**
     * 사용자가 null인 경우 테스트
     */
    public function testValidateUserStatusNullUser()
    {
        $result = $this->service->validateUserStatus(null);

        $this->assertFalse($result['valid']);
        $this->assertEquals('USER_NOT_FOUND', $result['reason']);
    }

    /**
     * 기존 status 필드 호환성 테스트
     */
    public function testLegacyStatusFieldCompatibility()
    {
        $user = (object) [
            'status' => 'active', // account_status 대신 status 사용
        ];

        $this->assertTrue($this->service->isAccountActive($user));
    }
}
