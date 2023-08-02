<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleProperty;
use App\Models\Category;
use App\Models\PropertyValue;
use App\Models\Property;
use App\Models\Feature;
use App\Models\FeatureValue;
use App\Models\File;
use App\Models\Photo;
use App\Models\Video;
use App\Models\City;
use App\Models\Brand;
use App\Models\Tag;
use App\Models\State;
use App\Models\Advertisement;
use App\Models\Currency;
use App\Models\Combination;
use App\Utils\UtilGeneral;
use App\Utils\UtilFile;
use App\Helpers\CookieHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class ArticlesController extends Controller
{
    private $article;
    private $cookies;

    protected $UtilGeneral;
    protected $UtilFile;

    public function __construct(Article $article, CookieHelper $cookies, UtilGeneral $UtilGeneral, UtilFile $UtilFile) {
        $this->article = $article;
        $this->cookies = $cookies;

        $this->UtilGeneral = $UtilGeneral;   
        $this->UtilFile = $UtilFile;   

        $this->middleware('permission:article.list|article.create|article.edit|article.delete', ['only' => ['index']]);
        $this->middleware('permission:article.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:article.edit', ['only' => ['update']]);
        $this->middleware('permission:article.delete', ['only' => ['destroy']]);
    }

    public function detail($id) {
        $article = Article::where('slug', $id)->where('active', 1);

        if(config('config.SHOW_PRICING')){
                $article = $article
                                    ->whereHas('properties', function($properties){
                                        $properties->where('price', '>', '0');
                                    });                                
        }

        if(config('config.SHIPPING_REQUIRED')){
            $article = $article->whereNotNull('weight')->whereNotNull('size_x')->whereNotNull('size_y')->whereNotNull('size_z');
        }

        $article = $article->first();
       
        if($article != null) {
            $current_visits = $article->visits_count;
            $article->update([
                'visits_count' => ($current_visits + 1)
            ]);

            $related_articles = [];
                        
            $articleCategories = $article->categories;
            if($articleCategories != null && count($articleCategories) > 0) {
                $related_articles = Article::where('active', '1')->whereHas('categories', function($query) use($articleCategories) {
                    $query->where('category_id', $articleCategories[0]->id);
                    for($i = 1;$i < count($articleCategories);$i++) {
                        $query->orWhere('category_id', $articleCategories[$i]->id);
                    }
                })->where('id', '<>', $article->id);

                
                if(config('config.SHOW_PRICING')){
                $related_articles = $related_articles
                                    ->whereHas('properties', function($properties){
                                        $properties->where('price', '>', '0');
                                    });                                
                }

                if(config('config.SHIPPING_REQUIRED')){
                    $related_articles = $related_articles->whereNotNull('weight')->whereNotNull('size_x')->whereNotNull('size_y')->whereNotNull('size_z');
                }

                $related_articles = $related_articles->limit(3)->get();
                                
            }

            $properties = Property::all();

            return view('front.pages.article', compact('article', 'related_articles', 'properties'));
        }else{
            abort(404);
        }
    }

    public function list(Request $request) {
        $this->article = $this->article
                                ->with('properties')
                                ->where('active', true)
                                ->orderBy('priority', 'ASC');

        if(config('config.SHOW_PRICING')){
                $this->article = $this->article
                                    ->whereHas('properties', function($properties){
                                        $properties->where('price', '>', '0');
                                    });                                
        }

        if(config('config.SHIPPING_REQUIRED')){
            $this->article = $this->article->whereNotNull('weight')->whereNotNull('size_x')->whereNotNull('size_y')->whereNotNull('size_z');
        }

        $validator = Validator::make($request->query(), [
            's' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric','min:0'],
            'max_price' => ['nullable', 'numeric','min:0'],
            'category_id' => ['nullable', 'numeric', 'exists:categories,id'],
            'state_id' => ['nullable', 'numeric', 'exists:states,id'],
            'order' => ['nullable', Rule::in(['newest', 'cheapest', 'menu-order'])]
        ]);

        $features = Feature::orderBy('name')->get();
        $properties = Property::orderBy('sort')->get();

        if(!$validator->fails()) {
            // Comienza filtro
            if($request->query('s') != null) {
                $cleanedSearch = 
                str_replace('+', ' ', 
                    urlencode(
                        str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], 
                            $request->query('s'))));

                $this->article->where('title', 'like', '%' . $cleanedSearch . '%');
            }

            if($request->query('min_price') != null) {
                $this->article->where('price', '>=', $request->query('min_price'));
            }

            if($request->query('max_price') != null) {
                $this->article->where('price', '<=', $request->query('max_price'));
            }

            if($request->query('category_id') != null) {
                $variable = $request->query('category_id');
                $this->article->whereHas('categories', function($q) use ($variable) { 
                    $q->where('id',$variable); 
                });
            }

            if($request->query('brand_id') != null) {
                $brand_id = $request->query('brand_id');
                $this->article->whereHas('brands', function($brands) use ($brand_id) {
                    $brands->where('brands.id', $brand_id);
                });
            }

            foreach($features as $feature) {
                $feature_query = urlencode(strtolower( $feature->name) . '_id');                

                if($request->query($feature_query) != null) {
                    $request->validate([
                        $feature_query => ['numeric', 'min:0']
                    ]);

                    $feature_value_id = $request->query($feature_query);

                    $this->article->whereHas('features', function(Builder $query) use ($feature_value_id) {
                        $query->where('feature_values.id', $feature_value_id);
                    });
                }
            }

            foreach($properties as $property) {
                $property_query = urlencode(strtolower($property->name . '_id'));

                if($request->query($property_query) != null) {
                    $request->validate([
                        $property_query => ['numeric', 'min:0']
                    ]);

                    $property_value_id = $request->query($property_query);

                    $this->article->whereHas('properties', function(Builder $query) use ($property_value_id) {
                        $query->whereHas('values', function(Builder $query2) use ($property_value_id) {
                            $query2->where('property_values.id', $property_value_id);
                        });
                    });
                }
            }

            if($request->query('state_id') != null) {
                $state_id = $request->query('state_id');

                $this->article->whereHas('user', function(Builder $query) use ($state_id) {
                    $query->whereHas('city', function(Builder $query2) use ($state_id) {
                        $query2->where('state_id', $state_id);
                    });
                });
            }

            if($request->query('order') != null) {
                if($request->query('order') == 'newest') {
                    $this->article->orderBy('created_at', 'desc');
                }

                if($request->query('order') == 'cheapest') {
                    $this->article->orderBy('price', 'asc');
                }
            }
            // EOF Filtro
        }

        $this->article->orderBy('featured', 'desc');

        $articles = $this->article->paginate(6);

        $categories = Category::withTrashed()->get();

        $asideAds = Advertisement::where('active', true)
            ->where('aside', true)
            ->get();

        $interProductAds = Advertisement::where('active', true)
            ->where('inter_product', true)
            ->get()
            ->shuffle()
            ->all();

        $counters = array();
        /* foreach($categories as $category) {
            $counters[$category->id] = 0;
            foreach($articles as $article) {
                if($article->category_id == $category->id) {
                    $counters[$category->id] = $counters[$category->id] + 1;
                }
            }
        } */

        $states = State::orderBy('name', 'asc')->get();
        $brands = Brand::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();


        return view('front.pages.articles', [
            'articles' => $articles,
            'categories' => $categories,
            'counters' => $counters,
            'states' => $states,
            'asideAds' => $asideAds,
            'interProductAds' => $interProductAds,
            'countGrid' => 0,
            'countList' => 0,
            'countArts' => 0,
            'features' => $features,
            'properties' => $properties,
            'brands' => $brands,
            'tags' => $tags,
        ]);
    }

    public function create(Request $request) {
        $categories = Category::orderBy('name', 'asc')->whereNull('parent_id')->get();
        $currencies = Currency::orderBy('code', 'asc')->get();
        $properties = Property::orderBy('sort')->get();
        $features = Feature::orderBy('name')->get();
        $brands = Brand::orderBy('name')->pluck('name','id');
        $tags = Tag::orderBy('name')->pluck('name','id');
        return view('admin.articles.form', compact('categories', 'currencies', 'properties', 'features', 'brands', 'tags'));
    }

    public function store(Request $request) {
        if($request->ajax()) {

            $rules = [
                    'title' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'price' => 'nullable|numeric|min:1',
                    'type' => ['required', Rule::in(['simple', 'combo'])],
                    'stock' => 'nullable|numeric',
                    'stock_limit' => 'nullable|numeric',

                    'sku' => 'nullable|string',

                    'weight' => 'nullable|numeric',
                    'size_x' => 'nullable|numeric',
                    'size_y' => 'nullable|numeric',
                    'size_z' => 'nullable|numeric',


                    'priority' => 'numeric',
                    'currency' => 'exists:currencies,id'
                ];

            if(config('config.PHOTO_REQUIRED')) {
                $rules['article-photos'] = 'required|string|min:2';
            }

            if($request->input('active') == 'on') {
                $active = true;
            } else {
                $active = false;
            }
            if($request->input('featured') != null && $request->input('featured') == 'on') {
                $featured = true;
            } else {
                $featured = false;
            }

            DB::beginTransaction();

            $categories = Category::find( json_decode($request->input('categories'), true) );
            
            

            $art = $this->article->create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'currency_id' => $request->input('currency'),
                'price' => $request->input('price'),
                'type' => $request->input('type'),
                'user_id' => $request->input('user_id'),
                'sku' => $request->input('sku'),
                'weight' => $request->input('weight'),
                'size_x' => $request->input('size_x'),
                'size_y' => $request->input('size_y'),
                'size_z' => $request->input('size_z'),
                'active' => $active,
                'priority' => empty($request->input('priority')) ? 0 : $request->input('priority'),
                'featured' => $featured
            ]);
                    

            if($art->type == 'simple') {
                ArticleProperty::create([
                    'article_id' => $art->id,
                    'price' => $art->price,
                    'stock' => $request->input('stock'),
                    'stock_limit' => $request->input('stock_limit'),
                ]);
            }

            $art->categories()->attach($categories);
                
            $brands = Brand::find( $request->input('brands'));

            $art->brands()->attach($brands);

            $tags = Tag::find( $request->input('tags'));

            $art->tags()->attach($tags);


            $principalPhoto = $request->input('principal-photo');
            $photoError = $this->UtilFile->updatePhotos($request->input('article-photos'), $art, $principalPhoto);
            if($photoError) {
                throw ValidationException::withMessages([
                    'article-photos' => __('articles.photo_required')
                ]);
            }
       
            
            // $this->updateVideos($request->input('article-videos'), null, $art);

            DB::commit();
            
            $request->session()->flash('success', __('articles.store_msg'));

            return ['success' => true, 'id' => $art->id];
        } else {
            abort(404);
        }
    }

    public function index() {
        if(request()->ajax()) {
            $userid = Auth::user()->id;

            if(Auth::user()->isAdmin() || Auth::user()->isStaff() ) {
                $datas = Article::where('user_id', '!=', '0');
            } else {
                $datas = Article::where('user_id', $userid);
            }    

            $filters = (request()->get('filters', null));            

            if (!empty($filters['category_id'])) {                
                $variable = $filters['category_id'];
                $datas->whereHas('categories', function($q) use($variable) { $q->where('id', $variable); });
            } 

            if (!is_null($filters['status'])) {                
                $variable = $filters['status'];
                $datas->where('active', $variable);
            }

            if (!is_null($filters['featured'])) {                
                $variable = $filters['featured'];
                $datas->where('featured', $variable);
            }

            if (!empty($filters['price_from'])) {                
                $variable = $filters['price_from'];
                $datas->where('price', '>=', $variable);                
            }

            if (!empty($filters['price_until'])) {                
                $variable = $filters['price_until'];
                $datas->where('price', '<=', $variable);                
            }            

            $datas = $datas->orderBy('title', 'ASC');  

            return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";     
                       
                        if (auth()->user()->can("article.view")) {
                                $html .= '<a class="btn btn-sm btn-info" target="_blank" href="' . route('front.articledetail',  [$row->slug]) . '"><i class="fa fa-eye"></i></a>';
                        }

                        if (auth()->user()->can("article.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updatearticle', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                        if (auth()->user()->can("article.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletearticle', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }

                        //if($row->type=='combo'){
                            $html .= '<a class="btn btn-sm btn-default details-control" target="_blank" ><i class="fa fa-search-plus"></i></a>';

                        //}
                        
                        return $html;
                     }                   
                )
                ->addColumn(
                    'childs',
                    function($row){
                        $properties = [];
                        foreach ($row->properties as $property) {
                            $name = $row->type=='simple' ? __('articles.simple') : $property->valuesResumeName();
                            $properties[] = array(
                                'id' => $property->id,
                                'name' => $name,
                                'price' => $property->price,
                                'stock' => $property->stock == null ? __('articles.no_control') : $property->stock
                            );

                        }
                        return ($properties);
                    }
                )
                ->addColumn(
                    'categories',
                    function ($row)  {
                        $html ="";                        
                        if(!empty($row->categoriesName())){
                            foreach($row->categoriesName() as $v){
                                $html .= '<label class="badge badge-success">'.$v.'</label>';
                            }
                        }
                        return $html;
                    }
                ) 
                ->addColumn('stock_articles', function($row){
                    return $row->stockTotal();
                })
                ->editColumn('price', function($row){
                    $html = '';

                    $html .= $row->getFullPriceFormatted();

                    if($row->current_discount != null){
                        $html .= '<span class="old-price">'.$row->getFullPriceWithoutDiscountFormatted().'</span>';
                    }
                    return $html;
                })
                ->editColumn(
                    'active', function($row) {
                         return $this->UtilGeneral->changeStatus($row->id, $row->active, 'ArticlesController'); 
                    }
                )
                ->editColumn(
                    'publish_date', function($row) {
                         return $this->UtilGeneral->format_date($row->created_at) ;
                    }
                )
                ->addColumn('mass_delete', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id .'">' ;
                })
                ->editColumn('title', function($row){                    
                    if(!$row->isValidToShow()){
                        $row->title .= ' <span class="color-warning" data-toggle="tooltip" data-placement="top" title="' . __("tooltips.warning_product") . '"><i class="fa fa-warning"></i></span>';
                    }
                    return $row->title;
                })
                ->removeColumn('properties')
                ->rawColumns(['id', 'title', 'publish_date', 'active', 'action', 'price', 'categories', 'childs', 'stock_articles', 'mass_delete'])
                ->make(true);                   
        }

        $categories = Category::forDropdown();

        $articles_alert_stock = ArticleProperty::whereRaw('stock <= stock_limit')
                                    ->join('articles', 'article_properties.article_id', '=', 'articles.id')
                                    ->orderBy('articles.title')
                                    ->get();        


        return view('admin.articles.list')->with(compact('categories', 'articles_alert_stock'));
    }

    

    public function destroy($id) {
        if (request()->ajax()) {
            try {     
                $article = Article::find($id);
                $base_path = 'app/public/';
                foreach($article->photos as $photo) {
                    if(file_exists(storage_path($base_path . $photo->path))) {
                        unlink(storage_path($base_path . $photo->path));
                    }
                }

                $article->delete();
                $output = ['success' => true, 'msg' => __("general.delete_ok")];

                
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }
            return $output;            
        }

    }

    public function edit(Request $request, $id) {
        $art = Article::with('properties')->find($id);

        if($art != null) {
            $properties = Property::all();
            $categories = Category::orderBy('name', 'asc')->get();
            $currencies = Currency::orderBy('code', 'asc')->get();
            $properties = Property::orderBy('sort')->get();
            $features = Feature::orderBy('name')->get();
            $has_transactions = $art->hasTransactions();
            $brands = Brand::orderBy('name')->pluck('name','id');
            $tags = Tag::orderBy('name')->pluck('name','id');

            return view('admin.articles.form', [
                'article' => $art,
                'categories' => $categories,
                'currencies' => $currencies,
                'properties' => $properties,
                'features' => $features,
                'has_transactions' => $has_transactions,
                'brands' => $brands,
                'tags' => $tags,
            ]);
        } else {
            abort(404);
        }
    }

    public function update_price_article_property(Request $request) {
        if($request->ajax()) {
            $request->validate([
                'value' => ['required', 'numeric']
            ]);

            $art = ArticleProperty::find($request->input('id'));

            if($art == null){
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
                return $output;
            }

            DB::beginTransaction();

            $art->price = $request->input('value');
            $art->update();

            if($art->article->type == 'simple') {
                $art->article->price = $request->input('value');
                $art->article->update();
            }

            DB::commit();

            $output = ['success' => true, 'msg' => __("articles.change_price_ok")];
            return $output;
        }
    }

    public function update_stock_article_property(Request $request) {
        if($request->ajax()) {
            $request->validate([
                'value' => ['required', 'numeric']
            ]);

            $art = ArticleProperty::find($request->input('id'));

            if($art == null){
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
                return $output;
            }

            $art->stock = $request->input('value');
            $art->update();

            $output = ['success' => true, 'msg' => __("articles.change_stock_ok")];
            return $output;
        }
    }
    

    public function update(Request $request, $id) {
        if($request->ajax()) {
            $art = $this->article->find($id);

            if($art == null) {
                abort(404);
            }

            $previous_article_type = $art->type;

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:1',
                'type' => ['required', Rule::in(['simple', 'combo'])],
                'stock' => 'nullable|numeric',

                'sku' => 'nullable|string',

                'weight' => 'nullable|numeric',
                    'size_x' => 'nullable|numeric',
                    'size_y' => 'nullable|numeric',
                    'size_z' => 'nullable|numeric',

                'priority' => 'numeric',
                'stock_limit' => 'nullable|numeric',
                'article-photos' => 'required|string|min:2',
                'currency' => 'exists:currencies,id'
            ]);

            if($request->input('active') == 'on') {
                $active = true;
            } else {
                $active = false;
            }
            if($request->input('featured') != null && $request->input('featured') == 'on') {
                $featured = true;
            } else {
                $featured = false;
            }


            DB::beginTransaction();

            $principalPhoto = $request->input('principal-photo');
            $error = $this->UtilFile->updatePhotos($request->input('article-photos'), $art, $principalPhoto);
            if($error) {
                throw ValidationException::withMessages([
                    'file' => 'Seleccione al menos una foto'
                ]);
            }
            /* $this->updateVideos($request->input('article-videos'), 
                $request->input('existentVideos'), $art); */

            $art->update([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'currency_id' => $request->input('currency'),
                'price' => $request->input('price'),

                'sku' => $request->input('sku'),

                'weight' => $request->input('weight'),
                'size_x' => $request->input('size_x'),
                'size_y' => $request->input('size_y'),
                'size_z' => $request->input('size_z'),


                'type' => $request->input('type'),
                'user_id' => $request->input('user_id'),
                'priority' => empty($request->input('priority')) ? 0 : $request->input('priority'),
                'active' => $active,
                'featured' => $featured
            ]);

            // si el articulo ya fue procesado en alguna venta no se permite el cambio de tipo
            if(!$art->hasTransactions()) {
                if($art->type == 'simple') {
                    if($previous_article_type == 'combo') {
                        // Elimino las combinaciones existentes por si antes era producto compuesto
                        $article_property_ids = ArticleProperty::where('article_id', $art->id)->get()->pluck('id');
                        ArticleProperty::destroy($article_property_ids);

                        ArticleProperty::create([
                            'article_id' => $art->id,
                            'price' => $art->price,
                            'stock' => $request->input('stock'),
                            'stock_limit' => $request->input('stock_limit')
                        ]);
                    } else {
                        $article_property = ArticleProperty::where('article_id', $art->id)->first();

                        $article_property->update([
                            'article_id' => $art->id,
                            'price' => $art->price,
                            'stock' => $request->input('stock'),
                            'stock_limit' => $request->input('stock_limit')
                        ]);
                    }
                } else if($art->type == 'combo') {
                    // si antes era articulo simple, elimino la unica combinacion que tiene para generar las nuevas en la pestaña atributos
                    if($previous_article_type == 'simple') {
                        $article_property_ids = ArticleProperty::where('article_id', $art->id)->get()->pluck('id');

                        ArticleProperty::destroy($article_property_ids);
                    }
                }
            } else {
                if($art->type == 'simple') {
                    $article_property = ArticleProperty::where('article_id', $art->id)->first();

                    $article_property->update([
                        'article_id' => $art->id,
                        'price' => $art->price,
                        'stock' => $request->input('stock'),
                        'stock_limit' => $request->input('stock_limit')
                    ]);
                }
            }

            $art->brands()->detach();
            
            $brands = Brand::find( $request->input('brands'));
            
            $art->brands()->attach($brands);

            $art->tags()->detach();
            
            $tags = Tag::find( $request->input('tags'));
            
            $art->tags()->attach($tags);

            $art->categories()->detach();
            $categories = Category::find( json_decode($request->input('categories'), true) );
            $art->categories()->attach($categories);

            DB::commit();

            $request->session()->flash('success', __('articles.update_msg'));

            return ['success' => true];
        } else {
            abort(404);
        }
    }

    public function contact(Request $request, $id) {
        if($request->isMethod('post')) {
            $request->validate([
                'name' => ['required', 'max:100'],
                'email' => ['required', 'max:255', 'email'],
                'message' => ['required', 'max:1000']
            ]);

            $article = Article::find($id);

            if($article != null) {
                $article->user->sendArticleContactNotification($id, 
                    $request->input('name'), $request->input('email'), 
                    $request->input('message'));
            }
        }

        return redirect()->route('admin.articleslist');
    }

    public function autosuggest(Request $request) {
        $validator = Validator::make($request->query(), [
            's' => ['required', 'max: 100', 'regex:/^[A-Z0-9\s]+$/i']
        ]);

        if(!$validator->fails()) {
            $articles = Article::select('title')->where('title', 'like', '%' . $request->query('s') . '%')
                ->limit(6)->orderBy('title', 'desc')->get();

            return json_encode($articles);
        } else {
            return json_encode(['fails' => 'fails']);
        }
    }

    public function updateProperties(Request $request, $id) {
        if($request->ajax()) {
            $article = Article::find($id);

            if($article == null) {
                abort(404);
            }

            $request->validate([
                'article_property_ids' => ['array'],
                'article_property_ids.*' => ['nullable', 'numeric', 'min:1'],
                'combination_ids' => ['array'],
                'combination_ids.*' => ['string', 'min:1'],
                'combination_price' => ['array'],
                'combination_price.*' => ['nullable', 'numeric'],
                'combination_stock' => ['array'],
                'combination_stock.*' => ['nullable', 'numeric', 'min: 0'],
                'combination_stock_limit' => ['array'],
                'combination_stock_limit.*' => ['nullable', 'numeric', 'min: 0']
            ]);

            $article_property_ids = !is_null($request->input('article_property_ids')) ? $request->input('article_property_ids') : [];
            $combinaciones = !is_null($request->input('combination_ids')) ? $request->input('combination_ids') : [];
            $prices = !is_null($request->input('combination_price')) ? $request->input('combination_price') : [];
            $stocks = !is_null($request->input('combination_stock')) ? $request->input('combination_stock') : [];
            $stock_limits = !is_null($request->input('combination_stock_limit')) ? $request->input('combination_stock_limit') : [];

            // valida que los tres arrays tengan la misma cantidad de elementos
            if(count($combinaciones) != count($prices) || count($prices) != count($stocks) || 
                count($combinaciones) != count($stocks)) {
                abort(400);
            }

            DB::beginTransaction();

            $article_properties = ArticleProperty::where('article_id', $article->id)->get();

            foreach($article_properties as $existent_article_property) {
                // si alguno de los ids existentes no estan dentro de los enviados en el formulario => tratar de eliminarlo
                if(!in_array($existent_article_property->id, $article_property_ids)) {
                    if($existent_article_property->transaction_lines()->count() > 0) {
                        throw ValidationException::withMessages([
                            'combination_ids' => __('articles.cannot_delete_art_property')
                        ]);
                    } else {
                        $existent_article_property->delete();
                    }
                }
            }

            for($i = 0;$i < count($combinaciones);$i++) {
                // combinacion ya tiene id => actualizarla
                if($article_property_ids[$i] != null) {
                    $article_property = ArticleProperty::find($article_property_ids[$i]);
                    $article_property->price = $prices[$i];
                    $article_property->stock = $stocks[$i];
                    $article_property->stock_limit = $stock_limits[$i];
                    $article_property->save();
                } else { // combinacion NO tiene id => crearla
                    $ids = explode(',', $combinaciones[$i]);

                    $article_property = new ArticleProperty();
                    $article_property->article_id = $article->id;
                    $article_property->price = $prices[$i];
                    $article_property->stock = $stocks[$i];
                    $article_property->stock_limit = $stock_limits[$i];
                    $article_property->save();

                    $article_property->values()->attach($ids);
                }
            }

            DB::commit();

            $request->session()->flash('success', __('properties.update_success'));

            return ['success' => true];
        } else {
            abort(404);
        }
    }

    public function changeStatus($id){
        if (request()->ajax()) {
            try {                
                $notice = Article::find($id);

                if(empty($notice)){ 
                    abort(404, 'Not found');
                }

                $notice->active = !$notice->active;
                $notice->update();
                $output = ['success' => true, 'msg' => __("general.change_status_ok")];

            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }

            return $output;
        }
    }

    public function updateFeatures(Request $request, $id) {
        if($request->ajax()) {
            $article = Article::find($id);

            if($article == null) {
                abort(404);
            }

            $request->validate([
                'feature_values' => ['array'],
                'feature_values.*' => ['array']
            ]);

            $values = $request->input('feature_values');

            // busca cuales de las feature values ingresadas son IDs (pueden ser texto libre)
            $ids_for_search = [];
            foreach($values as $feature_id => $feature_value_ids) {
                foreach($feature_value_ids as $feature_value_id) {
                    if(preg_match('/^\d+$/', $feature_value_id)) {
                        $ids_for_search[] = $feature_value_id;
                    }
                }
            }
            $founded_feature_values = FeatureValue::whereIn('id', $ids_for_search)->get()->pluck('id')->toArray();

            DB::beginTransaction();

            // elimina feature values de tipo texto de este articulo (luego crea los nuevos valores)
            $feature_values = FeatureValue::whereHas('feature', function($query) {
                $query->where('input_type', 'text');
            })->whereHas('articles', function($articles) use ($id) {
                $articles->where('articles.id', $id);
            })->get();

            foreach($feature_values as $feature_value) {
                $feature_value->delete();
            }

            // limpia todas las features existentes
            $article->features()->detach();

            // asocia features al articulo
            $ids = array();
            if($values != null) {
                foreach($values as $feature_id => $feature_value_ids) {
                    foreach($feature_value_ids as $feature_value_id) {
                        if(in_array($feature_value_id, $founded_feature_values)) {
                            $ids[] = $feature_value_id;
                        } else {
                            $value = new FeatureValue();
                            $value->feature_id = $feature_id;
                            $value->possible_value = $feature_value_id;
                            $value->save();

                            $ids[] = $value->id;
                        }
                    }
                }
            }

            if(count($ids) > 0) {
                $article->features()->attach($ids);
            }
            
            DB::commit();

            $request->session()->flash('success', __('features.update_success'));

            return ['success' => true];
        } else {
            abort(404);
        }
    }

    public function updateFiles(Request $request, $id) {
        if($request->ajax()) {
            $article = Article::find($id);

            if($article == null) {
                abort(404);
            }

            $request->validate([
                'article-files' => ['string'],
            ]);

            $files = json_decode($request->input('article-files'), true);

            DB::beginTransaction();

            // elimina archivos existentes
            if(count($article->files) > 0) {
                foreach($article->files as $file) {
                    $file->delete();
                }
            }

            // crea nuevos archivos
            $created_ids = [];
            foreach($files as $file) {
                $f = File::create([
                    'path' => $file['filename'],
                    'original_name' => $file['originalName']
                ]);
                
                $created_ids[] = $f->id;
            }

            // asocia archivos al articulo
            $article->files()->attach($created_ids);

            DB::commit();

            $request->session()->flash('success', __('articles.files_update_msg'));

            return ['success' => true];
        } else {
            abort(404);
        }
    }

    public function canDeletePropertyValue($property_value_id) {
        $articles = Article::whereHas('properties', function($query) use ($property_value_id) {
            $query->whereHas('values', function($query2) use ($property_value_id) {
                $query2->where('id', $property_value_id);
            });
        })->get();

        return json_encode(['cant_articles' => count($articles)]);
    }

    public function show_child($id) {
        $article = Article::with('properties')->find($id);

        if($article != null) {
            return view('admin.articles.partials.table_child_details', [
                'article' => $article
            ]);
        } else {
            abort(404);
        }
    }


    function checkAtributeValue(Request $request){

        if($request->ajax()) {
            
            $validator = Validator::make($request->all(), [
                //'value' => ['required', 'numeric', 'min:0'],
                'article' => ['required', 'numeric', 'min:0'],
                //'property' => ['required', 'numeric', 'min:0'],                
            ],
            [
                //'value' => ['El atributo :attribute es obligatorio y numerico'],
                'article'=> ['El atributo :attribute es obligatorio y numerico'],
                //'property'=> ['El atributo :attribute es obligatorio y numerico'],
            ]);


            if(!$validator->fails()) {

                $article_id = $request->input('article');
                //$property_id = $request->input('property');
                $properties = $request->input('properties');
                //$value_id = $request->input('value');
                $article = Article::find($article_id);
            
                if(!empty($properties)){
                    $properties_values = PropertyValue::whereHas('articleproperty', function($article_property) use($properties, $article_id) { 
                        foreach($properties as $property) {
                            $article_property->where('article_id', $article_id);
                            if(config('config.SHOW_PRICING')){
                                $article_property->where('price', '>', 0);
                            }
                            $article_property->where('stock', '>', 0)->whereHas('values', function($property_values) use($property) { 
                            $property_values->where('property_value_id', $property); 
                        });
                        }
                    });       
                   
                    $properties_values = $properties_values->get();
                } else{
                    $properties_values = PropertyValue::whereHas('articleproperty', function($query) use ($article_id) { 
                        $query->where('article_id', $article_id)->where('stock', '>', 0); 
                        if(config('config.SHOW_PRICING')){
                                $query->where('price', '>', 0);
                        }
                    });

                    
                    $properties_values = $properties_values->get();
                }

                //Busco un producto que cumpla con todos los atributos
                $article_property = null;
                if(!empty($properties)) {
                    $article_properties = ArticleProperty::where('stock', '>', 0)->where('article_id', $article_id);
                    if(config('config.SHOW_PRICING')){
                                $article_properties->where('price', '>', 0);
                    }
                    $article_properties = $article_properties->get();                    
                    foreach($article_properties as $art){                        
                        $band = true;
                        foreach ($art->values as $val) {                            
                            if(!in_array($val->pivot->property_value_id, $properties)){  
                                $band = false;
                                break;                   
                            }
                        }
                        if($band && count($art->values) == count($properties)){
                            $article_property = $art;
                            $article_property->price_formatted = $article_property->getPriceFormatted();
                            break;
                        }
                    }
                } else {
                    if($article->type == 'simple'){
                        $article_property = ArticleProperty::where('stock', '>', 0)->where('article_id', $article_id);
                        if(config('config.SHOW_PRICING')){
                            $article_property->where('price', '>', 0);
                        }
                        $article_property = $article_property->first();
                    }
                }
                
                return $output = ['success' => true, 'msg' => __("general.ok"), 'data'=>$properties_values, 'article_property' => $article_property];            
            } else{                
                return $output = ['success' => false, 'msg' => $validator->message, 'fails'=>$validator];
            }
            
            
        }

        return $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
        
    }

    /**
     * Mass deletes products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(Request $request)
    {
        if (!auth()->user()->can('article.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {            

            if (!empty($request->input('selected_rows'))) {                

                $selected_rows = explode(',', $request->input('selected_rows'));

                $articles = Article::whereIn('id', $selected_rows)                                    
                                    ->get();
                $deletable_products = [];

                DB::beginTransaction();

                foreach ($articles as $article) {
                    $article->delete();                    
                }

                DB::commit();
            }
            
            return $output = ['success' => 1,
                        'msg' => __('general.deleted_success')
                    ];
         
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            return $output = ['success' => 0,
                            'msg' => __("general.something_went_wrong")
                        ];
        }

        
    }

}