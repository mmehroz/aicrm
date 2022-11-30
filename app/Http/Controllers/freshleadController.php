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
use URL;

class freshleadController extends Controller
{
	public $emptyarray = array();
	public function savefreshlead(Request $request){
		$validate = Validator::make($request->all(), [
	    	'freshlead_name'  		=> 'required',
		    'freshlead_email' 		=> 'required',
		    'freshlead_phone' 		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds = array(
		'freshlead_name' 			=> $request->freshlead_name,
		'freshlead_email'			=> $request->freshlead_email,
		'freshlead_phone' 			=> $request->freshlead_phone,
		'freshlead_otherdetail' 	=> $request->freshlead_otherdetail,
		'freshlead_date'			=> date('Y-m-d'),
		'brand_id' 					=> $request->brandid,
		'status_id'	 				=> 1,
		'created_by'		 		=> $request->user_id,
		'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('freshlead')->insert($adds);
		if($save){
			return response()->json(['message' => 'Lead Saved Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function freshleadlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'from'	=> 'required',
	      'to'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if ($request->role_id == 1 || $request->role_id == 3) {
			$getorderlist = DB::table('freshlead')
			->select('*')
			->where('brand_id','=',$request->brandid)
			->whereBetween('freshlead_date',[$request->from, $request->to])
			->where('status_id','=',1)
			->get();		
		}else{
			$getorderlist = DB::table('freshlead')
			->select('*')
			->where('brand_id','=',$request->brandid)
			->where('created_by','=',$request->user_id)
			->whereBetween('freshlead_date',[$request->from, $request->to])
			->where('status_id','=',1)
			->get();		
		}
		if($getorderlist){
			return response()->json(['data' => $getorderlist,'message' => 'Save Lead List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Lead List'],200);
		}
	}
	public function savefreshleadfollowup(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'leadfollowup_comment'	=> 'required',
	      'freshlead_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds[] = array(
		'leadfollowup_comment' 	=> $request->leadfollowup_comment,
		'freshlead_id' 			=> $request->freshlead_id,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('leadfollowup')->insert($adds);
		if($save){
			return response()->json(['message' => 'Followup Saved Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function getfreshleadfollowup(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'freshlead_id'	=> 'required',
	    ]);
	 	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getdealfollowup = DB::table('getleadfollowup')
		->select('*')
		->where('freshlead_id','=',$request->freshlead_id)
		->where('status_id','=',1)
		->get();
		if($getdealfollowup){
			return response()->json(['data' => $getdealfollowup,'message' => 'Followup List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}