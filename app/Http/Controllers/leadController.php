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

class leadController extends Controller
{
	public $emptyarray = array();
	public function createlead(Request $request){
		$checkemail = DB::table('lead')
		->select('lead_email')
		->where('lead_email','=',$request->lead_email)
		->where('status_id','=',1)
		->where('brand_id','=',$request->brand_id)
		->first();
		if (isset($checkemail)) {
			return response()->json("Lead Email Already Exist", 400);
		}
		$validate = Validator::make($request->all(), [ 
	      'role_id' 			=> 'required',
	      'lead_name' 			=> 'required',
	      'lead_email'			=> 'required',
		  'brand_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds = array(
		'lead_name' 			=> $request->lead_name,
		'lead_email'			=> $request->lead_email,
		'lead_altemail' 		=> $request->lead_altemail,
		'lead_phone' 			=> $request->lead_phone,
		'city_id' 				=> 1,
		'state_id'				=> $request->state_id,
		'country_id' 			=> $request->country_id,
		'lead_zip' 				=> $request->lead_zip,
		'lead_address' 			=> $request->lead_address,
		'lead_bussinessname' 	=> $request->lead_bussinessname,
		'lead_bussinessemail'	=> $request->lead_bussinessemail,
		'lead_bussinesswebsite' => $request->lead_bussinesswebsite,
		'lead_bussinessphone' 	=> $request->lead_bussinessphone,
		'lead_otherdetails' 	=> $request->lead_otherdetails,
		'lead_pickby' 			=> $request->role_id == 6 || $request->role_id == 7 || $request->role_id == 2 ? $request->user_id : null,
		'lead_date' 			=> date('Y-m-d'),
		'leadstatus_id'		 	=> $request->role_id == 6 || $request->role_id == 7 || $request->role_id == 2 ? 2 : 1,
		'brand_id'		 		=> $request->brand_id,
		'leadtype_id'	 		=> 2,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('lead')->insert($adds);
		if($save){
			if(isset($request->patchquery_id)){
				DB::table('patchquery')
				->where('patchquery_id','=',$request->patchquery_id)
				->update([
					'patchquery_islead' 	=> 1,
				]);
			}
			return response()->json(['message' => 'Lead Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatelead(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id' 				=> 'required',
	      'lead_name' 				=> 'required',
	      'lead_email'				=> 'required',
	      'lead_altemail' 			=> 'required',
	      'lead_phone'				=> 'required',
	      'state_id'				=> 'required',
	      'country_id' 				=> 'required',
	      'lead_zip'				=> 'required',
	      'lead_address'			=> 'required',
	      'lead_bussinessname' 		=> 'required',
	      'lead_bussinessemail'		=> 'required',
	      'lead_bussinesswebsite' 	=> 'required',
	      'lead_bussinessphone'		=> 'required',
	      'lead_otherdetails' 		=> 'required',
	      'brand_id'				=> 'required',
	    ]);
	 	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$checkemail = DB::table('lead')
		->select('lead_email')
		->where('lead_id','=',$request->lead_id)
		->where('status_id','=',1)
		->first();
		if ($checkemail->lead_email != $request->lead_email) {
		$validateunique = Validator::make($request->all(), [ 
	      'lead_email' 		=> 'unique:lead,lead_email',
	    ]);
     	if ($validateunique->fails()) {    
			return response()->json("Lead Email Already Exist", 400);
		}
		}
		$updatelead  = DB::table('lead')
		->where('lead_id','=',$request->lead_id)
		->update([
		'lead_name' 			=> $request->lead_name,
		'lead_email'			=> $request->lead_email,
		'lead_altemail' 		=> $request->lead_altemail,
		'lead_phone' 			=> $request->lead_phone,
		'state_id'				=> $request->state_id,
		'country_id' 			=> $request->country_id,
		'lead_zip' 				=> $request->lead_zip,
		'lead_address' 			=> $request->lead_address,
		'lead_bussinessname' 	=> $request->lead_bussinessname,
		'lead_bussinessemail'	=> $request->lead_bussinessemail,
		'lead_bussinesswebsite' => $request->lead_bussinesswebsite,
		'lead_bussinessphone' 	=> $request->lead_bussinessphone,
		'lead_otherdetails' 	=> $request->lead_otherdetails,
		'brand_id'				=> $request->brand_id,
		'status_id'		 		=> 1,
		'updated_by'	 		=> $request->user_id,
		'updated_at'	 		=> date('Y-m-d h:i:s'),
		]);
		if($updatelead){
			return response()->json(['message' => 'Lead Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function leadlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'role_id'		=> 'required',
	      'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if ($request->role_id == 1) {
			$getleadlist = DB::table('leaddetail')
			->select('*')
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->orderBy('lead_id','DESC')
			->get();
		}else{
			$getleadlist = DB::table('leaddetail')
			->select('*')
			->where('brand_id','=',$request->brand_id)
			->where('created_by','=',$request->user_id)
			->where('status_id','=',1)
			->orderBy('lead_id','DESC')
			->get();
		}
		if(isset($getleadlist)){
			return response()->json(['data' => $getleadlist,'message' => 'Lead List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Lead List'],200);
		}
	}
	public function leaddetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Lead Id Required", 400);
		}
		$getdetails = DB::table('leadcompletedetails')
		->select('*')
		->where('lead_id','=',$request->lead_id)
		->where('status_id','!=',2)
		->first();
		if($getdetails){
			return response()->json(['data' => $getdetails,'message' => 'Lead Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deletelead(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Lead Id Required", 400);
		}
		$update  = DB::table('lead')
		->where('lead_id','=',$request->lead_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($update){
			return response()->json(['message' => 'Lead Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function forwardedleadlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->role_id != 7){
			$getleadlist = DB::table('leaddetail')
			->select('*')
			->where('brand_id','=',$request->brand_id)
			->where('lead_pickby','=',null)
			->where('leadstatus_id','=',1)
			->where('status_id','=',3)
			->orderBy('lead_id','DESC')
			->paginate(30);
		}
		if(isset($getleadlist)){
			return response()->json(['data' => $getleadlist,'message' => 'Forwarded Lead List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Forwarded Lead List'],200);
		}
	}
	public function pickedleadlist(Request $request){
		if($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 3){
			$getleadlist = DB::table('leaddetail')
			->select('*')
			->where('brand_id','=',$request->brand_id)
			->where('leadstatus_id','=',$request->leadstatus_id)
			->where('status_id','!=',2)
			->orderBy('lead_id','DESC')
			->paginate(30);
		}else{
			$getleadlist = DB::table('leaddetail')
			->select('*')
			->where('lead_pickby','=',$request->user_id)
			->where('brand_id','=',$request->brand_id)
			->where('leadstatus_id','=',$request->leadstatus_id)
			->where('status_id','!=',2)
			->orderBy('lead_id','DESC')
			->paginate(30);
		}
		if(isset($getleadlist)){
			return response()->json(['data' => $getleadlist,'message' => 'Picked Lead List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Lead List'],200);
		}
	}
	public function searchlead(Request $request){
		$validate = Validator::make($request->all(), [ 
			'brand_id'		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->role_id == 1 || $request->role_id == 2 || $request->role_id == 3){
			$getleadlist = DB::table('leaddetail')
			->select('*')
			->where('brand_id','=',$request->brand_id)
			->where('leadstatus_id','=',3)
			->where('lead_email','like','%'.$request->lead_email.'%')
			->where('status_id','!=',2)
			->orderBy('lead_id','DESC')
			->get();
		}else{
			$getleadlist = DB::table('leaddetail')
			->select('*')
			->where('lead_pickby','=',$request->user_id)
			->where('brand_id','=',$request->brand_id)
			->where('leadstatus_id','=',3)
			->where('lead_email','like','%'.$request->lead_email.'%')
			->where('status_id','!=',2)
			->orderBy('lead_id','DESC')
			->get();
		}
		if(isset($getleadlist)){
			return response()->json(['data' => $getleadlist,'message' => 'Search Leads'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Search Lead'],200);
		}
	}
	public function automanualleadlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'brand_id'	=> 'required',
	      'leadtype_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getleadlist = DB::table('leaddetail')
		->select('*')
		->where('brand_id','=',$request->brand_id)
		->where('leadtype_id','=',$request->leadtype_id)
		->where('leadstatus_id','<=',2)
		->where('status_id','=',3)
		->orderBy('lead_id','DESC')
		->paginate(30);
		if(isset($getleadlist)){
			return response()->json(['data' => $getleadlist,'message' => 'Forwarded Lead List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Forwarded Lead List'],200);
		}
	}
	public function picklead(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Lead Id Required", 400);
		}
		$update  = DB::table('lead')
		->where('lead_id','=',$request->lead_id)
		->update([
			'lead_pickby'	=> $request->user_id,
			'leadstatus_id'	=> 2,
		]); 
		if($update){
			return response()->json(['message' => 'Lead Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function unpicklead(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Lead Id Required", 400);
		}
		$update  = DB::table('lead')
		->where('lead_id','=',$request->lead_id)
		->update([
			'lead_pickby'	=> null,
			'leadstatus_id'	=> 1,
		]); 
		if($update){
			return response()->json(['message' => 'Lead Un-Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function cancellead(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Lead Id Required", 400);
		}
		$update  = DB::table('lead')
		->where('lead_id','=',$request->lead_id)
		->update([
			'leadstatus_id'	=> 4,
			'status_id'		=> 1,
		]); 
		if($update){
			return response()->json(['message' => 'Lead Cacel Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function makelead(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Lead Id Required", 400);
		}
		$order = DB::table('order')
		->select('*')
		->where('lead_id','=',$request->lead_id)
		->where('status_id','=',3)
		->first();
		$update  = DB::table('lead')
		->where('lead_id','=',$request->lead_id)
		->update([
			'leadstatus_id'	=> 3,
			'status_id'		=> 1,
			'created_by'	=> $request->user_id,
		]);
		DB::table('order')
		->where('order_id','=',$order->order_id)
		->update([
			'status_id'		=> 1,
			'created_by'	=> $request->user_id,
		]);
		DB::table('orderpayment')
		->where('order_id','=',$order->order_id)
		->update([
			'status_id'		=> 1,
			'created_by'	=> $request->user_id,
		]); 
		DB::table('orderrefrence')
		->where('order_id','=',$order->order_id)
		->update([
			'status_id'		=> 1,
			'created_by'	=> $request->user_id,
		]);
		DB::table('orderqa')
		->where('order_id','=',$order->order_id)
		->update([
			'status_id'		=> 1,
			'created_by'	=> $request->user_id,
		]);
		DB::table('orderattachment')
		->where('order_id','=',$order->order_id)
		->update([
			'status_id'		=> 1,
			'created_by'	=> $request->user_id,
		]);
		DB::table('leadgenerate')
		->where('lead_id','=',$request->lead_id)
		->update([
			'leadstatus_id'	=> 3,
		]);
		if($update){
			return response()->json(['message' => 'Make Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function clientrevenuereport(Request $request){
		$validate = Validator::make($request->all(), [ 
			'role_id' 			=> 'required',
			'brand_id'			=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->role_id <= 3){
			$lead = DB::table('leaddetail')
			->select('lead_id','lead_name','lead_email','lead_phone','lead_bussinessname')
			->where('brand_id','=',$request->brand_id)
			->where('leadstatus_id','=',3)
			->where('status_id','=',1)
			->orderBy('lead_id','DESC')
			->get();
			$clients = array();
			$index=0;
			foreach($lead as $leads){
				$totalrevenue = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('lead_id','=',$leads->lead_id)
				->sum('orderpayment_amount');
				$forwardedrevenue = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('lead_id','=',$leads->lead_id)
				->where('orderpaymentstatus_id','=',8)
				->sum('orderpayment_amount');
				$pickedrevenue = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('lead_id','=',$leads->lead_id)
				->where('orderpaymentstatus_id','=',9)
				->sum('orderpayment_amount');
				$invoicesentrevenue = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('lead_id','=',$leads->lead_id)
				->where('orderpaymentstatus_id','=',2)
				->sum('orderpayment_amount');
				$paidrevenue = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('lead_id','=',$leads->lead_id)
				->where('orderpaymentstatus_id','=',3)
				->sum('orderpayment_amount');
				$cancelrevenue = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('lead_id','=',$leads->lead_id)
				->where('orderpaymentstatus_id','=',4)
				->sum('orderpayment_amount');
				$refundrevenue = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('lead_id','=',$leads->lead_id)
				->where('orderpaymentstatus_id','=',5)
				->sum('orderpayment_amount');
				$chargebackrevenue = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('lead_id','=',$leads->lead_id)
				->where('orderpaymentstatus_id','=',6)
				->sum('orderpayment_amount');
				$recoveryrevenue = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('lead_id','=',$leads->lead_id)
				->where('orderpaymentstatus_id','=',7)
				->sum('orderpayment_amount');
				$leads->totalrevenue = $totalrevenue;
				$leads->forwardedrevenue = $forwardedrevenue;
				$leads->pickedrevenue = $pickedrevenue;
				$leads->invoicesentrevenue = $invoicesentrevenue;
				$leads->paidrevenue = $paidrevenue;
				$leads->cancelrevenue = $cancelrevenue;
				$leads->refundrevenue = $refundrevenue;
				$leads->chargebackrevenue = $chargebackrevenue;
				$leads->recoveryrevenue = $recoveryrevenue;
				$clients[$index] = $leads;
				$index++;
			}
		}
		if(isset($clients)){
			return response()->json(['data' => $clients,'message' => 'Client Wise Revenue Report'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Client Wise Revenue Report'],200);
		}
	}
	public function transferclient(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'		=> 'required',
	      'id'			=> 'required',
		  'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$transfer  = DB::table('lead')
		->where('lead_id','=',$request->lead_id)
		->update([
			'lead_pickby' 	=> $request->id,
			'brand_id' 		=> $request->brand_id,
			'created_by' 	=> $request->id,
		]);
		if(isset($transfer)){
			return response()->json(['message' => 'Client Transfer Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong'],200);
		}
	}
	public function allclientlist(Request $request){
		$validate = Validator::make($request->all(), [ 
			'brand_id'	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$clients = DB::table('leaddetail')
		->select('*')
		->where('brand_id','=',$request->brand_id)
		->where('leadstatus_id','=',3)
		->where('status_id','=',1)
		->orderBy('lead_id','DESC')
		->paginate(30);
		if(isset($clients)){
			return response()->json(['data' => $clients,'message' => 'All Client List List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'All Client List List'],200);
		}
	}
}