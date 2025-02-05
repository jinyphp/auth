<?php
namespace Jiny\Auth\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

// use Jiny\Auth\Http\AuthRegist;

/**
 * 회원가입 컴포넌트
 */
class AuthRegistForm extends Component
{

    public $viewFile;
    public $message;

    public $forms = [];
    public $errors = [];
    public $setting = [];

    public $spinner = false;


    public function mount()
    {
        if(!$this->viewFile) {
            $this->viewFile = "jiny-auth::regist.form.layout";
        }

        $this->setting = config("jiny.auth.setting");
    }

    public function render()
    {
        return view($this->viewFile);
    }

    public function submit()
    {
        // step1.이름 검증
        $name = $this->checkName();

        // step2.이메일 검증
        $email =$this->checkEmail();

        // step3.비밀번호 검증
        $this->checkPassword();

        // $auth = new AuthRegist($this->forms, $this->setting);
        // $auth->save();
        // $this->errors = $auth->errors;
        // $this->spinner = $auth->spinner;

        // 예약회원 검사
        $reserved = DB::table('user_reserved')
            ->where('email', $email)->first();
        if($reserved) {
            $this->errors['email'] = [
                'status' => 'danger',
                'message' => '예약어로 가입이 제한된 이메일 주소 입니다.',
            ];
            return false;
        }

        // 블랙리스트 검사
        $blacklist = DB::table('user_blacklist')
            ->where('email', $email)->first();
        if($blacklist) {
            $this->errors['email'] = [
                'status' => 'danger',
                'message' => '블랙리스트로 가입이 제한된 이메일 주소 입니다.',
            ];
            return false;
        }


        $this->spinner = true;
        if($email && $user = $this->tempRegist()) {

            // 이메일 검증여부 확인처리
            if($verify = $this->processVerify($user)) {
                // 검증 대기 화면으로 이동처리
                $this->redirect($verify);
                return false;
            }

            // Ste6.회원약관 동의이력 저장
            if(!$this->processAgree($user)) {
                return false;
            }

            // step7.회원승인
            if($auth = $this->processAuth($user)) {
                // 인증 대기 화면으로 이동처리
                $this->redirect($auth);
                return false;
            }

            // Step7. user_id를 이용한 자동 로그인 처리
            if(!$auth && !$verify) {
                Auth::loginUsingId($user->id);
            }

            // Step8.로그인 로그기록
            $this->processLog($user);

            // 성공페이지 설정시
            if(!$this->processSuccess()) {
                // 성공후 지정한 페이지로 이동
                if($result = $this->processRedirect()){
                    $this->redirect($result);
                }
            } else {
                // 성공페이지 출력
            }

            return true;
        }

        return false;

    }

    /**
     * 페이지 이동
     * 회원 가입후 이동하는 경로
     */
    private function processRedirect()
    {
        if(isset($this->setting['login']['home'])) {
            if($this->setting['login']['home']) {
                return $this->setting['login']['home'];
            } else {
                return "/home";
            }
        }

        return "/";
    }

    /**
     * 회원가입 성공 페이지 설정
     */
    private function processSuccess()
    {
        if(isset($this->setting['regist']['success'])) {
            if($this->setting['regist']['success']) {

                // 회원가입 성공 페이지 설정
                if(isset($this->setting['regist']['success_view'])) {
                    if($this->setting['regist']['success_view']) {
                        $this->viewFile = $this->setting['regist']['success_view'];
                    }
                } else {
                    $this->viewFile = "jiny-auth::login.success.layout";
                }

                return true;
            }
        }

        return false;
    }

