# 설치 및 설정
라라벨 기반의 회원 가입 및 인증을 처리합니다.

## 설치
컴포저를 인증 패키지를 설치합니다.

```
composer require jiny/auth
```

데이터베이스 마이그레이션과 설정파일을 프로젝트에 복사합니다.
```
php artisan migrate
php artisan vendor:publish --provider="Jiny\Auth\JinyAuthServiceProvider"
```

## 관리자 설정
인증 관리자에 접속을 하기 위해서는 관리자 권환이 필요합니다.
먼저 회원을 가입하고, 관리자 권한을 부여합니다.

```
php artisan user:create --name=이름 --email=이메일 --password=패스워드
php artisan user:admin 이메일 --enable
```





## 헬퍼

is_admin()
어드민 회원 여부를 확인할 수 있는 헬퍼 함수를 제공합니다.

## 커멘트

### 회원 추가
artisan 명령을 통하여 콘솔창에서 회원을 추가할 수 있습니다.

```
php artisan user:create --name=이름 --email=이메일 --password=패스워드
```

패스워드를 생략하는 경우 자동으로 임시로 만들어 집니다.

> 인자값이 없는 경우에는 ramdom한 이름과 이메일로 회원을 추가합니다.


`--verified` 옵션을 추가할 수 있습니다.


### 패스워드 변경
회원의 패스워드를 변경합니다.

```
php artisan user:password 이메일 패스워드
```

### 관리자 회원 변경
지정한 회원을 관리자 등급으로 변경합니다. users 테이블의 isAdmin 필드를 1로 설정합니다.

```
php artisan user:admin 이메일 --enable
php artisan user:admin 이메일 --disable
```

## 미들웨어

### admin 체크
`users`테이블의 isAdmin 필드값을 통하여 admin 회원 여부를 체크하는 미들웨어를 제공합니다.
라우트에서 `admin`을 추가합니다.

```php
Route::get('_admin/test', [\Jiny\Auth\Http\Controllers\Admin\AdminTestController::class, 'index'])
->middleware(['web', 'auth','admin']);
```
