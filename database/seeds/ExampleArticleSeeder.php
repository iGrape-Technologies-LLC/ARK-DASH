<?php

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\ArticleProperty;
use App\Models\Combination;
use App\Models\Photo;

class ExampleArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $art = new Article();
        $art->title = 'Example article';
        $art->description = 'This is an awesome article';
        $art->price = 111;
        $art->currency_id = 1;
        $art->user_id = 1;
        $art->category_id = 1;
        $art->save();

        $art->features()->attach([1]);

        $artprop = new ArticleProperty();
        $artprop->article_id = $art->id;
        $artprop->price = 0;
        $artprop->stock = 1;
        $artprop->save();

        $artprop->values()->attach([1]);

        $ph = new Photo();
        $ph->name = 'Original name';
        $ph->path = 'somepath.jpg';
        $ph->article_id = $art->id;
        $ph->principal = false;
        $ph->save();
    }
}
