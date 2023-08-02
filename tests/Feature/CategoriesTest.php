<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\User;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use App\Http\Middleware\Authenticate;

class CategoriesTest extends TestCase
{

    public function setUp() : void {
        parent::setUp();

        $user = factory(User::class)->make();

        $this->actingAs($user);

        $this->withoutMiddleware(PermissionMiddleware::class);
    }

    public function testCategoriesList() {
        $categoryMock = $this->mock(Category::class, function($mock) {
            $mock->shouldReceive('orderBy')->andReturn($mock);
            $mock->shouldReceive('get')->andReturn([]);
        });

        $response = $this->withHeaders([
            "X-Requested-With" => "XMLHttpRequest",
        ])->json('GET', '/admin/categories');

        $response->assertJson(['data' => []]);
    }

    public function testCreateCategory() {
        $this->mock(Category::class, function($mock) {
            $mock->shouldReceive('create')->andReturn($mock);
            $mock->shouldReceive('save');
        });

        $response = $this->withHeaders([
            "X-Requested-With" => "XMLHttpRequest",
        ])->json('POST', '/admin/categories/new', [
            'name' => 'SOMENAME'
        ]);

        $response->assertJson(['success' => true]);
    }

    public function testCreateCategoryEmptyName() {
        $response = $this->withHeaders([
            "X-Requested-With" => "XMLHttpRequest",
        ])->json('POST', '/admin/categories/new', []);

        $response->assertStatus(422);
    }

    public function testUpdateCategory() {
        $this->mock(Category::class, function($mock) {
            $mock->shouldReceive('find')->andReturn($mock);
            $mock->shouldReceive('update');
        });

        $response = $this->withHeaders([
            "X-Requested-With" => "XMLHttpRequest",
        ])->json('POST', '/admin/categories/update/1', [
            'name' => 'SOMENAME'
        ]);

        $response->assertJson(['success' => true]);
    }

    public function testDeleteCategory() {
        $returned = $this->mock(Category::class, function($mock) {
            $mock->shouldReceive('delete')->once();
        });

        $this->mock(Category::class, function($mock) use ($returned) {
            $mock->shouldReceive('find')->with(1)->once()->andReturn($returned);
        });

        $this->get('/admin/categories/delete/1');
    }
}