<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Category;
use App\Models\PropertyValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PropertiesController extends Controller
{

	public function __construct() {
		$this->middleware('permission:property.list|property.create|property.edit|property.delete', ['only' => ['index']]);
    $this->middleware('permission:property.create', ['only' => ['create', 'store']]);
    $this->middleware('permission:property.edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:property.delete', ['only' => ['destroy']]);
	}

	public function findValues($property_id) {
		$values = PropertyValue::where('property_id', $property_id)
								->orderBy('possible_value', 'desc')
								->get();

		return json_encode($values);
	}

	public function findByCategory($id_category) {
		$category = Category::find($id_category);

		$properties = $category->properties->sortBy('sort')->values()->all();

		foreach($properties as $property) {
			$property['values'] = PropertyValue::where('property_id', $property->id)
								->orderBy('possible_value', 'desc')
								->get();
		}

		return json_encode($properties);
	}

	public function index() {

		if(request()->ajax()) {
        $datas = Property::orderBy('name')->get();

        return DataTables::of($datas)
            ->addColumn(
                'action',
                 function ($row)  {
                     $html ="";     

                    if (auth()->user()->can("property.edit")) {
                            $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updateattribute', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                    }

                    /*if (auth()->user()->can("category.delete")) {
                            $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletefeature', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                    }*/
                    
                    return $html;
                 }                   
            )         
            ->rawColumns(['name', 'action'])
            ->make(true);
    }

		return view('admin.properties.list');
	}

	public function create(Request $request) {
		return view('admin.properties.form');
	}

	public function store(Request $request) {
		if($request->ajax()) {
			$request->validate([
				'name' => ['required', 'string', "max:255", 'unique:properties,name'],
				'value' => ['required', 'array', 'min:1'],
				'value.*' => ['string', 'max:255', 'min:1'],
				'value_order' => ['array'],
				'value_order.*' => ['numeric']
			]);

			DB::beginTransaction();

			$property = new Property();
			$property->name = $request->input('name');
			$property->save();

			if($request->input('value') != null) {
				$i = 0;
				foreach($request->input('value') as $value) {
					$attrvalue = new PropertyValue();
					$attrvalue->possible_value = $value;
					$attrvalue->property_id = $property->id;
					$attrvalue->order = $request->value_order[$i];
					$attrvalue->save();

					$i++;
				}
			}

			DB::commit();

			$request->session()->flash('success', __('properties.store_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function edit(Request $request, $id) {
		$property = Property::find($id);

		if($property != null) {
			return view('admin.properties.form', [
				'attribute' => $property
			]);
		} else {
			abort(404);
		}
	}

	public function update(Request $request, $id) {
		if($request->ajax()) {
			$property = Property::find($id);

			if($property == null) {
				abort(404);
			}

			if($request->isMethod('post')) {
				$request->validate([
					'name' => ['required', 'string', "max:255"],
					'value' => ['required', 'array', 'min:1'],
					'value.*' => ['string', 'max:255', 'min:1'],
					'value_order' => ['array'],
					'value_order.*' => ['numeric']
				]);

				// Si el nombre cambia, validar que no sea uno ya elegido
				if(trim($property->name) != trim($request->input('name'))) {
					$request->validate([
						'name' => 'unique:properties,name'
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
				if($registeredCount < count($property->values)) {
					foreach($property->values as $currentValue) {
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
							if(count($currentValue->articleproperty) > 0) {
								$error = ValidationException::withMessages([
									'value' => __('properties.cant_delete', ['value'=> $currentValue->possible_value])
									
								]);
								throw $error;
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
						$value_order = $request->value_order[$i];

						if($value_id != null) {
							// recorre valores actuales para actualizar los existentes
							foreach($property->values as $currentValue) {
								if($currentValue->id == $value_id) {
									$currentValue->possible_value = $value;
									$currentValue->order = $value_order;
									$currentValue->save();

									break;
								}
							}
						} else {
							// si se agrega un valor nuevo, lo crea
							$attrvalue = new PropertyValue();
							$attrvalue->possible_value = $value;
							$attrvalue->property_id = $property->id;
							$attrvalue->order = $value_order;
							$attrvalue->save();
						}
					}
				}

				$property->name = $request->input('name');
				$property->save();

				DB::commit();

				$request->session()->flash('success', __('properties.update_msg'));

				return ['success' => true];
			} else {
				abort(404);
			}
		}
	}

	public function destroy($id) {
		$property = Property::find($id);
		if($property != null) {
			$property->delete();

			return redirect()->route('admin.propertieslist');
		} else {
			abort(404);
		}
	}
}