    private function processLog($user)
    {
        // 접속횟수 증가
        userLogCount($user->id);

        if(isset($this->setting['login']['log'])) {
            if($this->setting['login']['log']) {

                // log 기록을 DB에 삽입
                DB::table('user_logs')->insert([
                    'user_id' => $user->id,
                    'provider'=>"email",
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);

            }
        }
    }

    private function processAuth($user)
    {
        // 회원승인
        // 자동 인증설정 처리
        if(isset($this->setting['auth']['enable'])) {
            if($this->setting['auth']['enable']) {

                $auth = [
                    'email' => $user->email,
                    'user_id' => $user->id,
                    'created_at' => date("Y-m-d h:i:s"),
                    'updated_at' => date("Y-m-d h:i:s")
                ];

                // 자동 인증 여부
                if(isset($this->setting['auth']['auto'])){
                    if($this->setting['auth']['auto']) {

                        // 승인 테이블 데이터 추가
                        $auth['enable'] = 1;
                        $auth['auth'] = 1;
                        $auth['auth_date'] = date("Y-m-d h:i:s");
                        $auth['description'] = '자동 인증';

                        DB::table('users_auth')->insert($auth);
                        DB::table('users')->where('id', $user->id)->update([
                            'auth'=>1
                        ]);

                        return true;
                    }
                }

                // 인증요청
                $auth['enable'] = 0;
                $auth['auth'] = 0;
                DB::table('users_auth')->insert($auth);

                // 자동인증 아닌경우...
                return '/login/auth';
            }
        }

        return false;
    }

    private function processAgree($user)
    {
        if(isset($this->setting['agree']['enable'])) {
            if($this->setting['agree']['enable']) {

                // 세션에 동의서 저장
                if(session()->has('agree')) {
                    $agree = [];
                    $rows = session('agree');

                    foreach($rows as $item) {
                        $agree []= [
                            'user_id'=>$user->id, // 사용자id
                            'agree_id'=>$item, // 동의서 id
                            'agree' => 1,
                            'created_at' => date("Y-m-d"),
                            'updated_at' => date("Y-m-d")
                        ];
                    }

                    DB::table('user_agreement_logs')->insert($agree);

                    // 저장후, 세션삭제
                    session()->forget('agree');

                } else {
                    $this->viewFile = "jiny-auth::errors.message";
                    $this->message = "회원약관 동의이력 저장 오류";
                    return false;
                }

            }
        }

        return true;
    }

    private function processVerify($user)
    {
        if(isset($this->setting['regist']['verified'])) {
            if($this->setting['regist']['verified']) {

                // 회원 검증 이메일 발송
                // notify 메소드를 호출하기 위해서는 모델이 필요
                $userModel = User::where('email', $user->email)->first(); //

                $verificationToken = "";
                $userModel->notify(new VerifyEmail($verificationToken));

                // 이메일 검증 안내 화면으로 이동
                return "/regist/verified";

            }
        } else {
            // 검증이 필요없는 경우, 바로 회원가입 처리
            // 회원 가입 축하 이메일 발송
            if(isset($this->setting['regist']['mail'])) {
                if($this->setting['regist']['mail']) {
                    //$user->notify(new WelcomeEmailNotification($user));
                }
            }
        }

        return false;
    }

    private function tempRegist()
    {
        // step4.중복 여부 확인인
        $user = $this->isDatabaseUser($this->forms['email']);
        if($user) {
            $this->errors['email'] = [
                'status' => 'danger',
                'message' => '이미 가입된 이메일 주소 입니다.',
            ];
            return false;
        }

        // step5.신규 유저 임시 DB등록
        $user = $this->createNewUser();
        return $user;
    }


    private function createNewUser()
    {
        // 신규 유저 임시 DB등록
        $id = DB::table('users')->insertGetId([
            'name' => $this->forms['name'],
            'email' => $this->forms['email'],
            'password' => Hash::make($this->forms['password']),

            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $user = DB::table('users')->where('id', $id)->first();
        return $user;
    }

    /**
     * DB에 회원 이메일이 존재하는지 확인
     */
    private function isDatabaseUser($email)
    {
        $user = DB::table('users')->where('email', $email)->first();
        if($user) {
            return $user;
        }

        return false;
    }




    /**
     * 이름 유효성 검증
     */
    public function checkName()
    {
        if(isset($this->forms['name']) && $this->forms['name']) {
            unset($this->errors['name']);
            $this->spinner |= 0b0001;
            return $this->forms['name'];
        } else {
            $this->errors['name'] = [
                'status' => 'danger',
                'message' => '이름을 입력해 주세요',
            ];
            $this->spinner &= ~0b0001;
        }

        return false;
    }

    /**
     * 이메일 유효성 검증
     */
    public function checkEmail()
    {
        // 이메일 검증
        if(isset($this->forms['email']) && $this->forms['email']) {
            unset($this->errors['email']);
        } else {
            $this->errors['email'] = [
                'status' => 'danger',
                'message' => '이메일을 입력해 주세요',
            ];
            $this->spinner &= ~0b0010;
        }

        // 이메일 형식 검증
        if(isset($this->forms['email']) && $this->forms['email']) {
            $email = $this->forms['email'];
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                unset($this->errors['email']);
                $this->spinner |= 0b0010;
                return $this->forms['email'];
            } else {
                $this->errors['email'] = [
                    'status' => 'danger',
                    'message' => '올바른 이메일 형식이 아닙니다.'
                ];
                $this->spinner &= ~0b0010;
            }
        }

        return false;
    }

    // 비밀번호 검증
    public function checkPassword()
    {
        // 비밀번호 검증
        if(isset($this->forms['password']) && $this->forms['password']) {
            unset($this->errors['password']);
        } else {
            $this->errors['password'] = [
                'status' => 'danger',
                'message' => '비밀번호를 입력해 주세요',
            ];
            $this->spinner &= ~0b0100;
            return false;
        }

        // 패스워드 규칙 검증
        $password = $this->forms['password'];

        if(isset($this->setting['password']['min'])) {

            if($this->setting['password']['min']) {
                $password_len = $this->setting['password']['min'];
            } else {
                $password_len = 8;
            }

            if(strlen($password) < $password_len) {
                $this->errors['password'] = [
                    'status' => 'danger',
                    'message' => "패스워드 최소 ".$password_len."자 이상 되어야 합니다.",
                ];
                $this->spinner &= ~0b0100;
                return false;
            }
        }

        if(isset($this->setting['password']['max'])) {
            if($this->setting['password']['max']) {
                $password_len = $this->setting['password']['max'];
            } else {
                $password_len = 20;
            }

            if(strlen($password) >= $password_len) {
                $this->errors['password'] = [
                    'status' => 'danger',
                    'message' => "패스워드 최대 ".$password_len."자 입니다.",
                ];
                $this->spinner &= ~0b0100;
                return false;
            }
        }

        // 특수문자 포함여부 체크
        if(isset($this->setting['password']['special'])
            && $this->setting['password']['special']) {
            if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $password)) {
                //return "비밀번호에는 특수문자가 포함되어야 합니다.";
                $this->errors['password'] = [
                    'status' => 'danger',
                    'message' => "비밀번호에는 특수문자가 포함되어야 합니다.",
                ];
                $this->spinner &= ~0b0100;
                return false;
            }
        }

        // 숫자 포함 체크
        if(isset($this->setting['password']['number'])
            && $this->setting['password']['number']) {
            if (!preg_match('/[0-9]/', $password)) {
                //return "비밀번호에는 숫자가 포함되어야 합니다.";
                $this->errors['password'] = [
                    'status' => 'danger',
                    'message' => "비밀번호에는 숫자가 포함되어야 합니다.",
                ];
                $this->spinner &= ~0b0100;
                return false;
            }
        }

        // 영문자 포함 체크
        if(isset($this->setting['password']['alpha'])
            && $this->setting['password']['alpha']) {
            if (!preg_match('/[a-zA-Z]/', $password)) {
                //return "비밀번호에는 영문자가 포함되어야 합니다.";
                $this->errors['password'] = [
                    'status' => 'danger',
                    'message' => "비밀번호에는 영문자가 포함되어야 합니다.",
                ];
                $this->spinner &= ~0b0100;
                return false;
            }
        }

        // 비밀번호 검증 완료
        $this->errors['password'] = [
            'status' => 'success',
            'message' => '비밀번호 확인완료',
        ];
        $this->spinner |= 0b0100;
        return true;
    }

    public function confirmPassword()
    {
        if(isset($this->forms['confirm_password']) && $this->forms['confirm_password']) {
            unset($this->errors['confirm_password']);

        }

        if(isset($this->forms['password']) && $this->forms['password']) {
            if($this->forms['password'] == $this->forms['confirm_password']) {
                $this->spinner |= 0b1000;
                return true;
            }
        } else {
            $this->checkPassword();
        }

        $this->errors['confirm_password'] = [
            'status' => 'danger',
            'message' => '비밀번호가 일치 하지 않습니다.',
        ];
        $this->spinner &= ~0b1000;
        return false;
    }





}
