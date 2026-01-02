<?php

namespace Jiny\Auth\Services;

use Jiny\Jwt\Contracts\UserResolverInterface;
use Jiny\Auth\Facades\Shard;

/**
 * 샤딩 사용자 조회 구현체
 *
 * UserResolverInterface를 구현하여 jiny/jwt 패키지가
 * 샤딩된 사용자 테이블에서 사용자 정보를 조회할 수 있도록 합니다.
 *
 * 이 클래스는 jiny/auth 패키지에서 jiny/jwt 패키지의
 * UserResolverInterface를 구현한 것으로, 의존성 역전 원칙을 적용합니다.
 */
class ShardingUserResolver implements UserResolverInterface
{
    /**
     * UUID로 사용자 조회
     *
     * 샤딩된 사용자 테이블에서 UUID를 기반으로 사용자 정보를 조회합니다.
     *
     * @param  string  $uuid  사용자 UUID
     * @return object|null 사용자 객체 또는 null
     */
    public function getUserByUuid(string $uuid): ?object
    {
        return Shard::getUserByUuid($uuid);
    }

    /**
     * 여러 UUID로 사용자 조회
     *
     * 여러 사용자 UUID를 한 번에 조회합니다.
     *
     * @param  array  $uuids  사용자 UUID 배열
     * @return array 사용자 객체 배열
     */
    public function getUsersByUuids(array $uuids): array
    {
        return Shard::getUsersByUuids($uuids);
    }

    /**
     * 샤딩 활성화 여부 확인
     *
     * 샤딩이 활성화되어 있는지 확인합니다.
     *
     * @return bool 샤딩 활성화 여부
     */
    public function isShardingEnabled(): bool
    {
        return Shard::isEnabled();
    }

    /**
     * 샤드 번호 조회
     *
     * UUID를 기반으로 샤드 번호를 계산합니다.
     *
     * @param  string  $uuid  사용자 UUID
     * @return int 샤드 번호 (1부터 시작)
     */
    public function getShardNumber(string $uuid): int
    {
        return Shard::getShardNumber($uuid);
    }
}
