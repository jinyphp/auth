# jinyPHP Auth
라라벨 기반의 인증을 확장한 모듈입니다. 기본 인증 뿐만 아니라, 회원을 관리할 수 있는 Admin 기능을 가지고 있습니다.

## 설치
기본 라라벨 회원 테이블을 확장하여 보다 향상된 회원인증을 처리할 수 있습니다.

```bash
composer require jiny/auth
```
> 의존성 : `jiny/auth-users`,`jiny/auth-profile`,`jiny/auth-social` 


## 설정
환경설정을 패키지에서 복사하여 설치를 합니다. 다음과 같이 콘솔창에서 입력하세요.

```
php artisan vendor:publish --provider="Jiny\Auth\JinyAuthServiceProvider"
```

`/config/jiny/auth.php` 파일이 생성됩니다. 설정값에서 화면과 url등의 설정값을 정의합니다.

```php
<?php
return [
    'urls'=>[
        'home' => "/", // 로그인호 홈으로 이동
        'logout_back' => "/" // 로그아웃후 이동되는 경로
    ],
    'views'=>[
        'login'=> "jiny-auth::login",
        'regist'=>"jiny-auth::register"
    ]
];
```

### actions rules 배포

```
php artisan vendor:publish --tag=auth-actions --force
```

## 커멘드
artisan 명령을 이용하여 콘솔창에서도 회원을 등록, 관리자 기능을 활성화 시킬 수 있습니다.

새로운 사용자 등록
```
$ php artisan user:create --name=이름 --email=이메일 --password=패스워드
```

관리자 활성화
```
php artisan user:admin 이메일 --enable
```

슈퍼 관리자 활성화
```
php artisan user:super 이메일 --enable
```

## 설치
```
composer require jiny/auth
```

## 라우팅

* /admin/auth/users : 회원목록
* 역할 : /admin/auth/roles
* reserved : /admin/auth/reserved
* Agree : /admin/auth/agree
* teams : /admin/auth/teams
* setting : /admin/auth/setting



php artisan vendor:publish --tag=laravel-mail

## 확장모듈 소개
지니Auth를 기반으로 다양한 회원정보를 유지관리 할 수 있는 추가 모듈을 제공합니다.
> 일부 모듈은 유료로 제공됩니다.

* module-accounts
회원의 상세(성-중간-이름) 이름정보를 관리하고, 아바타 이미지, 멀티 주소록, 멀티 연락처등 관리 가능합니다.


