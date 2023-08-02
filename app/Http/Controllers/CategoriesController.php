<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Property;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CategoriesController extends Controller
{
	private $categoriesRepository;

	public function __construct(Category $category) {
		$this->categoriesRepository = $category;

		$this->middleware('permission:category.list|category.create|category.edit|category.delete', ['only' => ['index']]);
    $this->middleware('permission:category.create', ['only' => ['create', 'store']]);
    $this->middleware('permission:category.edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:category.delete', ['only' => ['destroy']]);
	}

	public function index() {
		if(request()->ajax()) {
      	$datas = $this->categoriesRepository
					->orderBy('parent_id')
					->orderBy('name');

        return DataTables::of($datas)
                ->addColumn(
                    'action',
                     function ($row)  {
                         $html ="";

                        if (auth()->user()->can("category.edit")) {
                                $html .= '<a class="btn btn-sm btn-primary" href="' . route('admin.updatecategory', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
                        }

                         if (auth()->user()->can("category.delete")) {
                                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . route('admin.deletecategory', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
                        }

                        return $html;
                     }
                )
                ->filterColumn('category', function($query, $value) {
              		$query->whereHas('parent', function($parent) use($value) {
              			$parent->where('name', 'like', '%' . $value . '%');
              		});
                })
                ->editColumn(
                    'category', function($row) {
                         if(!empty($row->parent)){
                         	return $row->parent->name;
                         } else{
                         	return "-";
                         }


                      }
                 )
                ->rawColumns(['action'])
                ->make(true);

    }

		return view('admin.categories.list');
	}

	public function create(Request $request) {
		$categories = Category::orderBy('name')->get();

		return view('admin.categories.form', compact('categories'));
	}

	public function store(Request $request) {
		if($request->ajax()) {
			$request->validate([
				'name' => ['required', "max:255"],
				'parent_id' => ['nullable', 'exists:categories,id'],
				'photo' => ['nullable', 'string']
			]);

			$category = $this->categoriesRepository->create([
				'name' => $request->input('name'),
				'parent_id' => $request->input('parent_id'),
				'priority' => $request->input('priority'),
				'photo' => $request->input('photo')
			]);

			try {
					// crea thumbnail de la foto de la categoria
          if(!file_exists(public_path() . '/storage/thumb_' . $request->photo)) {
              $im = new \Imagick(public_path() . '/storage/' . $request->photo);
              // $im->setImageCompressionQuality(95);
              $im->thumbnailImage(300,300,true);
              $im->writeImage(public_path() . '/storage/thumb_' . $request->photo);
              $im->destroy();
          }
      } catch(\Exception $e) {
          $error = true;
      }

			$request->session()->flash('success', __('categories.store_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function edit(Request $request, $id) {
		$category = $this->categoriesRepository->find($id);
		$categories = Category::orderBy('name')->get();

		if($category != null) {
			return view('admin.categories.form', [
				'category' => $category,
				'categories' => $categories
			]);
		} else {
			abort(404);
		}
	}

	public function update(Request $request, $id) {
		if($request->ajax()) {
			$category = $this->categoriesRepository->find($id);

			$request->validate([
				'name' => ['required', "max:255"],
				'parent_id' => ['nullable', 'exists:categories,id'],
				'photo' => ['nullable', 'string']
			]);

			$category->update([
				'name' => $request->input('name'),
				'parent_id' => $request->input('parent_id'),
				'priority' => $request->input('priority'),
				'photo' => $request->input('photo')
			]);

			try {
					// crea thumbnail de la foto de la categoria
          $im = new \Imagick(public_path() . '/storage/' . $request->photo);
          // $im->setImageCompressionQuality(95);
          $im->thumbnailImage(300,300,true);
          $im->writeImage(public_path() . '/storage/thumb_' . $request->photo);
          $im->destroy();
      } catch(\Exception $e) {
          $error = true;
      }

			$request->session()->flash('success', __('categories.update_msg'));

			return ['success' => true];
		} else {
			abort(404);
		}
	}

	public function destroy($id) {
        if (request()->ajax()) {
            try {
                $category = $this->categoriesRepository->find($id);
				$category->delete();

				$output = ['success' => true, 'msg' => __("general.delete_ok")];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false, 'msg' => __("general.something_went_wrong")];
            }
            return $output;
        }

    }


	/**
	* Obtiene vista de arbol de categorias
	*
	* @param integer $category_id: categoria que actualmente esta seleccionada
	* @return json
	*/
	public function get_treeview($category_id = null) {
		$categories = $this->categoriesRepository->where('parent_id', null)->get();

		$formattedCategories = $this->format_categories($categories, $category_id);

		return json_encode($formattedCategories);
	}

	// metodo recursivo que se llama a si mismo en caso en que la categoria tenga subcategorias
	private function format_categories($subcategories, $selected_category = null) {
		$formattedSubcategories = [];

		foreach($subcategories as $category) {
			$formattedCategory = [];
			$formattedCategory['href'] = '#' . $category->id;
			$formattedCategory['text'] = $category->name;
			$formattedCategory['tags'] = [$category->id];

			if($category->id == $selected_category) {
				$formattedCategory['state'] = [
					'selected' => true
				];
			}

			if(count($category->subcategories) > 0) {
				$formattedCategory['nodes'] = $this->format_categories($category->subcategories, $selected_category);
			}

			$formattedSubcategories[] = $formattedCategory;
		}

		return $formattedSubcategories;
	}

	public function frontlist(Request $request, $slugs=null) {
		$category_id = null;
		if($request->query('category_id') != null) {
			try {
					$category_id = intval($request->query('category_id'));
			} catch(\Exception $e) {
				\Log::error($e);
			}
		}

		$last_slug = null;
		if(!is_null($slugs)) {
        $slugsarr = explode('/', $slugs);

        if(count($slugsarr) > 0) {
        	$last_slug = $slugsarr[count($slugsarr)-1];
        }
    }

		if(!is_null($last_slug)) {
			$categories = $this->categoriesRepository
												->whereHas('parent', function($query) use($last_slug) {
													$query->where('slug', $last_slug);
												})
												->orderBy('priority');

		} else {
			$categories = $this->categoriesRepository
												->whereNull('parent_id')
												->orderBy('priority');
		}


		$categories = $categories->paginate(9);

		// si no encuentra ninguna subcategoria, busca en la lista de productos con esa categoria
		if(count($categories) == 0) {
			$category = $this->categoriesRepository->where('slug', $last_slug)->first();
			return redirect()->route('front.articleslist', ['slugs' => $category->breadcrumbs]);
		}

		return view('front.categories', compact('categories'));
	}
}
