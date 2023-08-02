<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserFavorites;

class FavoritesController extends Controller
{
    public function add(Request $request) {

        if(request()->ajax()) {

            $request->validate([
                'article_id' => 'required|integer',
            ]);

    		$user = Auth::user();

    		$fav = new UserFavorites([
    			'article_id' => $request->input('article_id'),
    			'user_id' => $user->id
    		]);
    		$fav->save();

        
            $output = ['success' => true, 'msg' => __("general.favorite_added")];
            return $output;
        }
    	
    }

    public function delete(Request $request) {
        if(request()->ajax()) {

            $request->validate([
                'article_id' => 'required|integer',
            ]);
            
    		$user = Auth::user();

    		UserFavorites::where('user_id', $user->id)
    			->where('article_id', $request->input('article_id'))->delete();

            $output = ['success' => true, 'msg' => __("general.favorite_deleted")];
            return $output;
        }
    	
    }

    public function toggleFavorite(Request $request) {      
        if(request()->ajax()) {

            $request->validate([
                'article_id' => 'required|integer',
            ]);
            
            $user = Auth::user();

            $article_id = $request->input('article_id');

            $has = false;
            foreach ($user->favorites as $favorite) {
                if($favorite->article_id == $article_id) {
                    $has = true;
                }
            }            

            if($has){
                UserFavorites::where('user_id', $user->id)
                ->where('article_id', $request->input('article_id'))->delete();

                $output = ['success' => true, 'msg' => __("general.favorite_deleted")];
            } else{
                $fav = new UserFavorites([
                    'article_id' => $request->input('article_id'),
                    'user_id' => $user->id
                ]);
                $fav->save();

            
                $output = ['success' => true, 'msg' => __("general.favorite_added")];
            }
            
            return $output;
        }
            
    }

    public function index() {
    	$favorites = UserFavorites::where('user_id', Auth::user()->id)->get();

    	return view('admin.favorites.list', compact('favorites'));
    }
}
