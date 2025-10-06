<?php

namespace Jiny\Auth\Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class RegisterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 테스트 사용자 정리
        User::where('email', 'like', 'test-%@example.com')->delete();

        // 약관이 없으면 생성
        if (\DB::table('user_terms')->count() === 0) {
            \DB::table('user_terms')->insert([
            [
                'title' => '이용약관',
                'content' => '<p>서비스 이용약관</p>',
                'enable' => '1',
                'required' => '1',
                'pos' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => '개인정보처리방침',
                'content' => '<p>개인정보 처리방침</p>',
                'enable' => '1',
                'required' => '1',
                'pos' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ]);
        }
    }

    protected function tearDown(): void
    {
        // 테스트 사용자 정리
        User::where('email', 'like', 'test-%@example.com')->delete();

        parent::tearDown();
    }

    /** @test */
    public function test_register_page_is_displayed()
    {
        $response = $this->get('/register');
        
        $response->assertStatus(200);
        $response->assertSee('회원가입');
        $response->assertSee('이름');
        $response->assertSee('이메일');
        $response->assertSee('비밀번호');
    }

    /** @test */
    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'name' => '홍길동',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => '홍길동',
        ]);
    }

    /** @test */
    public function test_duplicate_email_is_rejected()
    {
        // 기존 사용자 생성
        User::create([
            'name' => '기존사용자',
            'email' => 'existing@example.com',
            'password' => Hash::make('password'),
            'uuid' => \Str::uuid(),
        ]);

        $response = $this->post('/register', [
            'name' => '신규사용자',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function test_password_must_be_confirmed()
    {
        $response = $this->post('/register', [
            'name' => '홍길동',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => '홍길동',
            'email' => 'test@example.com',
            'password' => 'Pass1!',
            'password_confirmation' => 'Pass1!',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => '홍길동',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function test_email_must_be_valid_email()
    {
        $response = $this->post('/register', [
            'name' => '홍길동',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => '홍길동',
            'email' => 'test@example.com',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function test_password_is_hashed()
    {
        $this->post('/register', [
            'name' => '홍길동',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $user = User::where('email', 'test@example.com')->first();
        
        $this->assertNotNull($user);
        $this->assertNotEquals('Password123!', $user->password);
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    /** @test */
    public function test_user_profile_is_created()
    {
        $this->post('/register', [
            'name' => '홍길동',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '010-1234-5678',
            'terms' => [1 => 'on', 2 => 'on'],
        ]);

        $user = User::where('email', 'test@example.com')->first();
        
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
        ]);
    }
}
