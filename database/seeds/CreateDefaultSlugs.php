<?php

use Illuminate\Database\Seeder;
use App\Models\Article;

class CreateDefaultSlugs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    		// actualizar el modelo genera el slug automaticamente
        $articles = Article::all();
        foreach($articles as $article) {
        	$article->save();
        }
    }
}
