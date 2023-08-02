<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CsvImportRequest;
use App\Imports\IdsImport;
use App\Models\State;
use App\Models\PropertyValue;
use App\Models\Article;
use App\Models\Transaction;
use App\Models\Newsletter;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller {

	public function home() {
		return redirect()->route('login');
	}

	public function indexAdmin() {
    	return view('admin.import.ids');
	}

	public function import(CsvImportRequest $request)
	{
		$name = $request->csv_file->store('', 'public');

		$validator = Validator::make($request->query(), [
			'csv_file' => ['required|max:50000|mimes:xlsx,application/excel,application/vnd.ms-excel, application/vnd.msexcel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
		]);

		if($validator->fails()) {
			return view('admin.import.product')->withErrors(['errors' => 'Please, verify your excel file is not protected.']); 
		}
		$isRar = explode('.', $name);
		

		if(end($isRar) == 'rar' || end($isRar) ==  'zip' || end($isRar) ==  'bin'){
				return view('admin.import.product')->withErrors(['errors' => 'Please, verify your excel file is not protected or empty']); 
		}

		$data = Excel::toArray(new IdsImport, public_path('storage/'.$name));
		$data_clean = [];
		
		if (count($data[0]) > 0) {
			$csv_header_fields = $data[0][0];

			$data = array_slice($data[0], 1);

			foreach ($data as $row) {
				array_push($data_clean, ($row));
			}


			$dnero_endpoint = config('config.DNERO_ENDPOINT') . 'GiftCoin/drop';
			$dnero_lat = $request->lat;
			$dnero_lng = $request->lng;

			$delivered = [];
			$errors = [];
			foreach ($data_clean as $data) {
				$client = new Client();
				try {			
					// for ($i=1; $i <= $data[2]; $i++) { 
						
					// 	$response = $client->post($dnero_endpoint, [
					// 		'json' => [
					// 			"Latitude" => $dnero_lat + random_int(-5, 5) / 10000,
					// 			"Longitude" => $dnero_lng + random_int(-5, 5) / 10000,
					// 			"Accurency" => 20,
					// 			"IsMock" => true,
					// 			"Expiration" => Carbon::parse()->addMonth(1)->format('Y-m-d\TH:i:s.u\Z'),
					// 			"Message" => "Superbowl",
					// 			"TransactionMeanId" => 1,
					// 			"Phone" => $data[0],
					// 			"Amount" => $data[1] / $data[2],
					// 		],
					// 		'headers' => [
					// 			'Content-Type' => "application/json",
					// 			'Authorization' => "Bearer " . auth()->user()->token
					// 		]
					// 	]);

					// 	\Log::info($response->getBody()->getContents());
	
					// 	array_push($delivered, $data);
					// }
					
					$amount = $data[1];
					$coins = $data[2] ?? 1;
					
					for($i = 1; $i <= $coins; $i++){
						if($i != $coins){
							$numero = rand(1, $amount/2);
							$amount = $amount - $numero;
						}else{
							$numero = $amount;        
						}

					$response = $client->post($dnero_endpoint, [
						'json' => [
							"Latitude" => $dnero_lat + random_int(-5, 5) / 10000,
							"Longitude" => $dnero_lng + random_int(-5, 5) / 10000,
							"Accurency" => 20,
							"IsMock" => true,
							"Expiration" => Carbon::parse()->addMonth(1)->format('Y-m-d\TH:i:s.u\Z'),
							"Message" => "Superbowl",
							"TransactionMeanId" => 1,
							"Phone" => $data[0],
							"Amount" => $numero
						],
						'headers' => [
							'Content-Type' => "application/json",
							'Authorization' => "Bearer " . auth()->user()->token
						]
					]);
	
						\Log::info($response->getBody()->getContents());

						array_push($data, $numero);
						array_push($delivered, [
							$data[0],
							$data[1],
							$data[2],
							$numero
						]);
					}
				} catch (\Throwable $th) {
					\Log::info($th);
					array_push($errors, $data);
				}
			}
			return view('admin.import.status', ['delivered_coins' => $delivered, 'errors' => $errors]);
			
		} else {
				return redirect()->back()->withErrors(['errors' => 'Please, verify the data is complete']);
		}
	}
}
