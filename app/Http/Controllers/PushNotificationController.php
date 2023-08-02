<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class PushNotificationController extends Controller
{


	public function __construct() {

        $this->middleware('permission:push_notification.list|push_notification.create|push_notification.edit|push_notification.delete', ['only' => ['index']]);
        $this->middleware('permission:push_notification.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:push_notification.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:push_notification.delete', ['only' => ['destroy']]);
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
		return view('admin.push_notifications.form');
	}


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => ['required', "max:255"],
            'description' => ['required']
        ]);

        $params = [];

        $contents = [
        "en" => $request->description,
        "es" => $request->description
        ];
        $params['contents'] = $contents;

        $headings = [
            "en" => $request->title,
            "es" => $request->title
            ];
        $params['headings'] = $headings;

        $params['included_segments'] = array('All');

        \OneSignal::sendNotificationCustom($params);

        $request->session()->flash('success', __('push_notifications.store_msg'));

		return ['success' => true];

    }

}
