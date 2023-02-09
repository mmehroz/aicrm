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

class searchleadController extends Controller
{
	public $emptyarray = array();
    public function randomsearchlead(Request $request){
        $validate = Validator::make($request->all(), [ 
	    	'brand_id'  	 => 'required',
		]);
		if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		if($request->brand_id == 5){
			$search = DB::table('searchlead')
			->select('*')
			->where('searchleadstatus_id','=',1)
			->whereIn('brand_id',[4,5])
			->inRandomOrder()
			->first();
		}elseif($request->brand_id == 1){
			$search = DB::table('lead')
			->select('lead_id as searchlead_id','lead_bussinessname as searchlead_bussinessname','lead_phone as searchlead_phone','lead_name as searchlead_name','lead_email as searchlead_email','lead_altemail as searchlead_altemail','lead_bussinessphone as searchlead_altphone')
			->where('leadstatus_id','=',3)
			->where('status_id','=',1)
			->inRandomOrder()
			->first();
		}else{
			$search = DB::table('searchlead')
			->select('*')
			->where('searchleadstatus_id','=',1)
			->where('brand_id','=',$request->brand_id)
			->inRandomOrder()
			->first();
		}
		if($search){
			return response()->json(['data' => $search,'message' => 'Search Lead Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}	
	}
	public function movesearchlead(Request $request){
        $validate = Validator::make($request->all(), [ 
	    	'searchlead_id'  	 	=> 'required',
			'searchlead_comment'  	=> 'required',
		]);
		if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		if($request->brand_id == 1){
			$lead = DB::table('lead')
			->select('*')
			->where('lead_id','=',$request->searchlead_id)
			->first();
			$adds[] = array(
				'searchlead_bussinessname' 	=> $lead->lead_bussinessname,
				'searchlead_phone' 			=> $lead->lead_phone,
				'searchlead_name'			=> $lead->lead_name,
				'searchlead_email' 			=> $lead->lead_email,
				'searchlead_altemail' 		=> $lead->lead_altemail,
				'searchlead_altphone' 		=> $lead->lead_bussinessphone,
				'searchlead_city' 			=> "",
				'searchlead_state' 			=> "",
				'searchlead_by'				=> $request->user_id,
				'searchlead_comment'		=> $request->searchlead_comment,
				'searchleadstatus_id'		=> $request->searchleadstatus_id,
				'searchlead_date'			=> date('Y-m-d'),
				'brand_id'					=> $request->brand_id,
				);
				$move = DB::table('searchlead')->insert($adds);
		}else{
			$move  = DB::table('searchlead')
			->where('searchlead_id','=',$request->searchlead_id )
			->update([
				'searchlead_by'			=> $request->user_id,
				'searchlead_comment'	=> $request->searchlead_comment,
				'searchleadstatus_id'	=> $request->searchleadstatus_id,
				'searchlead_date'		=> date('Y-m-d'),
			]);
		}
		if($move){
			return response()->json(['message' => 'Moved Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}	
	}
	public function searchleadlist(Request $request){
        $validate = Validator::make($request->all(), [ 
	    	'brand_id'  	 		=> 'required',
			'searchleadstatus_id'  	=> 'required',
		]);
		if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		$data = DB::table('searchlead')
		->select('*')
		->where('searchleadstatus_id','=',$request->searchleadstatus_id)
		->where('brand_id','=',$request->brand_id)
		->get();
		if($data){
			return response()->json(['data' => $data,'message' => 'Search Lead List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Search Lead List'],200);
		}	
	}
}