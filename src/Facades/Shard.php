<?php

namespace Jiny\Auth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Shard 파사드
 *
 * 샤딩 관련 기능에 쉽게 접근할 수 있도록 제공하는 파사드
 *
 * @method static bool isEnabled() 샤딩이 활성화되어 있는지 확인
 * @method static int getShardNumber(string $uuid) UUID를 기반으로 샤드 번호 계산
 * @method static string getShardTableName(string $uuid) UUID를 기반으로 샤드 테이블명 조회
 * @method static object|null getUserByUuid(string $uuid) UUID로 사용자 조회
 * @method static object|null getUserByEmail(string $email) 이메일로 사용자 조회
 * @method static object|null getUserByUsername(string $username) 사용자명으로 사용자 조회
 * @method static string createUser(array $data) 사용자 생성 후 UUID 반환
 * @method static array createShardingRelationData(string|object $user) 샤딩 관계 데이터 생성
 * @method static \Illuminate\Support\Collection getUserRelatedData(string $uuid, string $tableName) 사용자 관련 데이터 조회
 * @method static bool insertRelatedData(string $tableName, array $data) 샤딩 관계 데이터 삽입
 * @method static bool updateUser(string $uuid, array $data) 사용자 정보 업데이트
 * @method static bool deleteUser(string $uuid) 사용자 삭제 (소프트 딜리트)
 * @method static array getShardStatistics() 샤드별 통계 정보 조회
 * @method static array getAllShardTables() 전체 샤드 테이블 목록 조회
 * @method static array getShardTableList(string $baseTableName = 'users') 샤드 테이블 목록 및 상태 조회
 * @method static bool createShardTable(int $shardId, string $baseTableName = 'users') 특정 샤드 테이블 생성
 * @method static array createAllShardTables(string $baseTableName = 'users') 모든 샤드 테이블 생성
 * @method static bool dropShardTable(int $shardId, string $baseTableName = 'users') 특정 샤드 테이블 삭제
 * @method static array dropAllShardTables(string $baseTableName = 'users') 모든 샤드 테이블 삭제
 * @method static string getTableNameByShardId(int $shardId, string $prefix = 'users_') 샤드 ID로 테이블명 조회
 *
 * @see \Jiny\Auth\Services\ShardingService
 */
class Shard extends Facade
{
    /**
     * 파사드의 등록된 이름을 가져옵니다.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'jiny.auth.sharding';
    }
}