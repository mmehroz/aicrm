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

class billingController extends Controller
{
	public $emptyarray = array();
	public function forwardedpaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'from'	=> 'required',
	      'to'		=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json("From And To Date Is Required", 400);
		}
		$paymentlist = DB::table('orderpaymentdetails')
		->select('*')
		->where('orderpaymentstatus_id','=',8)
		->where('orderpayment_pickby','=',null)
		->whereBetween('orderpayment_date',[$request->from, $request->to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('orderpayment_id','DESC')
		->paginate(30);	
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist, 'message' => 'Forwarded Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Forwarded Order List'],200);
		}
	}
	public function pickedpaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'from'					=> 'required',
	      'to'						=> 'required',
	      'orderpaymentstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json("From And To Date Is Required", 400);
		}
		$getmergedeal = DB::table('mergedeal')
		->select('order_token')
		->where('status_id','=',1)
		->get();
		$getmergedealtoken = array();
		foreach ($getmergedeal as $getmergedeals) {
			$getmergedealtoken[] = $getmergedeals->order_token;
		}
		$paymentlist = DB::table('orderpaymentdetails')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
		->where('orderpayment_pickby','=',$request->user_id)
		->whereBetween('orderpayment_date',[$request->from, $request->to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('orderpayment_id','DESC')
		->get();	
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist,'message' => 'Picked Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Order List'],200);
		}
	}
	public function mergepickedpaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'from'					=> 'required',
	      'to'						=> 'required',
	      'orderpaymentstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		$getmergedeal = DB::table('mergedeal')
		->select('order_token')
		->where('status_id','=',1)
		->get();
		$getmergedealtoken = array();
		foreach ($getmergedeal as $getmergedeals) {
			$getmergedealtoken[] = $getmergedeals->order_token;
		}
		$paymentlist = DB::table('mergepaymentdetails')
		->select('*')
		->whereIn('order_token', $getmergedealtoken)
		->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
		->where('orderpayment_pickby','=',$request->user_id)
		->whereBetween('orderpayment_date',[$request->from, $request->to])
		->where('status_id','=',1)
		->groupBy('mergedeal_token')
		->orderBy('orderpayment_id','DESC')
		->get();	
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist,'message' => 'Picked Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Order List'],200);
		}
	}
	public function statuswisepaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'from'					=> 'required',
	      'to'						=> 'required',
	      'orderpaymentstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json("From And To Date Is Required", 400);
		}
		$getmergedeal = DB::table('mergedeal')
		->select('order_token')
		->where('status_id','=',1)
		->get();
		$getmergedealtoken = array();
		foreach ($getmergedeal as $getmergedeals) {
			$getmergedealtoken[] = $getmergedeals->order_token;
		}
		$paymentlist = DB::table('orderpaymentdetails')
		->select('*')
		->whereNotIn('order_token', $getmergedealtoken)
		->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
		->where('orderpayment_pickby','=',$request->user_id)
		->whereBetween('orderpayment_date',[$request->from, $request->to])
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('orderpayment_id','DESC')
		->paginate(30);	
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist,'message' => 'Picked Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Order List'],200);
		}
	}
	public function mergestatuswisepaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'from'					=> 'required',
	      'to'						=> 'required',
	      'orderpaymentstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		$getmergedeal = DB::table('mergedeal')
		->select('order_token')
		->where('status_id','=',1)
		->get();
		$getmergedealtoken = array();
		foreach ($getmergedeal as $getmergedeals) {
			$getmergedealtoken[] = $getmergedeals->order_token;
		}
		$paymentlist = DB::table('mergepaymentdetails')
		->select('*')
		->whereIn('order_token', $getmergedealtoken)
		->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
		->where('orderpayment_pickby','=',$request->user_id)
		->whereBetween('orderpayment_date',[$request->from, $request->to])
		->where('status_id','=',1)
		->groupBy('mergedeal_token')
		->orderBy('orderpayment_id','DESC')
		->paginate(30);	
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist,'message' => 'Picked Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Order List'],200);
		}
	}
	public function pickpayment(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Order Id Required", 400);
		}
		$update  = DB::table('orderpayment')
		->where('order_id','=',$request->order_id)
		->update([
			'orderpayment_pickby'	=> $request->user_id,
			'orderpaymentstatus_id'	=> 9,
		]); 
		if($update){
			return response()->json(['message' => 'Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function unpickpayment(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Order Id Required", 400);
		}
		$update  = DB::table('orderpayment')
		->where('order_id','=',$request->order_id)
		->update([
			'orderpayment_pickby'	=> null,
			'orderpaymentstatus_id'	=> 8,
		]); 
		if($update){
			return response()->json(['message' => 'Un-Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function paymentdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_type'	=> 'required',
		  'order_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		if ($request->order_type == "Merge") {
			$paymentdetail = DB::table('mergepaymentdetails')
			->where('mergedeal_token','=',$request->order_id)
			->select('*')
			->get();
			$getleadid = DB::table('mergepaymentdetails')
			->where('mergedeal_token','=',$request->order_id)
			->select('lead_id','mergedeal_token')
			->first();
			$order_token = $getleadid->mergedeal_token;
		}else{
			$paymentdetail = DB::table('orderpaymentdetails')
			->where('order_id','=',$request->order_id)
			->select('*')
			->get();
			$getleadid = DB::table('orderpaymentdetails')
			->where('order_id','=',$request->order_id)
			->select('lead_id','order_token')
			->first();
			$order_token = $getleadid->order_token;
		}
		$getleaddetail = DB::table('lead')
		->where('lead_id','=',$getleadid->lead_id)
		->select('brand_id','lead_name','lead_email','lead_phone','lead_bussinessname')
		->first();
		$getbranddetail = DB::table('brand')
		->where('brand_id','=',$getleaddetail->brand_id)
		->select('brand_logo','brand_email','brand_website','brand_invoicename')
		->first();
		$logopath = URL::to('/')."/public/brand_logo/";
		$inviceinfo = array(
			'lead_name' 			=> $getleaddetail->lead_name,
			'lead_email' 			=> $getleaddetail->lead_email,
			'lead_phone' 			=> $getleaddetail->lead_phone,
			'lead_bussinessname' 	=> $getleaddetail->lead_bussinessname,
			'brand_email' 			=> $getbranddetail->brand_email,
			'brand_website' 		=> $getbranddetail->brand_website,
			'brand_invoicename' 	=> $getbranddetail->brand_invoicename,
			'order_token' 			=> $order_token,
			'brand_logo' 			=> $getbranddetail->brand_logo,
			'brand_logopath' 		=> $logopath,
		);
		if(isset($paymentdetail)){
			return response()->json(['data' => $paymentdetail,'inviceinfo' => $inviceinfo, 'message' => 'Payment Details'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'inviceinfo' => $inviceinfo, 'message' => 'Payment Details'],200);
		}
	}
	public function updatepaymentstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_type'				=> 'required',
	      'orderpaymentstatus_id'	=> 'required',
	      'order_id'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if ($request->order_type == "Merge") {
			$validate = Validator::make($request->all(), [ 
		      'order_id'	=> 'required',
		    ]);
	     	if ($validate->fails()) {
				return response()->json($validate->errors(), 400);
			}
			$ordertoken = DB::table('mergedeal')
			->select('order_token')
			->where('mergedeal_token','=',$request->order_id)
			->where('status_id','=',1)
			->get();
			$token = array();
			foreach ($ordertoken as $ordertokens) {
				$token[] = $ordertokens->order_token;
			}
			if ($request->orderpaymentstatus_id == 4) {
				$validate = Validator::make($request->all(), [ 
			      'orderpayment_comment'	=> 'required',
			    ]);
		     	if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				$update  = DB::table('orderpayment')
				->whereIn('order_token',$token)
				->update([
					'orderpayment_comment'	=> $request->orderpayment_comment,
					'orderpaymentstatus_id'	=> $request->orderpaymentstatus_id,
				]); 	
			}elseif ($request->orderpaymentstatus_id == 2) {
				$validate = Validator::make($request->all(), [ 
			      'merchant_id'				=> 'required',
			      'orderpayment_invoiceno'	=> 'required',
			    ]);
		     	if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				$update  = DB::table('orderpayment')
				->whereIn('order_token',$token)
				->update([
					'merchant_id'				=> $request->merchant_id,
					'orderpayment_invoiceno'	=> $request->orderpayment_invoiceno,
					'orderpaymentstatus_id'		=> $request->orderpaymentstatus_id,
				]); 	
			}else{
				$update  = DB::table('orderpayment')
				->whereIn('order_token',$token)
				->update([
					'orderpaymentstatus_id'	=> $request->orderpaymentstatus_id,
				]); 
			}
		}else{
			if ($request->orderpaymentstatus_id == 2) {
				$validate = Validator::make($request->all(), [ 
			      'merchant_id'				=> 'required',
			      'orderpayment_invoiceno'	=> 'required',
			    ]);
		     	if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				$update  = DB::table('orderpayment')
				->whereIn('order_token',$request->order_id)
				->update([
					'merchant_id'				=> $request->merchant_id,
					'orderpayment_invoiceno'	=> $request->orderpayment_invoiceno,
					'orderpaymentstatus_id'		=> $request->orderpaymentstatus_id,
				]); 	
			}
			elseif ($request->orderpaymentstatus_id == 4) {
				$validate = Validator::make($request->all(), [ 
			      'orderpayment_comment'	=> 'required',
			    ]);
		     	if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				$update  = DB::table('orderpayment')
				->whereIn('order_token',$request->order_id)
				->update([
					'orderpayment_comment'	=> $request->orderpayment_comment,
					'orderpaymentstatus_id'	=> $request->orderpaymentstatus_id,
				]); 
			}else{
				$update  = DB::table('orderpayment')
				->whereIn('order_token',$request->order_id)
				->update([
					'orderpaymentstatus_id'	=> $request->orderpaymentstatus_id,
				]); 
			}
		}
		if($update){
			return response()->json(['message' => 'Payment Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function mergedeal(Request $request){
		$mergedeal_token = mt_rand(1000000, 9999999);
		$multiple = $request->multiple;
		$index = 0;
		foreach ($multiple as $multiples) {
			$adds[] = array(
			'mergedeal_token' 	=> $mergedeal_token,
			'order_token' 		=> $multiples,
			'status_id'		 	=> 1,
			'created_by'	 	=> $request->user_id,
			'created_at'	 	=> date('Y-m-d h:i:s'),
			);
			$index++;
		}
	    $save = DB::table('mergedeal')->insert($adds);
		if($save){
			return response()->json(['message' => 'Deals Merge Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function unmergedeal(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_id'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$update = DB::table('mergedeal')
			->where('mergedeal_token','=',$request->order_id)
			->update([
			'status_id' 	=> 2,
		]);
		if($update){
			return response()->json(['message' => 'Un Merge Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function merchantlist(Request $request){
		$merchantlist = DB::table('merchant')
		->select('*')
		->where('status_id','=',1)
		->get();	
		if(isset($merchantlist)){
			return response()->json(['data' => $merchantlist, 'message' => 'Merchant List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Merchant List'],200);
		}
	}
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
	public function paymentamount(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_id'	=> 'required',
	      'order_type'	=> 'required',
	    ]);
	 	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if ($request->order_type == "Merge") {
			$getmergedeal = DB::table('mergedeal')
			->select('order_token')
			->where('status_id','=',1)
			->get();
			$getmergedealtoken = array();
			foreach ($getmergedeal as $getmergedeals) {
				$getmergedealtoken[] = $getmergedeals->order_token;
			}	
			$getdealfollowup = DB::table('orderpayment')
			->select('orderpayment_amount')
			->whereIn('order_token',$getmergedealtoken)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
		}else{
			$getdealfollowup = DB::table('orderpayment')
			->select('orderpayment_amount')
			->whereIn('order_token',$request->order_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
		}
		
		if($getdealfollowup){
			return response()->json(['data' => $getdealfollowup,'message' => 'Followup List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}