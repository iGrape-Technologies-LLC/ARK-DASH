<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

class UsersTest extends TestCase
{
    
    public function testSimple() {
        $this->assertTrue(true);
    }

    /* public function testProfile() {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $stb = $this->createStub(User::class);
        $this->actingAs($stb);
        $stb->expects($this->once())->method('save');

        $this->post('/admin/users/profile', [
        	'name' => 'asdasdasd',
            'lastname' => 'SOMEFAKE LASTNAME',
        	'email' => 'asda@asd.com'
        ]);
    }

    protected function tearDown(): void {
        \Mockery::close();
    } */
}