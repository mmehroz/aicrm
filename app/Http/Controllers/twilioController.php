<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use ZipArchive;
use Image;
use DB;
use Input;
use App\Item;
use Session;
use Response;
use Validator;
use URL;
use Twilio\Rest\Client;

class twilioController extends Controller      
{
	public $emptyarray = array();
	public function sendsms(Request $request)
    {
        $account_sid = "ACab3a98ae37fb5f925ae52bd26521b7b4";
        $auth_token = "d8909bb2fe3c6ba501f1d5dab826aa7b";
        $twilio_number = "+14345955489";
        $recipients = "+3331230521";
        $message = "Hi";
        $client = new Client($account_sid, $auth_token);
        $client->messages->create($recipients,
                ['from' => $twilio_number, 'body' => $message]);
    	return response()->json(['message' => 'Sent Successfully'],200);
    }
}