# jinyPHP Auth
라라벨 기반의 인증을 확장한 모듈입니다. 기본 인증 뿐만 아니라, 회원을 관리할 수 있는 Admin 기능을 가지고 있습니다.

## 커멘드
artisan 명령을 이용하여 콘솔창에서도 회원을 등록, 관리자 기능을 활성화 시킬 수 있습니다.

새로운 사용자 등록
```
php artisan user:create -u 이름 -e 이메일 -p 패스워드
```

관리자 활성화
```
php artisan user:admin 이메일 --enable
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
