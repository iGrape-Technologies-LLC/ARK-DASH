<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\User;
use App\Models\CsvData;
use App\Models\Article;
use App\Models\Feature;
use App\Models\FeatureValue;
use App\Models\Currency;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Tag;
use App\Models\ArticleProperty;
use App\Imports\ArticlesImport;
use App\Utils\UtilGeneral;
use App\Utils\UtilFile;
use App\Http\Requests\CsvImportRequest;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Debugbar\Facade as Debugbar;

use Validator;


class ImportController extends Controller
{ 
    protected $UtilGeneral;
    protected $UtilFile;
    protected $db_fields =  ['none','title', 'description', 'price', 'stock', 'stock_limit', 'currency', 'featured', 'categories', 'brands', 'tags', 'sku', 'priority', 'weight', 'size_x', 'size_y', 'size_z'];
    public function __construct(UtilGeneral $UtilGeneral, UtilFile $UtilFile)
    {
        $this->UtilGeneral = $UtilGeneral;  
        $this->UtilFile = $UtilFile;                
    }

   
   public function importProducts(){
    	return view('admin.import.product');
   }

   public function importImages(){
        return view('admin.import.image');
   }

   public function importFiles(){
        return view('admin.import.files');
   }

   public function storeImages(Request $request) {
        if($request->ajax()) {            

            $rules = [
                    'import-photos' => 'required|string|min:2'
                ];           
            
            DB::beginTransaction();    

            $photoError = $this->UtilFile->updatePhotosBulk($request->input('import-photos'));

            if($photoError) {
                throw ValidationException::withMessages([
                    'import-photos' => __('import.photo_required')
                ]);
            }       

            DB::commit();
            
            $request->session()->flash('success', __('import.store_msg'));

            return ['success' => true];
        } else {
            abort(404);
        }
    }

    public function storeFiles(Request $request) {
        if($request->ajax()) {            

            $rules = [
                    'import-files' => 'required|string|min:2'
                ];           
            
            DB::beginTransaction();    

            $photoError = $this->UtilFile->updateFilesBulk($request->input('import-files'));

            if($photoError) {
                throw ValidationException::withMessages([
                    'import-photos' => __('import.file_required')
                ]);
            }       

            DB::commit();
            
            $request->session()->flash('success', __('import.store_msg'));

            return ['success' => true];
        } else {
            abort(404);
        }
    }

