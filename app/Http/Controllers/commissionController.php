<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Image;
use DB;
use Input;
use App\Item;
use Session;
use Response;
use Validator;
use ZipArchive;
use URL;

class commissionController extends Controller
{
	public function addcommission(Request $request){
		$validate = Validator::make($request->all(), [
	    	'commission'  	=> 'required',
			'id'  			=> 'required',
	    ]);
		if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		$multiple = $request->commission;
		foreach ($multiple as $multiples) {
		$addcommission[] = array(
		'commission_from'	=> $multiples['commission_from'],
		'commission_to'		=> $multiples['commission_to'],
		'commission_rate' 	=> $multiples['commission_rate'],
		'brandtype_id' 		=> $multiples['brandtype_id'],
		'user_id' 			=> $request->id,
		'role_id' 			=> $request->role_id,
		'status_id'		 	=> 1,
		'created_by'	 	=> $request->user_id,
		'created_at'	 	=> date('Y-m-d h:i:s'),
		);
		}
		DB::table('commission')->insert($addcommission);
		if($addcommission){
			return response()->json(['message' => 'Commission Added Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function commissionlist(Request $request){
		$validate = Validator::make($request->all(), [
	    	'id'  	=> 'required',
	    ]);
		if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		$getcommissionlist = DB::table('commissionlist')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->get();	
		if($getcommissionlist){
			return response()->json(['commissiondata' => $getcommissionlist, 'message' => 'Commission List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
}