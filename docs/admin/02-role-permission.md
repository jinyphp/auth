# 역할 및 권한 관리 (Role & Permission)

## 개요
역할 기반 접근 제어(RBAC) 시스템으로 사용자의 권한을 체계적으로 관리합니다.

## 관련 테이블

### 1. auth_roles
- **위치**: `2025_01_01_000005_create_auth_roles_and_permissions_table.php`
- **목적**: 시스템 역할 정의
- **주요 필드**:
  - `id`: 역할 ID
  - `name`: 역할명 (예: admin, editor, viewer)
  - `display_name`: 표시명
  - `level`: 역할 레벨 (계층 구조)
  - `is_active`: 활성화 상태

### 2. auth_permissions
- **위치**: `2025_01_01_000005_create_auth_roles_and_permissions_table.php`
- **목적**: 개별 권한 정의
- **주요 필드**:
  - `id`: 권한 ID
  - `name`: 권한명 (예: user.create, post.edit)
  - `group`: 권한 그룹 (모듈별 분류)
  - `description`: 권한 설명

### 3. auth_role_permissions
- **위치**: `2025_01_01_000005_create_auth_roles_and_permissions_table.php`
- **목적**: 역할-권한 매핑
- **주요 필드**:
  - `role_id`: 역할 ID
  - `permission_id`: 권한 ID

### 4. auth_user_roles
- **위치**: `2025_01_01_000005_create_auth_roles_and_permissions_table.php`
- **목적**: 사용자-역할 매핑
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `role_id`: 역할 ID
  - `assigned_at`: 할당 시간
  - `expires_at`: 만료 시간 (임시 권한)

### 5. auth_user_permissions
- **위치**: `2025_01_01_000005_create_auth_roles_and_permissions_table.php`
- **목적**: 사용자 개별 권한 (직접 할당)
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `permission_id`: 권한 ID
  - `expires_at`: 만료 시간

### 6. user_admin
- **위치**: `2024_12_22_074747_create_user_admin_table.php`
- **목적**: 관리자 추가 정보
- **주요 필드**:
  - `user_id`: 사용자 ID
  - `admin_level`: 관리자 레벨
  - `department`: 부서
  - `access_ip`: 접속 허용 IP

## 기능 설명

### 역할 계층 구조
```
Super Admin (레벨 100)
    ├── Admin (레벨 80)
    │   ├── Manager (레벨 60)
    │   └── Editor (레벨 40)
    └── User (레벨 20)
```

### 권한 확인 프로세스
1. 사용자의 직접 권한 확인 (`auth_user_permissions`)
2. 사용자의 역할 확인 (`auth_user_roles`)
3. 역할의 권한 확인 (`auth_role_permissions`)
4. 만료 시간 검증
5. 최종 권한 결정

### 권한 그룹 예시
- **user**: 사용자 관리 (user.view, user.create, user.edit, user.delete)
- **content**: 콘텐츠 관리 (content.view, content.create, content.edit, content.publish)
- **system**: 시스템 관리 (system.config, system.backup, system.update)

### 임시 권한
- 특정 기간 동안만 유효한 권한 부여
- 프로젝트 기반 접근 제어
- 자동 권한 만료 처리

## 사용 사례

### 1. 신규 직원 온보딩
```
1. 사용자 계정 생성
2. 부서별 기본 역할 할당
3. 필요시 추가 개별 권한 부여
```

### 2. 임시 권한 부여
```
1. 특정 프로젝트 참여자에게 한시적 권한 부여
2. expires_at 설정으로 자동 만료
3. 프로젝트 종료 시 자동 권한 회수
```

### 3. 권한 상속
```
1. 상위 역할은 하위 역할의 모든 권한 포함
2. 역할 레벨로 계층 관리
3. 효율적인 권한 관리
```