   public function parseImport(CsvImportRequest $request)
    {
    	$name = $request->csv_file->store('', 'public');

        $validator = Validator::make($request->query(), [
            'csv_file' => ['required|max:50000|mimes:xlsx,application/excel,application/vnd.ms-excel, application/vnd.msexcel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        ]);

        if($validator->fails()) {
            return view('admin.import.product')->withErrors(['errors' => 'Verifique que su archivo de excel no este protegido']); 
        }
        $isRar = explode('.', $name);
        

        if(end($isRar) == 'rar' || end($isRar) ==  'zip' || end($isRar) ==  'bin'){
            return view('admin.import.product')->withErrors(['errors' => 'Verifique que su archivo de excel no este protegido ni vacio']); 
        }

        $data = Excel::toArray(new ArticlesImport, public_path('storage/'.$name));
       	$data_clean = [];
       	$csv_header_fields = [];

        if (count($data[0]) > 0) {
        	$csv_header_fields = $data[0][0];
        	if ($request->has('header')) {
        		$data = array_slice($data[0], 1);
        	} else{
        		$cont = 1;
        		for ($i=0; $i< count($csv_header_fields); $i++) {
        			$csv_header_fields[$i] = "Columna " . $cont;  
        			$cont++;      			
        		}
        		$data = $data[0];
        	}

        	foreach ($data as $row) {
        		array_push($data_clean, ($row));
        	}

            $csv_data = $data_clean;

            $csv_data_file = CsvData::create([
                'csv_filename' => $name,
                'csv_header' => $request->has('header'),
                'csv_data' => json_encode($data)
            ]);
        } else {
            return redirect()->back();
        }

        //$db_fields = Schema::getColumnListing('articles');
        $db_fields = $this->db_fields;

        $features = Feature::pluck('name');

        

        foreach ($features as $key => $feature) {
            array_push($db_fields, __('import.feature') . ':' . $feature);
        }

        return view('admin.import.import_fields', compact( 'csv_header_fields', 'csv_data', 'csv_data_file', 'db_fields'));

    }

    public function processImport(Request $request)
    {
        try {
            $data = CsvData::find($request->csv_data_file_id);
            $csv_data = json_decode($data->csv_data, true);
            $db_fields = $this->db_fields;

            if(count($csv_data) == 0){
                $output = ['success' => 0, 'msg' => __('import.empty_file')]; return $output;
            }

            if($this->has_duplicates($request->fields)){
                $output = ['success' => 0, 'msg' =>  __('import.repeated_column')]; return $output;
            }

            DB::beginTransaction();

            //Recorro cada ROW del archivo
            foreach ($csv_data as $nro => $row) {
                $row_no = $nro + 1;
                $features = [];
                $features_ids = array();
                //Creo el Articulo
                $article=  new \stdClass();
                //Busco en todos los SELECTS, a ver cual lo tiene. $field= nombre en BD. $valueSelect= nombre del select         
                foreach ($request->fields as $key => $valueSelect) {                         
                        //Interrogo a ver si fue una columna que no fue asignada a nada
                        if($valueSelect !== 'none') {
                            //Interrogo a ver si es caracteristica o campo normal
                            if(strpos($valueSelect, __('import.feature') . ':') === false ){                                
                                //Es un campo normal, sigo
                                //Busco por cada columna de la BD      
                                foreach ($db_fields as $index => $fieldDB) {                                           
                                    //Si el nombre del Select coincide con el de BD, entonces lo asigno                                    
                                    if($valueSelect == $fieldDB && $valueSelect != 'none'){
                                        $article->$fieldDB = $row[$key];
                                        break;
                                    }
                                }            
                            } else{                                
                                //Quiere decir que es una Feature (caracteristica)
                                $valueSelect = str_replace(__('import.feature') . ':', "",$valueSelect);
                                //Aca tengo que hacer un foreach tuti
                                //Busco a ver si existe la Caracteristica
                                $feature = Feature::where('name', $valueSelect)->first();
                                if(empty($feature)){
                                    $output = ['success' => 0, 'msg' => __('import.feature_no_exist', ['value'=> $valueSelect, 'row'=>$row_no]) ]; return $output;
                                }                   
                                //Busco a ver si existe el valor de esa caracteristica
                                $nameFeature = explode("|", $row[$key]);
                                foreach ($nameFeature as $nF) {
                                    $featureValue = FeatureValue::where('possible_value', $nF)->whereHas('feature', function($query) use ($feature){
                                        $query->where('features.id', $feature->id);
                                    })->first();
                                    if(empty($featureValue)){
                                        //Si no existe, pregunto si es texto libre o select, sino ejecuto error
                                        if($feature->input_type == 'select'){
                                            $output = ['success' => 0, 'msg' => __('import.feature_no_concord', ['value'=> $valueSelect, 'row'=>$row_no]) ]; return $output;
                                        } else if($feature->input_type == 'text'){
                                            $ff = new FeatureValue();
                                            $ff->feature_id = $feature->id;
                                            $ff->possible_value = $nF; 
                                            $ff->save();

                                            $ftre=  new \stdClass();
                                            $ftre->id = $ff->id;                                       
                                        }
                                    } else{
                                        $ftre=  new \stdClass();
                                        $ftre->id = $featureValue->id;                                   
                                    }                                                        
                                    $features_ids[] = $ftre->id;     
                                }                 
                            }
                        }
                }                                                     

                //Check title (required)
                if(empty($article->title)){
                    $output = ['success' => 0, 'msg' => __('import.not_valid_value', ['value'=> __('articles.fields.title'), 'row'=>$row_no])]; return $output;
                } 

                if(config('config.SHIPPING_REQUIRED')){
                    if(empty($article->weight) || empty($article->size_x ) || empty($article->size_y)  || empty($article->size_z )){
                        $output = ['success' => 0, 'msg' => __('import.not_valid_value', ['value'=> __('articles.measures'), 'row'=>$row_no])]; return $output;
                    } 
                    if(($article->size_x - intval($article->size_x)) > 0 || ($article->size_y - intval($article->size_y)) > 0 || 
        ($article->size_z - intval($article->size_z)) > 0 || ($article->weight - intval($article->weight)) > 0) {
                        $output = ['success' => 0, 'msg' => __('import.not_valid_value_decimal', ['value'=> __('articles.measures'), 'row'=>$row_no])]; return $output;
                    }
                }

                //Check currency (required)            
                if(empty($article->currency)){
                    $article->currency =  Currency::first();
                    //$output = ['success' => 0, 'msg' => __('import.not_valid_value', ['value'=> __('articles.fields.currency'), 'row'=>$row_no])]; return $output;
                } else{                    
                    $article->currency =  Currency::where('code',  $article->currency)->first();    
                    if(empty($article->currency)){
                        $output = ['success' => 0, 'msg' => __('import.not_exist_value', ['value'=> __('articles.fields.currency'), 'row'=>$row_no])]; return $output;
                    }
                }

                $categories_ids = [];
                if(!empty($article->categories)){
                    $categories = explode("|", $article->categories);
                    foreach ($categories as $cat) {
                        
                        $category = Category::where('name', $cat)->first();
                        
                        if(!empty($category)){
                            $categories_ids[] = $category->id;
                        }
                    }
                }

                $brands_ids = [];
                if(!empty($article->brands)){
                    $brands = explode("|", $article->brands);
                    foreach ($brands as $br) {
                        
                        $brand = Brand::where('name', $br)->first();
                        
                        if(!empty($brand)){
                            $brands_ids[] = $brand->id;
                        }
                    }
                }

                $tags_ids = [];
                if(!empty($article->tags)){
                    $tags = explode("|", $article->tags);
                    foreach ($tags as $tg) {
                        
                        $tag = Tag::where('name', $tg)->first();
                        
                        if(!empty($tag)){
                            $tags_ids[] = $tag->id;
                        }
                    }
                }

                $art = new Article();
                $art->title = $article->title;
                $art->description = empty($article->description) ? null : $article->description;
                $art->price = empty($article->price) ? null : $article->price;   
                $art->featured = empty($article->featured) ? 0 : $article->featured ;             
                $art->sku = empty($article->sku) ? null : $article->sku;

                $art->weight = empty($article->weight) ? null : $article->weight;
                $art->size_x = empty($article->size_x) ? null : $article->size_x;
                $art->size_y = empty($article->size_y) ? null : $article->size_y;
                $art->size_z = empty($article->size_z) ? null : $article->size_z;


                $art->priority = empty($article->priority) ? 0 : $article->priority;
                $art->currency_id = $article->currency->id;
                $art->user_id = Auth::user()->id;

                            
                $art->save();

                ArticleProperty::create([
                    'article_id' => $art->id,
                    'price' => empty($article->price) ? null : $article->price,
                    'stock' => empty($article->stock) ? null : $article->stock,
                    'stock_limit' => empty($article->stock_limit) ? null : $article->stock_limit,
                ]);

                //Agrego las features si es que las hubo
                if(count($features_ids) > 0) {
                    $art->features()->attach($features_ids);
                }   

                //Agrego las marcas si es que las hubo
                if(count($brands_ids) > 0) {
                    $art->brands()->attach($brands_ids);
                }     

                //Agrego las tags si es que las hubo
                if(count($tags_ids) > 0) {
                    $art->tags()->attach($tags_ids);
                }             

                //Agrego las categorieas si es que las hubo
                if(count($categories_ids) > 0) {
                    $art->categories()->attach($categories_ids);
                }                     
            }        
            DB::commit();
            $request->session()->flash('success', __('import.success_import'));
            $output = ['success' => true, 'msg' => __("import.success_import")];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
        }

        return $output;

        //return view('admin.products.list')->with('success-msg', 'Productos cargado con exito');
    }

    function has_duplicates($array) {
        $array = array_diff($array, ["none"]);
        return count($array) !== count(array_unique($array));
    }


   
}