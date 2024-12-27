# 로그인
`auth`인증 페키지는 회원 로그인을 처리하는 페이지와 기능을 제공합니다.

## 로그인 환경설정
사이트의 로그인 동작을 처리하기 위한 환경설정을 지정할 수 있습니다. 환경 파일은 `config/jiny/login.php`에 설정이 되어 있습니다.

### 로그인 허용
폐쇄적인 사이트 운영을 원하는 경우에는 로그인 가입절차를 중단할 수 있습니다. 이를 위해서는 로그인 허용 기능을 disable 해줄 수 있습니다.

```php
'disable' => false
```
> disable 기능을 true로 변경하면 로그인 동작이 제한됩니다.



## 로그인 페이지
적절한 디자인의 페이지를 작성한 후에 컴포넌트 코드를 삽입하여 로그인 페이지를 생성할 수 있습니다.

### 로그인 화면 컨트롤러
로그인의 화면을 처리하는 컨트롤러는 `LoginViewController` 입니다. 이 컨트롤러는 라우터에서 `/login` uri로 연결이 되어 있습니다.`LoginViewController`는 우선 순위에 따라서 화면 레이아웃을 불러오게 됩니다.


### 로그인 컴포넌트
blade 컴포넌트 코드를 통하여 간단하게 로그인 입력폼 양식을 페이지에 삽입할 수 있습니다.

```php
<x-login-form>
    @includeIf('jiny-auth::login.form')
</x-login-form>
```

`<x-login-form>` 는 서버로 로그인을 요청하는 post form 양식을 포함하고 있습니다. 

### 로그인폼 양식
> 로그인 폼의 입력 양식은 `jiny-auth::login.form`을 참고하면 됩니다.

로그인 화면을 쉽게 구현할 수 있도록 각각의 입력폼은 컨포넌트로 간소화 되어 있습니다.

```php
<x-login-email>
    <small>
        회원가입 이메일을 입력해 주세요.
    </small>
</x-login-email>

<x-login-password>
    <small>
        <x-login-forgot>
            {{ __('Forgot your password?') }}
        </x-login-forgot>
    </small>
</x-login-password>

<div>
    <div class="form-check align-items-center">
        <x-login-remember>
            {{ __('Remember me') }}
        </x-login-remember>
    </div>
</div>

<div class="d-grid gap-2 mt-3">
    <x-login-submit>
        {{ __('Log in') }}
    </x-login-submit>
</div>
```


## 로그인 성공
로그인이 정상적으로 성공을 하게 되면 `/home` 경로로 이동을 하게 됩니다.

### 로그인 처리 컨트롤러
로그인을 POST로 요청하게 되면 `AuthSessionController` 컨트롤러에 의해서 응답을 받게 됩니다.


### home 경로
환경설정 `config/jiny/auth/setting` 안에 있는 `['login']['home']`에서 지정된 값으로 로그인후 페이지 이동을 하게 됩니다. 기본 값은 `/home` 입니다.
