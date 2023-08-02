<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Category;
use App\Models\FeatureValue;
use App\Models\ArticleProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Yajra\DataTables\Facades\DataTables;

class FeaturesController extends Controller
{
	
	public function __construct() {
		$this->middleware('permission:feature.list|feature.create|feature.edit|feature.delete', ['only' => ['index']]);
    $this->middleware('permission:feature.create', ['only' => ['create', 'store']]);
    $this->middleware('permission:feature.edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:feature.delete', ['only' => ['destroy']]);
	}

	public function index() {

		if(request()->ajax()) {
      $datas = Feature::orderBy('name')->get();

      return DataTables::of($datas)
          ->addColumn(
              'action',
               function ($row)  {
                   $html ="";     

                  if (auth()->user()->can("feature.edit")) {
                          $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updatefeature', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                  }

                  if (auth()->user()->can("feature.delete")) {
                          $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletefeature', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                  }
                  
                  return $html;
               }                   
          )         
          ->rawColumns(['name', 'action'])
          ->make(true);                   
    }

		return view('admin.features.list');
	}

	public function create(Request $request) {
		return view('admin.features.form');
	}

	public function store(Request $request) {
		if($request->ajax()) {
			$request->validate([
				'name' => ['required', 'string', "max:255", 'unique:features,name'],
				'input_type' => ['required', 'string', 'max:50'],
				'value' => ['array', 'min:1'],
				'value.*' => ['string', 'max:255']
			]);

			// si el tipo de campo es select, radio o checkbox se deben cargar valores posibles
			if($request->input('input_type') != 'text') {
				$request->validate([
					'value' => ['required'],
				], [
					'value.required' => __('features.no_values_error')
				]);
			}

			DB::beginTransaction();

			$feature = new Feature();
			$feature->name = $request->input('name');
			$feature->input_type = $request->input('input_type');
			$feature->save();

			if($request->input('value') != null) {
				foreach($request->input('value') as $value) {
						$attrvalue = new FeatureValue();
						$attrvalue->possible_value = $value;
						$attrvalue->feature_id = $feature->id;

					try {
						$attrvalue->save();
					} catch(QueryException $e) {
						abort(422, __('features.cannot_repeat_value'));
					}
				}
			}

			DB::commit();

			$request->session()->flash('success', __('features.store_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function edit(Request $request, $id) {
		$feature = Feature::find($id);

		if($feature != null) {
			return view('admin.features.form', compact('feature'));
		} else {
			abort(404);
		}
	}

	public function update(Request $request, $id) {
		if($request->ajax()) {
			$feature = Feature::find($id);

			if($feature == null) {
				abort(404);
			}

			if($request->isMethod('post')) {
				$request->validate([
					'name' => ['required', 'string', "max:255"],
					'input_type' => ['required', 'string', 'max:50'],
					'value' => ['array', 'min:1'],
					'value.*' => ['string', 'max:255']
				]);

				if(trim($feature->name) != trim($request->input('name'))) {
					$request->validate([
						'name' => ['unique:features,name']
					]);
				}

				// si el tipo de campo es select, radio o checkbox se deben cargar valores posibles
				if($request->input('input_type') != 'text') {
					$request->validate([
						'value' => ['required'],
					], [
						'value.required' => __('features.no_values_error')
					]);
				}

				DB::beginTransaction();

				$registeredCount = 0;
				if($request->input('value_id') != null) {
					foreach($request->input('value_id') as $value_id) {
						if($value_id != null) {
							$registeredCount++;
						}
					}
				}

				// elimina possible_values quitados (si es posible)
				if($registeredCount < count($feature->values)) {
					foreach($feature->values as $currentValue) {
						$founded = false;

						if($request->input('value_id') != null) {
							foreach($request->input('value_id') as $value_id) {
								if($currentValue->id == $value_id) {
									$founded = true;
									break;
								}
							}
						}

						// si no lo encuentra, lo trata de eliminar
						if(!$founded) {
							// si encuentra articulos existentes con el valor eliminado, error de validacion
							if(count($currentValue->articles) > 0) {
								abort(422, __('features.cannot_delete_value', ['possible_value' => $currentValue->possible_value]));
							} else {
								$currentValue->delete();
							}
						}
					}
				}

				if($request->input('value_id') != null) {
					for($i = 0; $i < count($request->input('value_id')); $i++) {
						$value_id = $request->input('value_id')[$i];
						$value = $request->input('value')[$i];

						if($value_id != null) {
							// recorre valores actuales para actualizar los existentes
							foreach($feature->values as $currentValue) {
								if($currentValue->id == $value_id) {
									$currentValue->possible_value = $value;
									$currentValue->save();

									break;
								}
							}
						} else {
							try {
								// si se agrega un valor nuevo, lo crea
								$attrvalue = new FeatureValue();
								$attrvalue->possible_value = $value;
								$attrvalue->feature_id = $feature->id;
								$attrvalue->save();
							} catch(QueryException $e) {
								$error = ValidationException::withMessages([
									'value' => __('features.cannot_repeat_value')
								]);
								throw $error;
							}
						}
					}
				}

				$feature->name = $request->input('name');
				$feature->input_type = $request->input('input_type');
				$feature->save();

				DB::commit();

				$request->session()->flash('success', __('features.update_msg'));

				return ['success' => true];
			} else {
				abort(404);
			}
		}
	}

	public function destroy($id) {
        if (request()->ajax()) {
            try {     
                $tag = Feature::find($id);
				$tag->delete();

				$output = ['success' => true, 'msg' => __("general.delete_ok")];                                           
            
            } catch(QueryException $e) {
				$output = ['success' => false, 'msg' => __('features.cannot_delete_value', ['possible_value' => $tag->name])];
			}
             catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }
            return $output;            
        }

    }

	public function add_feature($id) {
		$feature = Feature::with('values')->find($id);

		if($feature != null) {
			return view('admin.articles.partials.feature_line', [
				'feature' => $feature,
				'selected_value' => null
			]);
		} else {
			abort(404);
		}
	}
}
