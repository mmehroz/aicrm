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
	    //   'from'	=> 'required',
	    //   'to'		=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json("From And To Date Is Required", 400);
		}
		$paymentlist = DB::table('orderpaymentdetails')
		->select('*')
		->where('orderpaymentstatus_id','=',8)
		->where('orderpayment_pickby','=',null)
		// ->whereBetween('orderpayment_date',[$request->from, $request->to])
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->groupBy('order_token')
		->orderBy('orderpayment_id','DESC')
		->get();	
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist, 'message' => 'Forwarded Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Forwarded Order List'],200);
		}
	}
	public function pickedpaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    //   'from'					=> 'required',
	    //   'to'						=> 'required',
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
		// ->where('orderpayment_pickby','=',$request->user_id)
		// ->whereBetween('orderpayment_date',[$request->from, $request->to])
		->where('brand_id','=',$request->brand_id)
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
	    //   'from'					=> 'required',
	    //   'to'						=> 'required',
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
		// ->where('orderpayment_pickby','=',$request->user_id)
		// ->whereBetween('orderpayment_date',[$request->from, $request->to])
		->where('brand_id','=',$request->brand_id)
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
		  'brand_id'				=> 'required', 
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
		if($request->orderpaymentstatus_id == 2){
			$paymentlisttopaid = DB::table('orderpaymentdetails')
			->select('*', DB::raw("0 as topaid"))
			->whereNotIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->whereBetween('orderpayment_date',[$request->from, $request->to])
			->where('orderpayment_lastpaiddate','>=',date('Y-m-d'))
			->where('status_id','=',1)
			->groupBy('order_token')
			->orderBy('orderpayment_id','DESC')
			->get()->toArray();	
			$paymentlisttorecovery = DB::table('orderpaymentdetails')
			->select('*', DB::raw("1 as topaid"))
			->whereNotIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->whereBetween('orderpayment_date',[$request->from, $request->to])
			->where('orderpayment_lastpaiddate','<',date('Y-m-d'))
			->where('status_id','=',1)
			->groupBy('order_token')
			->orderBy('orderpayment_id','DESC')
			->get()->toArray();	
			$paymentlist = array_merge($paymentlisttopaid,$paymentlisttorecovery);
			rsort($paymentlist);
		}elseif($request->orderpaymentstatus_id == 7){
			$paymentlist = DB::table('orderpaymentdetails')
				->select('*')
				->whereNotIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_recoverydate',[$request->from, $request->to])
				->where('status_id','=',1)
				->groupBy('order_token')
				->orderBy('orderpayment_id','DESC')
				->paginate(30);
		}else{
			if($request->orderpaymentstatus_id == 3){
				$validate = Validator::make($request->all(), [ 
					'billingmerchant_id'	=> 'required',
				]);
				if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				if($request->billingmerchant_id == 0){
					$paymentlist = DB::table('orderpaymentdetails')
					->select('*')
					->whereNotIn('order_token', $getmergedealtoken)
					->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
					->where('brand_id','=',$request->brand_id)
					// ->where('orderpayment_pickby','=',$request->user_id)
					->whereBetween('orderpayment_date',[$request->from, $request->to])
					->where('status_id','=',1)
					->groupBy('order_token')
					->orderBy('orderpayment_id','DESC')
					->paginate(30);
				}else{
					$paymentlist = DB::table('orderpaymentdetails')
					->select('*')
					->whereNotIn('order_token', $getmergedealtoken)
					->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
					->where('brand_id','=',$request->brand_id)
					->where('merchant_id','=',$request->billingmerchant_id)
					// ->where('orderpayment_pickby','=',$request->user_id)
					->whereBetween('orderpayment_date',[$request->from, $request->to])
					->where('status_id','=',1)
					->groupBy('order_token')
					->orderBy('orderpayment_id','DESC')
					->paginate(30);
				}
			}else{
				$paymentlist = DB::table('orderpaymentdetails')
				->select('*')
				->whereNotIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_date',[$request->from, $request->to])
				->where('status_id','=',1)
				->groupBy('order_token')
				->orderBy('orderpayment_id','DESC')
				->paginate(30);
			}
		}
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
	      'brand_id'				=> 'required',
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
		if($request->orderpaymentstatus_id == 2){
			$paymentlisttopaid = DB::table('mergepaymentdetails')
			->select('*', DB::raw("0 as topaid"))
			->whereIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->whereBetween('orderpayment_date',[$request->from, $request->to])
			->where('orderpayment_lastpaiddate','>=',date('Y-m-d'))
			->where('status_id','=',1)
			->groupBy('mergedeal_token')
			->orderBy('orderpayment_id','DESC')
			->get()->toArray();
			$paymentlisttorecovery = DB::table('mergepaymentdetails')
			->select('*', DB::raw("1 as topaid"))
			->whereIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->whereBetween('orderpayment_date',[$request->from, $request->to])
			->where('orderpayment_lastpaiddate','<',date('Y-m-d'))
			->where('status_id','=',1)
			->groupBy('mergedeal_token')
			->orderBy('orderpayment_id','DESC')
			->get()->toArray();
			$paymentlist = array_merge($paymentlisttopaid,$paymentlisttorecovery);
			rsort($paymentlist);
		}elseif($request->orderpaymentstatus_id == 7){
			$paymentlist = DB::table('mergepaymentdetails')
			->select('*')
			->whereIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->whereBetween('orderpayment_recoverydate',[$request->from, $request->to])
			->where('status_id','=',1)
			->groupBy('mergedeal_token')
			->orderBy('orderpayment_id','DESC')
			->paginate(30);
		}else{
			if($request->orderpaymentstatus_id == 3){
				$validate = Validator::make($request->all(), [ 
					'billingmerchant_id'	=> 'required',
				]);
				if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				if($request->billingmerchant_id == 0){
					$paymentlist = DB::table('mergepaymentdetails')
					->select('*')
					->whereIn('order_token', $getmergedealtoken)
					->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
					->where('brand_id','=',$request->brand_id)
					// ->where('orderpayment_pickby','=',$request->user_id)
					->whereBetween('orderpayment_date',[$request->from, $request->to])
					->where('status_id','=',1)
					->groupBy('mergedeal_token')
					->orderBy('orderpayment_id','DESC')
					->paginate(30);
				}else{
					$paymentlist = DB::table('mergepaymentdetails')
					->select('*')
					->whereIn('order_token', $getmergedealtoken)
					->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
					->where('brand_id','=',$request->brand_id)
					->where('merchant_id','=',$request->billingmerchant_id)
					// ->where('orderpayment_pickby','=',$request->user_id)
					->whereBetween('orderpayment_date',[$request->from, $request->to])
					->where('status_id','=',1)
					->groupBy('mergedeal_token')
					->orderBy('orderpayment_id','DESC')
					->paginate(30);
				}
			}else{
				$paymentlist = DB::table('mergepaymentdetails')
				->select('*')
				->whereIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_date',[$request->from, $request->to])
				->where('status_id','=',1)
				->groupBy('mergedeal_token')
				->orderBy('orderpayment_id','DESC')
				->paginate(30);
			}
		}
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist,'message' => 'Picked Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Order List'],200);
		}
	}
	public function sumpaymentamount(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'from'					=> 'required',
	      'to'						=> 'required',
		  'brand_id'				=> 'required', 
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
		if($request->orderpaymentstatus_id == 2){
			if($request->topaid == 1){
				$paymentamount = DB::table('orderpaymentdetails')
				->select('*')
				->whereNotIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_date',[$request->from, $request->to])
				// ->where('orderpayment_lastpaiddate','>=',date('Y-m-d'))
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$mergepaymentamount = DB::table('mergepaymentdetails')
				->select('*')
				->whereIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_date',[$request->from, $request->to])
				// ->where('orderpayment_lastpaiddate','>=',date('Y-m-d'))
				->where('status_id','=',1)
				->where('orderstatus','=',1)
				->sum('orderpayment_amount');
			}else{
				$paymentamount = DB::table('orderpaymentdetails')
				->select('*')
				->whereNotIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_date',[$request->from, $request->to])
				// ->where('orderpayment_lastpaiddate','<',date('Y-m-d'))
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$mergepaymentamount = DB::table('mergepaymentdetails')
				->select('*')
				->whereIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_date',[$request->from, $request->to])
				// ->where('orderpayment_lastpaiddate','<',date('Y-m-d'))
				->where('status_id','=',1)
				->where('orderstatus','=',1)
				->sum('orderpayment_amount');
			}
		}elseif($request->orderpaymentstatus_id == 7){
			if($request->topaid == 1){
				$paymentamount = DB::table('orderpaymentdetails')
				->select('*')
				->whereNotIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_recoverydate',[$request->from, $request->to])
				// ->where('orderpayment_lastpaiddate','>=',date('Y-m-d'))
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$mergepaymentamount = DB::table('mergepaymentdetails')
				->select('*')
				->whereIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_recoverydate',[$request->from, $request->to])
				// ->where('orderpayment_lastpaiddate','>=',date('Y-m-d'))
				->where('status_id','=',1)
				->where('orderstatus','=',1)
				->sum('orderpayment_amount');
			}else{
				$paymentamount = DB::table('orderpaymentdetails')
				->select('*')
				->whereNotIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_recoverydate',[$request->from, $request->to])
				// ->where('orderpayment_lastpaiddate','<',date('Y-m-d'))
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$mergepaymentamount = DB::table('mergepaymentdetails')
				->select('*')
				->whereIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->whereBetween('orderpayment_recoverydate',[$request->from, $request->to])
				// ->where('orderpayment_lastpaiddate','<',date('Y-m-d'))
				->where('status_id','=',1)
				->where('orderstatus','=',1)
				->sum('orderpayment_amount');
			}
		}else{
			$paymentamount = DB::table('orderpaymentdetails')
			->select('*')
			->whereNotIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->whereBetween('orderpayment_date',[$request->from, $request->to])
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$mergepaymentamount = DB::table('mergepaymentdetails')
			->select('*')
			->whereIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->whereBetween('orderpayment_date',[$request->from, $request->to])
			->where('status_id','=',1)
			->where('orderstatus','=',1)
			->sum('orderpayment_amount');
		}
		$sumallpaymentamount = $paymentamount+$mergepaymentamount;
		return response()->json(['paymentamount' => $paymentamount, 'mergepaymentamount' => $mergepaymentamount, 'sumallpaymentamount' => $sumallpaymentamount,'message' => 'Billing Payment'],200);
	}
	public function pickpayment(Request $request){
		if($request->picktype == "All"){
			$validate = Validator::make($request->all(), [ 
				'order_token'	=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json("Order Token Required", 400);
			}
			$update  = DB::table('orderpayment')
			->whereIn('order_token',$request->order_token)
			->update([
				'orderpayment_pickby'	=> $request->user_id,
				'orderpaymentstatus_id'	=> 9,
			]); 
		}else{
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
		}
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
			$ordertoken = DB::table('mergedeal')
			->where('mergedeal_token','=',$request->order_id)
			->select('order_token')
			->get();
			$sorttokens = array();
			foreach($ordertoken as $ordertokens){
				$sorttokens[] = $ordertokens->order_token;
			}
			$paymentdetail = DB::table('mergepaymentdetails')
			->where('mergedeal_token','=',$request->order_id)
			->where('orderstatus','=',1)
			->select('*')
			->get();
			$sumpaymentamount = DB::table('mergepaymentdetails')
			->where('mergedeal_token','=',$request->order_id)
			->where('orderstatus','=',1)
			->select('orderpayment_amount')
			->sum('orderpayment_amount');
			$getleadid = DB::table('order')
			->where('order_token','=',$sorttokens[0])
			->select('lead_id')
			->first();
		}else{
			$paymentdetail = DB::table('orderpaymentdetails')
			->where('order_token','=',$request->order_id)
			->where('status_id','=',1)
			->select('*')
			->get();
			$sumpaymentamount = DB::table('orderpaymentdetails')
			->where('order_token','=',$request->order_id)
			->where('status_id','=',1)
			->select('orderpayment_amount')
			->sum('orderpayment_amount');
			$getleadid = DB::table('order')
			->where('order_token','=',$request->order_id)
			->select('lead_id')
			->first();
		}
		$order_token = $request->order_id;
		$getleaddetail = DB::table('lead')
		->where('lead_id','=',$getleadid->lead_id)
		->select('brand_id','lead_name','lead_email','lead_phone','lead_bussinessname')
		->first();
		$getbranddetail = DB::table('brand')
		->where('brand_id','=',$getleaddetail->brand_id)
		->select('brand_cover','brand_email','brand_website','brand_invoicename','brand_currency')
		->first();
		$coverpath = URL::to('/')."/public/brand_cover/";
		$inviceinfo = array(
			'sumpaymentamount' 		=> $sumpaymentamount,
			'lead_name' 			=> $getleaddetail->lead_name,
			'lead_email' 			=> $getleaddetail->lead_email,
			'lead_phone' 			=> $getleaddetail->lead_phone,
			'lead_bussinessname' 	=> $getleaddetail->lead_bussinessname,
			'brand_email' 			=> $getbranddetail->brand_email,
			'brand_website' 		=> $getbranddetail->brand_website,
			'brand_invoicename' 	=> $getbranddetail->brand_invoicename,
			'brand_currency' 		=> $getbranddetail->brand_currency == 1 ? "$" : " £",
			'order_token' 			=> $order_token,
			'brand_cover' 			=> $getbranddetail->brand_cover,
			'brand_coverpath' 		=> $coverpath,
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
			}elseif ($request->orderpaymentstatus_id == 3) {
				$update  = DB::table('orderpayment')
				->whereIn('order_token',$token)
				->update([
					'orderpaymentstatus_id'		=> $request->orderpaymentstatus_id,
					'orderpayment_paiddate'		=> date('Y-m-d'),
				]); 	
			}elseif ($request->orderpaymentstatus_id == 7) {
				$update  = DB::table('orderpayment')
				->whereIn('order_token',$token)
				->update([
					'orderpaymentstatus_id'			=> $request->orderpaymentstatus_id,
					'orderpayment_recoverydate'		=> date('Y-m-d'),
				]); 	
			}elseif ($request->orderpaymentstatus_id == 10) {
				$validate = Validator::make($request->all(), [ 
			      'orderpayment_callbackcomment'	=> 'required',
				  'orderpayment_callbackdate'		=> 'required',
			    ]);
		     	if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				$update  = DB::table('orderpayment')
				->whereIn('order_token',$token)
				->update([
					'orderpayment_callbackcomment'	=> $request->orderpayment_callbackcomment,
					'orderpayment_callbackdate'		=> $request->orderpayment_callbackdate,
					'orderpaymentstatus_id'			=> $request->orderpaymentstatus_id,
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
				if($request->isEdit == 1){
					$validate = Validator::make($request->all(), [ 
						'orderpayment_invoiceno'	=> 'required',
						'merchant_id'				=> 'required',
						]);
						if ($validate->fails()) {
							return response()->json($validate->errors(), 400);
						}
						$update  = DB::table('orderpayment')
						->where('order_token','=',$request->order_id)
						->update([
							'orderpayment_invoiceno'	=> $request->orderpayment_invoiceno,
							'merchant_id'				=> $request->merchant_id,
							'orderpaymentstatus_id'		=> $request->orderpaymentstatus_id,
						]); 
				}else{
					$validate = Validator::make($request->all(), [ 
					'orderpayment_invoiceno'	=> 'required',
					'merchant_id'				=> 'required',
					]);
					if ($validate->fails()) {
						return response()->json($validate->errors(), 400);
					}
					$update  = DB::table('orderpayment')
					->where('order_token','=',$request->order_id)
					->update([
						'merchant_id'				=> $request->merchant_id,
						'orderpayment_invoiceno'	=> $request->orderpayment_invoiceno,
						'orderpaymentstatus_id'		=> $request->orderpaymentstatus_id,
					]); 
				}	
			}elseif ($request->orderpaymentstatus_id == 3) {
				$update  = DB::table('orderpayment')
				->where('order_token','=',$request->order_id)
				->update([
					'orderpaymentstatus_id'	=> $request->orderpaymentstatus_id,
					'orderpayment_paiddate'	=> date('Y-m-d'),
				]); 
			}elseif ($request->orderpaymentstatus_id == 7) {
				$update  = DB::table('orderpayment')
				->where('order_token','=',$request->order_id)
				->update([
					'orderpaymentstatus_id'		=> $request->orderpaymentstatus_id,
					'orderpayment_recoverydate'	=> date('Y-m-d'),
				]); 
			}elseif ($request->orderpaymentstatus_id == 4) {
				$validate = Validator::make($request->all(), [ 
			      'orderpayment_comment'	=> 'required',
			    ]);
		     	if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				$update  = DB::table('orderpayment')
				->where('order_token','=',$request->order_id)
				->update([
					'orderpayment_comment'	=> $request->orderpayment_comment,
					'orderpaymentstatus_id'	=> $request->orderpaymentstatus_id,
				]); 
			}elseif ($request->orderpaymentstatus_id == 10) {
				$validate = Validator::make($request->all(), [ 
					'orderpayment_callbackcomment'	=> 'required',
					'orderpayment_callbackdate'		=> 'required',
				]);
				if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				$update  = DB::table('orderpayment')
				->where('order_token','=',$request->order_id)
				->update([
					'orderpayment_callbackcomment'	=> $request->orderpayment_callbackcomment,
					'orderpayment_callbackdate'		=> $request->orderpayment_callbackdate,
					'orderpaymentstatus_id'			=> $request->orderpaymentstatus_id,
				]);
			}else{	
				$update  = DB::table('orderpayment')
				->where('order_token','=',$request->order_id)
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
			->where('mergedeal_token','=',$request->order_id)
			->where('status_id','=',1)
			->get();
			$getmergedealtoken = array();
			foreach ($getmergedeal as $getmergedeals) {
				$getmergedealtoken[] = $getmergedeals->order_token;
			}	
			$paymentamount = DB::table('orderpayment')
			->select('orderpayment_amount')
			->whereIn('order_token',$getmergedealtoken)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
		}else{
			$paymentamount = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('order_token','=',$request->order_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
		}
		if($paymentamount){
			return response()->json(['totalamount' => $paymentamount,'message' => 'Order Payment Amount'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function multiupdatepaymentstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'orderpaymentstatus_id'	=> 'required',
	      'order_token'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$update  = DB::table('orderpayment')
		->whereIn('order_token',$request->order_token)
		->update([
			'orderpaymentstatus_id'	=> $request->orderpaymentstatus_id,
		]); 
		if($update){
			return response()->json(['message' => 'Payment Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function savebillingorderfollowup(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'billingfollowup_comment'	=> 'required',
	      'token'					=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds = array(
		'billingfollowup_comment' 	=> $request->billingfollowup_comment,
		'token' 					=> $request->token,
		'status_id'		 			=> 1,
		'created_by'	 			=> $request->user_id,
		'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('billingfollowup')->insert($adds);
		if($save){
			return response()->json(['message' => 'Followup Saved Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function billingorderfollowuplist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'token'	=> 'required',
	    ]);
	 	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$dealfollowup = DB::table('billingfollowupdetail')
		->select('*')
		->where('token','=',$request->token)
		->where('status_id','=',1)
		->get();
		if($dealfollowup){
			return response()->json(['data' => $dealfollowup,'message' => 'Followup List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function oldcrmbillingpaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
			'payment_type'	=> 'required',
		]);
		if ($validate->fails()) {    
			  return response()->json($validate->errors(), 400);
		}
		if($request->payment_type == "Paid"){
			$ordertoken = DB::table('oldcrmpaidpayment')
			->select('order_token')
			->where('status_id','=',1)
			->get();
			$sortordertoken = array();
			foreach($ordertoken as $ordertokens){
				$sortordertoken[] = $ordertokens->order_token;
			}
			$getmergeorderlist = DB::connection('mysql3')->table('completedeallist')
			->select('*')
			->whereIn('order_token',$sortordertoken)
			->where('status_id','=',1)
			->get();
			$sumorderlist = DB::connection('mysql3')->table('completedeallist')
			->select('*')
			->whereIn('order_token',$sortordertoken)
			->where('status_id','=',1)
			->sum('order_amountquoted');
		}else{
			$ordertoken = DB::table('oldcrmpaidpayment')
			->select('order_token')
			->where('status_id','=',1)
			->get();
			$sortordertoken = array();
			foreach($ordertoken as $ordertokens){
				$sortordertoken[] = $ordertokens->order_token;
			}
			$getmergeorderlist = DB::connection('mysql3')->table('completedeallist')
			->select('*')
			->whereNotIn('order_token',$sortordertoken)
			->whereIn('orderstatus_id',[8,9,10])
			->where('status_id','=',1)
			->get();
			$sumorderlist = DB::connection('mysql3')->table('completedeallist')
			->select('*')
			->whereNotIn('order_token',$sortordertoken)
			->whereIn('orderstatus_id',[8,9,10])
			->where('status_id','=',1)
			->sum('order_amountquoted');
		}
		
		if($getmergeorderlist){
			return response()->json(['data' => $getmergeorderlist, 'sumorderlist' => $sumorderlist,'message' => 'Old CRM Billing Payment List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function markpaidonoldcrmpayment(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_token'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds = array(
		'order_token' 	=> $request->order_token,
		'status_id'		=> 1,
		'created_by'	=> $request->user_id,
		'created_at'	=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('oldcrmpaidpayment')->insert($adds);
		if($save){
			return response()->json(['message' => 'Paid Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function searchpayment(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'search_type'				=> 'required',
	      'brand_id'				=> 'required', 
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
		if($request->orderpaymentstatus_id == 2){
			$paymentlisttopaid = DB::table('orderpaymentdetails')
			->select('*', DB::raw("0 as topaid"))
			->whereNotIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->where( $request->search_type, 'like', '%'.$request->search_name.'%' )
			->where('orderpayment_lastpaiddate','>=',date('Y-m-d'))
			->where('status_id','=',1)
			->groupBy('order_token')
			->orderBy('orderpayment_id','DESC')
			->get()->toArray();	
			$paymentlisttorecovery = DB::table('orderpaymentdetails')
			->select('*', DB::raw("1 as topaid"))
			->whereNotIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->where( $request->search_type, 'like', '%'.$request->search_name.'%' )
			->where('orderpayment_lastpaiddate','<',date('Y-m-d'))
			->where('status_id','=',1)
			->groupBy('order_token')
			->orderBy('orderpayment_id','DESC')
			->get()->toArray();	
			$paymentlist = array_merge($paymentlisttopaid,$paymentlisttorecovery);
			rsort($paymentlist);
		}elseif($request->orderpaymentstatus_id == 7){
			$paymentlist = DB::table('orderpaymentdetails')
			->select('*')
			->whereNotIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->where( $request->search_type, 'like', '%'.$request->search_name.'%' )
			->where('status_id','=',1)
			->groupBy('order_token')
			->orderBy('orderpayment_id','DESC')
			->get();
		}else{
			if($request->orderpaymentstatus_id == 3){
				$validate = Validator::make($request->all(), [ 
					'billingmerchant_id'	=> 'required',
				]);
				if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				if($request->billingmerchant_id == 0){
					$paymentlist = DB::table('orderpaymentdetails')
					->select('*')
					->whereNotIn('order_token', $getmergedealtoken)
					->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
					->where('brand_id','=',$request->brand_id)
					// ->where('orderpayment_pickby','=',$request->user_id)
					->where( $request->search_type, 'like', '%'.$request->search_name.'%' )
					->where('status_id','=',1)
					->groupBy('order_token')
					->orderBy('orderpayment_id','DESC')
					->get();
				}else{
					$paymentlist = DB::table('orderpaymentdetails')
					->select('*')
					->whereNotIn('order_token', $getmergedealtoken)
					->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
					->where('brand_id','=',$request->brand_id)
					->where('merchant_id','=',$request->billingmerchant_id)
					// ->where('orderpayment_pickby','=',$request->user_id)
					->where( $request->search_type, 'like', '%'.$request->search_name.'%' )
					->where('status_id','=',1)
					->groupBy('order_token')
					->orderBy('orderpayment_id','DESC')
					->get();
				}
			}else{
				$paymentlist = DB::table('orderpaymentdetails')
				->select('*')
				->whereNotIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->where( $request->search_type, 'like', '%'.$request->search_name.'%' )
				->where('status_id','=',1)
				->groupBy('order_token')
				->orderBy('orderpayment_id','DESC')
				->get();
			}
		}
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist,'message' => 'Picked Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Order List'],200);
		}
	}
	public function searchmergepayment(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'search_type'				=> 'required',
	      'brand_id'				=> 'required',
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
		if($request->orderpaymentstatus_id == 2){
			$paymentlisttopaid = DB::table('mergepaymentdetails')
			->select('*', DB::raw("0 as topaid"))
			->whereIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->where( $request->search_type, 'like', '%'.$request->searchname.'%' )
			->where('orderpayment_lastpaiddate','>=',date('Y-m-d'))
			->where('status_id','=',1)
			->groupBy('mergedeal_token')
			->orderBy('orderpayment_id','DESC')
			->get()->toArray();
			$paymentlisttorecovery = DB::table('mergepaymentdetails')
			->select('*', DB::raw("1 as topaid"))
			->whereIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->where( $request->search_type, 'like', '%'.$request->searchname.'%' )
			->where('orderpayment_lastpaiddate','<',date('Y-m-d'))
			->where('status_id','=',1)
			->groupBy('mergedeal_token')
			->orderBy('orderpayment_id','DESC')
			->get()->toArray();
			$paymentlist = array_merge($paymentlisttopaid,$paymentlisttorecovery);
			rsort($paymentlist);
		}elseif($request->orderpaymentstatus_id == 7){
			$paymentlist = DB::table('mergepaymentdetails')
			->select('*')
			->whereIn('order_token', $getmergedealtoken)
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('brand_id','=',$request->brand_id)
			// ->where('orderpayment_pickby','=',$request->user_id)
			->where( $request->search_type, 'like', '%'.$request->searchname.'%' )
			->where('status_id','=',1)
			->groupBy('mergedeal_token')
			->orderBy('orderpayment_id','DESC')
			->get();
		}else{
			if($request->orderpaymentstatus_id == 3){
				$validate = Validator::make($request->all(), [ 
					'billingmerchant_id'	=> 'required',
				]);
				if ($validate->fails()) {
					return response()->json($validate->errors(), 400);
				}
				if($request->billingmerchant_id == 0){
					$paymentlist = DB::table('mergepaymentdetails')
					->select('*')
					->whereIn('order_token', $getmergedealtoken)
					->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
					->where('brand_id','=',$request->brand_id)
					// ->where('orderpayment_pickby','=',$request->user_id)
					->where( $request->search_type, 'like', '%'.$request->searchname.'%' )
					->where('status_id','=',1)
					->groupBy('mergedeal_token')
					->orderBy('orderpayment_id','DESC')
					->get();
				}else{
					$paymentlist = DB::table('mergepaymentdetails')
					->select('*')
					->whereIn('order_token', $getmergedealtoken)
					->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
					->where('brand_id','=',$request->brand_id)
					->where('merchant_id','=',$request->billingmerchant_id)
					// ->where('orderpayment_pickby','=',$request->user_id)
					->where( $request->search_type, 'like', '%'.$request->searchname.'%' )
					->where('status_id','=',1)
					->groupBy('mergedeal_token')
					->orderBy('orderpayment_id','DESC')
					->get();
				}
			}else{
				$paymentlist = DB::table('mergepaymentdetails')
				->select('*')
				->whereIn('order_token', $getmergedealtoken)
				->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
				->where('brand_id','=',$request->brand_id)
				// ->where('orderpayment_pickby','=',$request->user_id)
				->where( $request->search_type, 'like', '%'.$request->searchname.'%' )
				->where('status_id','=',1)
				->groupBy('mergedeal_token')
				->orderBy('orderpayment_id','DESC')
				->get();
			}
		}
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist,'message' => 'Picked Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Order List'],200);
		}
	}
	public function savenetsalary(Request $request){
		$validate = Validator::make($request->all(), [ 
			'netsalary_amount'	=> 'required',
			'netsalary_month'	=> 'required',
		]);
		if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		$adds = array(
		'netsalary_amount' 	=> $request->netsalary_amount,
		'netsalary_month' 	=> $request->netsalary_month,
		'status_id'		 	=> 1,
		'created_by'	 	=> $request->user_id,
		'created_at'	 	=> date('Y-m-d h:i:s'),
		);
	    $save = DB::connection( 'mysql2' )->table('netsalary')->insert($adds);
		if($save){
			return response()->json(['message' => 'Save Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function saveexternalpayment(Request $request){
		$validate = Validator::make($request->all(), [ 
			'orderpayment_title'	=> 'required',
			'orderpayment_amount'	=> 'required',
			'orderpayment_date'		=> 'required',
			'brand_id'				=> 'required',
			'merchant_id'			=> 'required',
		]);
		if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		$order_token = openssl_random_pseudo_bytes(7);
		$order_token = bin2hex($order_token);
		$payment = array(
			'orderpayment_title'		=> $request->orderpayment_title,
			'orderpayment_amount'		=> $request->orderpayment_amount,
			'orderpayment_duedate'		=> $request->orderpayment_date,
			'orderpayment_date' 		=> $request->orderpayment_date,
			'orderpayment_lastpaiddate' => $request->orderpayment_date,
			'orderpayment_paiddate' 	=> $request->orderpayment_date,
			'order_id'					=> 0,
			'brand_id'					=> $request->brand_id,
			'lead_id'					=> 0,
			'orderpaymentstatus_id'		=> 3,
			'order_token' 				=> $order_token,
			'orderpayment_token' 		=> $order_token,
			'merchant_id' 				=> $request->merchant_id,
			'orderpayment_pickby' 		=> $request->user_id,
			'status_id' 				=> 1,
			'created_by'				=> $request->user_id,
			'created_at'				=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('orderpayment')->insert($payment);
		if($save){
			return response()->json(['message' => 'External Payment Save Successfully'],200);
		}else{
			return response()->json("Oops! Something went wrong", 400);
		}
	}
	public function externalpaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'brand_id'				=> 'required',
		]);
     	if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		$paymentlist = DB::table('orderpayment')
		->select('*')
		->where('orderpaymentstatus_id','=',3)
		->where('brand_id','=',$request->brand_id)
		->where('order_id','=',0)
		->where('lead_id','=',0)
		->where('status_id','=',1)
		->orderBy('orderpayment_id','DESC')
		->get();
			
		
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist,'message' => 'External Payment List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'External Payment List'],200);
		}
	}
}