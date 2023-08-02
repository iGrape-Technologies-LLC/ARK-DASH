<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Article;
use App\Helpers\CookieHelper;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Validator;

class ArticlesListTest extends TestCase
{
    public function testSimple() {
        $this->assertTrue(true);
    }

    /* public function testEmptyFilterList() {
        $this->mock(Builder::class, function($builderMock) {
            $this->mock(Article::class, function($mock) use ($builderMock) {
                $mock->shouldReceive('with')->once()->andReturn($builderMock);

                $builderMock->shouldReceive('where')->with('active', true)->once()->andReturn($builderMock);

                $builderMock->shouldReceive('orderBy')->once();

                $builderMock->shouldReceive('paginate')->once();
            });
        });

        $response = $this->get('/articles');
    }

    public function testSearchBoxQuery() {
        Validator::shouldReceive('make')->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->mock(CookieHelper::class, function($mock) {
            $mock->shouldReceive('set')->once();
        });

        $this->mock(Builder::class, function($builderMock) {
            $this->mock(Article::class, function($mock) use ($builderMock) {
                $mock->shouldReceive('with')->once()->andReturn($builderMock);

                $builderMock->shouldReceive('where')->with('active', true)->once()->andReturn($builderMock);

                $builderMock->shouldReceive('where')->with('title', 'like', '%SOMEQUERY%')->once();

                $builderMock->shouldReceive('orderBy')->once();

                $builderMock->shouldReceive('paginate')->once();
            });
        });

        $response = $this->get('/articles?s=SOMEQUERY');
    }

    public function testCategoryFilter() {
        Validator::shouldReceive('make')->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->partialMock(Builder::class, function($builderMock) {
            $this->mock(Article::class, function($mock) use ($builderMock) {
                $mock->shouldReceive('with')->once()->andReturn($builderMock);

                $builderMock->shouldReceive('where')->with('active', true)->once()
                    ->andReturn($builderMock);

                $builderMock->shouldReceive('where')->once();

                $builderMock->shouldReceive('orderBy')->once();

                $builderMock->shouldReceive('paginate')->once();
            });
        });

        $response = $this->get('/articles?category_id=1');
    }

    public function testMinPriceFilter() {
        Validator::shouldReceive('make')->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->mock(Builder::class, function($builderMock) {
            $this->mock(Article::class, function($mock) use ($builderMock) {
                $mock->shouldReceive('with')->once()->andReturn($builderMock);

                $builderMock->shouldReceive('where')->with('active', true)->once()
                    ->andReturn($builderMock);

                $builderMock->shouldReceive('where')->with('price', '>=', 'SOMEPRICE')
                    ->once();

                $builderMock->shouldReceive('orderBy')->once();

                $builderMock->shouldReceive('paginate')->once();
            });
        });

        $response = $this->get('/articles?min_price=SOMEPRICE');
    }

    public function testMaxPriceFilter() {
        Validator::shouldReceive('make')->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->mock(Builder::class, function($builderMock) {
            $this->mock(Article::class, function($mock) use ($builderMock) {
                $mock->shouldReceive('with')->once()->andReturn($builderMock);
                
                $builderMock->shouldReceive('where')->with('active', true)->once()
                    ->andReturn($builderMock);

                $builderMock->shouldReceive('where')->with('price', '<=', 'SOMEPRICE')
                    ->once();

                $builderMock->shouldReceive('orderBy')->once();

                $builderMock->shouldReceive('paginate')->once();
            });
        });

        $response = $this->get('/articles?max_price=SOMEPRICE');
    }

    public function testListOrdering() {
        Validator::shouldReceive('make')->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->mock(Builder::class, function($builderMock) {
            $this->mock(Article::class, function($mock) use ($builderMock) {
                $mock->shouldReceive('with')->once()->andReturn($builderMock);
                
                $builderMock->shouldReceive('where')->with('active', true)->once()
                    ->andReturn($builderMock);

                $builderMock->shouldReceive('orderBy')->twice();

                $builderMock->shouldReceive('paginate')->once();
            });
        });

        $response = $this->get('/articles?order=newest');
    } */
}
