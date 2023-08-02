<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Subsidiary;
use App\Models\City;

class SubsidiariesController extends Controller
{
		private $subsidiariesRepository;
		private $citiesRepository;

		public function __construct(Subsidiary $repo, City $cityRepo) {
			$this->middleware('permission:subsidiary.list|subsidiary.create|subsidiary.edit|subsidiary.delete', ['only' => ['index']]);
	    $this->middleware('permission:subsidiary.create', ['only' => ['create', 'store']]);
	    $this->middleware('permission:subsidiary.edit', ['only' => ['edit', 'update']]);
	    $this->middleware('permission:subsidiary.delete', ['only' => ['destroy']]);

			$this->subsidiariesRepository = $repo;
			$this->citiesRepository = $cityRepo;
		}

		public function index(Request $request) {
			if($request->ajax()) {
				$subsidiaries = $this->subsidiariesRepository->orderBy('name')->get();

				return DataTables::of($subsidiaries)
					->addColumn('action', function($row) {
						$html = '';

						if (auth()->user()->can("subsidiary.edit")) {
                $html .= '<a class="btn btn-sm btn-primary" href="' . action('SubsidiariesController@edit', [$row->id]) . '"><i class="fa fa-pencil"></i></a>';
            }

						if (auth()->user()->can("subsidiary.delete")) {
                $html .= '<button class="btn btn-sm btn-danger delete" data-href="' . action('SubsidiariesController@destroy', [$row->id]) . '"><i class="fa fa-trash"></i></button>';
            }

						return $html;
					})
					->editColumn('address', function($row) {
						return $row->full_address . ', ' . $row->postal_code;
					})
					->editColumn('city', function($row) {
						return $row->city->name;
					})
					->make(true);
			}

			return view('admin.subsidiaries.index');
		}

		public function create() {
			$cities = $this->citiesRepository->orderBy('name')->get();

			return view('admin.subsidiaries.create', compact('cities'));
		}

		public function store(Request $request) {
			if($request->ajax()) {
				$request->validate([
					'subsidiary' => ['required', 'array'],
					'subsidiary.name' => ['required', 'string', 'max:255'],
					'subsidiary.city_id' => ['required', 'numeric', 'exists:cities,id'],
					'subsidiary.postal_code' => ['required', 'string'],
					'subsidiary.street' => ['required', 'string', 'max:255'],
					'subsidiary.street_number' => ['required', 'numeric'],
					'subsidiary.floor' => ['nullable', 'string', 'max:100'],
					'subsidiary.apartment' => ['nullable', 'string', 'max:100']
				]);
 
				$subs = $this->subsidiariesRepository->create($request->input('subsidiary'));

				return ['success' => true, 'data' => $subs, 'msg' => __('subsidiaries.create_success')];
			} else {
				abort(404);
			}
		}

		public function edit($id) {
			$subsidiary = $this->subsidiariesRepository->find($id);

			if($subsidiary != null) {
				$cities = $this->citiesRepository->orderBy('name')->get();

				return view('admin.subsidiaries.edit', compact('subsidiary', 'cities'));
			} else {
				abort(404);
			}
		}

		public function update(Request $request, $id) {
			if($request->ajax()) {
				$subsidiary = $this->subsidiariesRepository->find($id);

				if($subsidiary == null) {
					abort(404);
				}

				$request->validate([
					'subsidiary' => ['required', 'array'],
					'subsidiary.name' => ['required', 'string', 'max:255'],
					'subsidiary.city_id' => ['required', 'numeric', 'exists:cities,id'],
					'subsidiary.postal_code' => ['required', 'string'],
					'subsidiary.street' => ['required', 'string', 'max:255'],
					'subsidiary.street_number' => ['required', 'numeric'],
					'subsidiary.floor' => ['nullable', 'string', 'max:100'],
					'subsidiary.apartment' => ['nullable', 'string', 'max:100']
				]);

				$subsidiary->update($request->input('subsidiary'));

				return ['success' => true, 'msg' => __('subsidiaries.update_success')];
			} else {
				abort(404);
			}
		}

		public function destroy($id) {
			$subs = $this->subsidiariesRepository->find($id);

			if($subs != null) {
				$subs->delete();

				return ['success' => true, 'msg' => __('subsidiaries.destroy_success')];
			} else {
				abort(404);
			}
		}
}
