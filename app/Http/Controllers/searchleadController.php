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
		$datebeforethreemonths = null;
		if($request->role_id == 21){
			$search = DB::connection('mysql3')->table('dmeclient')
			->select('dmeclient_id as searchlead_id','dmeclient_lastname as searchlead_bussinessname','dmeclient_homephone as searchlead_phone','dmeclient_name as searchlead_name','dmeclient_email as searchlead_email','dmeclient_email as searchlead_altemail','dmeclient_cellphone as searchlead_altphone')
			->where('status_id','=',1)
			->inRandomOrder()
			->first();
		}else{
			if($request->brand_id == 1){
				$leaddate = DB::table('lead')
				->select('lead_date')
				->where('leadstatus_id','=',3)
				->where('status_id','=',1)
				->orderByDesc('lead_id')
				->first();
				$datebeforethreemonths = date('Y-m-d', strtotime($leaddate->lead_date . "-2 months") );
				$leadafterthreemonth = DB::table('orderpayment')
				->select('lead_id')
				->where('orderpayment_date','<',$datebeforethreemonths)
				->where('status_id','=',1)
				->get();
				$sortleadafter = array();
				foreach($leadafterthreemonth as $leadafterthreemonths){
					$sortleadafter[] = $leadafterthreemonths->lead_id;
				}
				// $leadbeforethreemonth = DB::table('orderpayment')
				// ->select('lead_id')
				// ->where('orderpayment_date','<',$datebeforethreemonths)
				// ->whereNotIn('lead_id',$sortleadafter)
				// ->where('status_id','=',1)
				// ->get();
				// $sortleadbefore = array();
				// foreach($leadbeforethreemonth as $leadbeforethreemonths){
				// 	$sortleadbefore[] = $leadbeforethreemonths->lead_id;
				// }
				$search = DB::table('lead')
				->select('lead_id as searchlead_id','lead_bussinessname as searchlead_bussinessname','lead_phone as searchlead_phone','lead_name as searchlead_name','lead_email as searchlead_email','lead_altemail as searchlead_altemail','lead_bussinessphone as searchlead_altphone')
				->where('lead_date','<',$datebeforethreemonths)
				->whereIn('lead_id',$sortleadafter)
				->where('is_search','=',0)
				->where('leadstatus_id','=',3)
				->where('status_id','=',1)
				->inRandomOrder()
				->first();
				$notactive = array(
					'is_search' 	=> 1,
				);
				if(isset($search->searchlead_id)){
					DB::table('lead')
					->where('lead_id','=',$search->searchlead_id)
					->update($notactive); 
				}
			}else{
				$search = DB::table('searchlead')
				->select('*')
				->where('searchleadstatus_id','=',1)
				->where('brand_id','=',$request->brand_id)
				->inRandomOrder()
				->first();
			}
		}
		if($search){
			return response()->json(['data' => $search,'id' => $datebeforethreemonths,'message' => 'Search Lead Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}	
	}
	public function movesearchlead(Request $request){
        $validate = Validator::make($request->all(), [ 
	    	'searchlead_id'  	 	=> 'required',
			'searchlead_comment'  	=> 'required',
			'searchleadstatus_id'  	=> 'required',
		]);
		if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		if($request->role_id == 21){
			$lead = DB::connection('mysql3')->table('dmeclient')
			->select('*')
			->where('dmeclient_id','=',$request->searchlead_id)
			->first();
			$adds[] = array(
				'searchlead_bussinessname' 	=> $lead->dmeclient_lastname,
				'searchlead_phone' 			=> $lead->dmeclient_homephone,
				'searchlead_name'			=> $lead->dmeclient_name,
				'searchlead_email' 			=> $lead->dmeclient_email,
				'searchlead_altemail' 		=> $lead->dmeclient_email,
				'searchlead_altphone' 		=> $lead->dmeclient_cellphone,
				'searchlead_city' 			=> "",
				'searchlead_state' 			=> "",
				'searchlead_by'				=> $request->user_id,
				'searchlead_comment'		=> $request->searchlead_comment,
				'searchleadstatus_id'		=> $request->searchleadstatus_id,
				'searchlead_date'			=> date('Y-m-d'),
				'brand_id'					=> $request->brand_id,
				'dmeclient_id' 				=> $lead->dmeclient_id,
				);
				$move = DB::table('searchlead')->insert($adds);
		}else{
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
		->where('searchlead_by','=',$request->user_id)
		->get();
		if($data){
			return response()->json(['data' => $data,'message' => 'Search Lead List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Search Lead List'],200);
		}	
	}
	public function dmeorderdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'dmeclient_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getorderdetails = DB::connection('mysql3')->table('dmeorderdetails')
		->select('*')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->where('status_id','=',1)
		->first();
		$getotherorderdetails = DB::connection('mysql3')->table('dmeotherdetails')
		->select('*')
		->where('dmeclient_id','=',$request->dmeclient_id)
		->where('status_id','=',1)
		->first();
		if($getorderdetails){
			return response()->json(['data' => $getorderdetails, 'otherdata' => $getotherorderdetails, 'message' => 'Order Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function savesearchleadfollowup(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'searchleadfollowup_comment'	=> 'required',
	      'searchlead_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds[] = array(
		'searchleadfollowup_comment' 	=> $request->searchleadfollowup_comment,
		'searchlead_id' 				=> $request->searchlead_id,
		'status_id'		 				=> 1,
		'created_by'	 				=> $request->user_id,
		'created_at'	 				=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('searchleadfollowup')->insert($adds);
		if($save){
			return response()->json(['message' => 'Followup Saved Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function getsearchleadfollowup(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'searchlead_id'	=> 'required',
	    ]);
	 	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getdealfollowup = DB::table('getsearchleadfollowup')
		->select('*')
		->where('searchlead_id','=',$request->searchlead_id)
		->where('status_id','=',1)
		->get();
		if($getdealfollowup){
			return response()->json(['data' => $getdealfollowup,'message' => 'Followup List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}