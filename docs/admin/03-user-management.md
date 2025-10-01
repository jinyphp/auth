# 사용자 관리 (User Management)

## 개요
사용자 계정의 생성, 수정, 삭제 및 프로필 관리를 담당하는 시스템입니다.

## 관련 테이블

### 1. accounts
- **위치**: `2024_03_05_082842_create_accounts_table.php`
- **목적**: 사용자 추가 계정 정보
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `account_type`: 계정 유형
  - `status`: 계정 상태
  - `verified`: 인증 여부

### 2. user_profile
- **위치**: `2021_11_11_082842_create_user_profile_table.php`
- **목적**: 사용자 프로필 정보
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `bio`: 자기소개
  - `avatar`: 프로필 사진
  - `birth_date`: 생년월일
  - `gender`: 성별

### 3. user_avata
- **위치**: `2024_03_05_082842_create_user_avata_table.php`
- **목적**: 프로필 이미지 관리
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `avatar_url`: 이미지 URL
  - `avatar_type`: 이미지 유형
  - `is_default`: 기본 이미지 여부

### 4. users_address
- **위치**: `2024_03_05_082842_create_users_address_table.php`
- **목적**: 사용자 주소 정보
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `type`: 주소 유형 (집/회사)
  - `address`: 주소
  - `city`: 도시
  - `country`: 국가
  - `postal_code`: 우편번호

### 5. users_phone
- **위치**: `2024_03_05_082842_create_users_phone_table.php`
- **목적**: 전화번호 관리
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `phone_type`: 전화 유형 (휴대폰/집/회사)
  - `phone_number`: 전화번호
  - `is_primary`: 기본 번호 여부
  - `verified`: 인증 여부

### 6. user_type
- **위치**: `2024_12_26_074747_create_user_type_table.php`
- **목적**: 사용자 유형 분류
- **주요 필드**:
  - `type_code`: 유형 코드
  - `type_name`: 유형명
  - `description`: 설명
  - `permissions`: 기본 권한

### 7. user_grade
- **위치**: `2022_03_12_053238_create_user_grade_table.php`
- **목적**: 사용자 등급 관리
- **주요 필드**:
  - `grade_code`: 등급 코드
  - `grade_name`: 등급명
  - `min_points`: 최소 포인트
  - `benefits`: 혜택 정보

## 기능 설명

### 계정 생성 프로세스
1. 기본 사용자 정보 입력 (`users`)
2. 추가 계정 정보 설정 (`accounts`)
3. 프로필 정보 입력 (`user_profile`)
4. 연락처 정보 추가 (`users_phone`, `users_address`)
5. 사용자 유형 및 등급 할당

### 프로필 관리
- 다중 프로필 이미지 지원
- 주소록 관리 (집/회사 구분)
- 연락처 우선순위 설정
- 프로필 공개 범위 설정

### 사용자 분류 체계

#### 사용자 유형 (user_type)
- **일반 사용자**: 기본 서비스 이용
- **프리미엄 사용자**: 확장 기능 이용
- **기업 사용자**: B2B 서비스
- **파트너**: 제휴사 계정

#### 사용자 등급 (user_grade)
- **Bronze**: 0-999 포인트
- **Silver**: 1000-4999 포인트
- **Gold**: 5000-9999 포인트
- **Platinum**: 10000+ 포인트

### 계정 상태 관리
- **active**: 활성 상태
- **inactive**: 비활성 (장기 미접속)
- **suspended**: 정지 (규정 위반)
- **deleted**: 삭제 (soft delete)

## 데이터 검증

### 이메일 검증
- 형식 검증
- 중복 확인
- 도메인 차단 리스트 확인

### 전화번호 검증
- 형식 검증
- SMS 인증
- 중복 사용 방지

### 주소 검증
- 우편번호 유효성
- 지역 코드 확인
- 배송 가능 지역 확인