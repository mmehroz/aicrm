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

class orderController extends Controller
{
	public $emptyarray = array();
	public function createorder(Request $request){
		$validate = Validator::make($request->all(), [ 
		  'masterOrder'		=> 'required',
	      'lead_id'			=> 'required',
		]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$order_token = openssl_random_pseudo_bytes(7);
		$order_token = bin2hex($order_token);
		$multiple = $request->masterOrder;
		$brand = DB::table('lead')
		->select('brand_id')
		->where('lead_id','=',$request->lead_id)
		->where('status_id','=',1)
		->first();
		foreach ($multiple as $multiples) {
			$basic = array(
			'order_title' 			=> $multiples['order_title'],
			'order_deadlinedate' 	=> $multiples['order_deadlinedate'],
			'order_description' 	=> $multiples['order_description'],
			'order_assignto'		=> $multiples['order_assignto'],
			'order_token' 			=> $order_token,
			'order_date' 			=> date('Y-m-d'),
			'ordertype_id'			=> $multiples['ordertype_id'],
			'lead_id'				=> $request->lead_id,
			'brand_id'				=> $brand->brand_id,
			'orderstatus_id'		=> 2,
			'status_id'				=> 1,
			'created_by'			=> $request->user_id,
			'created_at'			=> date('Y-m-d h:i:s'),
			);
			$save = DB::table('order')->insert($basic);
			$order_id = DB::getPdo()->lastInsertId();
			$recoverydate = date('Y-m-d');
			$recoverydate = date('Y-m-d', strtotime($recoverydate . "+1 months") );
			$lastpaiddate = explode('-', $recoverydate);
			$sortlastpaiddate = $lastpaiddate[0].'-'.$lastpaiddate[1].'-07';
			if (isset($multiples['payment'])) {
				foreach ($multiples['payment'] as $payments) {
					$payment = array(
					'orderpayment_title'		=> $payments['orderpayment_title'],
					'orderpayment_amount'		=> $payments['orderpayment_amount'],
					'orderpayment_duedate'		=> $payments['orderpayment_duedate'],
					'orderpayment_date' 		=> date('Y-m-d'),
					'orderpayment_lastpaiddate' => $sortlastpaiddate,
					'order_id'					=> $order_id,
					'brand_id'					=> $brand->brand_id,
					'lead_id'					=> $request->lead_id,
					'order_token' 				=> $order_token,
					'status_id' 				=> 1,
					'created_by'				=> $request->user_id,
					'created_at'				=> date('Y-m-d h:i:s'),
					);
					DB::table('orderpayment')->insert($payment);
				}
			}
			if (isset($multiples['refrence'])) {
				foreach ($multiples['refrence'] as $refrences) {
					$refrence = array(
					'orderrefrence_title'	=> $refrences['orderrefrence_title'],
					'orderrefrence_link'	=> $refrences['orderrefrence_link'],
					'order_id'				=> $order_id,
					'order_token' 			=> $order_token,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
					DB::table('orderrefrence')->insert($refrence);
				}
			}
			if (isset($multiples['question'])) {
				foreach ($multiples['question'] as $questions) {
					$question = array(
					'orderqa_answer'	=> $questions['orderqa_answer'],
					'orderquestion_id'	=> $questions['orderquestion_id'],
					'order_id'			=> $order_id,
					'order_token' 		=> $order_token,
					'status_id' 		=> 1,
					'created_by'		=> $request->user_id,
					'created_at'		=> date('Y-m-d h:i:s'),
					);
					DB::table('orderqa')->insert($question);
				}
			}
			if (isset($multiples['attachment'])) {
			$attachment = $multiples['attachment'];
			$index = 0 ;
			$filename = array();
			foreach($attachment as $attachments){
				$saveattachment = array();
				if($attachments->isValid()){
					$number = rand(1,999);
					$numb = $number / 7 ;
					$foldername = $order_token;
					$extension = $attachments->getClientOriginalExtension();
					$filename = $attachments->getClientOriginalName();
					$filename = $attachments->move(public_path('order/'.$foldername),$filename);
					$filename = $attachments->getClientOriginalName();
					$saveattachment = array(
					'orderattachment_name'	=> $filename,
					'order_id'				=> $order_id,
					'order_token'			=> $order_token,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
				}else{
					return response()->json("Invalid File", 400);
				}
			DB::table('orderattachment')->insert($saveattachment);
			}
			}
		}
		DB::table('lead')
		->where('lead_id','=',$request->lead_id)
		->update([
		'leadstatus_id'	=> 3,
		]);
		if($save){
			return response()->json(['message' => 'Order Created Successfully', 'order_token' => $order_token],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_id'	 			=> 'required',
	      'order_token'	 			=> 'required',
	      'order_title' 			=> 'required',
	      'order_deadlinedate' 		=> 'required',
	      'order_description' 		=> 'required',
	      'ordertype_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$update  = DB::table('order')
		->where('order_id','=',$request->order_id)
		->update([
		'order_title' 			=> $request->order_title,
		'order_deadlinedate' 	=> $request->order_deadlinedate,
		'order_description' 	=> $request->order_description,
		'ordertype_id'			=> $request->ordertype_id,
		'updated_by'			=> $request->user_id,
		'updated_at'			=> date('Y-m-d h:i:s'),
		]);
		if (isset($request->payment)) {
			foreach ($request->payment as $payments) {
				if ($payments['orderpayment_id'] ==  "-") {
					$payment = array(
					'orderpayment_title'	=> $payments['orderpayment_title'],
					'orderpayment_amount'	=> $payments['orderpayment_amount'],
					'orderpayment_duedate'	=> $payments['orderpayment_duedate'],
					'order_id'				=> $request->order_id,
					'order_token' 			=> $request->order_token,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
					DB::table('orderpayment')->insert($payment);	
				}else{
					DB::table('orderpayment')
					->where('orderpayment_id','=',$payments['orderpayment_id'])
					->update([
					'orderpayment_title'	=> $payments['orderpayment_title'],
					'orderpayment_amount'	=> $payments['orderpayment_amount'],
					'orderpayment_duedate'	=> $payments['orderpayment_duedate'],
					'updated_by'			=> $request->user_id,
					'updated_at'			=> date('Y-m-d h:i:s'),
					]);
				}
			}
		}
		if (isset($request->refrence)) {
			foreach ($request->refrence as $refrences) {
				if ($refrences['orderrefrence_id'] ==  "-") {
					$refrence = array(
					'orderrefrence_title'	=> $refrences['orderrefrence_title'],
					'orderrefrence_link'	=> $refrences['orderrefrence_link'],
					'order_id'				=> $request->order_id,
					'order_token' 			=> $request->order_token,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
					DB::table('orderrefrence')->insert($refrence);
				}else{
					DB::table('orderrefrence')
					->where('orderrefrence_id','=',$refrences['orderrefrence_id'])
					->update([
					'orderrefrence_title'	=> $refrences['orderrefrence_title'],
					'orderrefrence_link'	=> $refrences['orderrefrence_link'],
					'updated_by'			=> $request->user_id,
					'updated_at'			=> date('Y-m-d h:i:s'),
					]);
				}
			}
		}
		if (isset($request->question)) {
			foreach ($request->question as $questions) {
				if ($questions['orderqa_id'] ==  "-") {
					$question = array(
					'orderqa_answer'	=> $questions['orderqa_answer'],
					'orderquestion_id'	=> $questions['orderquestion_id'],
					'order_id'			=> $request->order_id,
					'order_token' 		=> $request->order_token,
					'status_id' 		=> 1,
					'created_by'		=> $request->user_id,
					'created_at'		=> date('Y-m-d h:i:s'),
					);
					DB::table('orderqa')->insert($question);
				}else{
					DB::table('orderqa')
					->where('orderqa_id','=',$questions['orderqa_id'])
					->update([
					'orderqa_answer'	=> $questions['orderqa_answer'],
					'orderquestion_id'	=> $questions['orderquestion_id'],
					'updated_by'		=> $request->user_id,
					'updated_at'		=> date('Y-m-d h:i:s'),
					]);
				}
			}
		}
		if (isset($request->attachment)) {
			$attachment = $request->attachment;
			$index = 0 ;
			$filename = array();
			foreach($attachment as $attachments){
				$saveattachment = array();
				if($attachments->isValid()){
					$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $request->order_token;
					$extension = $attachments->getClientOriginalExtension();
		            $filename = $attachments->getClientOriginalName();
		            $filename = $attachments->move(public_path('order/'.$foldername),$filename);
		            $filename = $attachments->getClientOriginalName();
				  	$saveattachment = array(
					'orderattachment_name'	=> $filename,
					'order_id'				=> $request->order_id,
					'order_token'			=> $request->order_token,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
			DB::table('orderattachment')->insert($saveattachment);
			}
    	}
		if($update){
			return response()->json(['message' => 'Order Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function orderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Brand Id Required", 400);
		}
		if ($request->role_id <= 3) {
			$orderlist = DB::table('basicorderdetail')
			->select('*')
			->groupBy('order_token')
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->orderBy('order_id','DESC')
			->paginate(30);
		}else{
			$orderlist = DB::table('basicorderdetail')
			->select('*')
			->groupBy('order_token')
			->where('brand_id','=',$request->brand_id)
			->where('orderstatus_id','<',4)
			->where('created_by','=',$request->user_id)
			->where('status_id','=',1)
			->orderBy('order_id','DESC')
			->paginate(30);
		}
		if(isset($orderlist)){
			return response()->json(['data' => $orderlist, 'message' => 'Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Order List'],200);
		}
	}
	public function orderdetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Order Id Required", 400);
		}
		$basicdetail = DB::table('basicorderdetail')
		->select('*')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->first();
		$paymentdetail = DB::table('orderpayment')
		->select('*')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->get();
		$refrencedetail = DB::table('orderrefrence')
		->select('*')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->get();
		$qadetail = DB::table('orderquestiondetail')
		->select('*')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->get();
		$attachmentdetail = DB::table('orderattachment')
		->select('*')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->get();
		$gettask = DB::table('task')
		->select('task_id','task_title','task_token')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->get();
		$sorttask = array();
		if (isset($gettask)) {
			foreach ($gettask as $gettasks) {
				$sorttask[] = $gettasks->task_id;
			}
		}
		$workattachmentdetail = DB::table('taskattachment')
		->select('*')
		->whereIn('task_id',$sorttask)
		->where('attachmenttype','=',2)
		->where('status_id','=',1)
		->get();
		$orderpath = URL::to('/')."/public/order/".$basicdetail->order_token.'/';
		$taskworkpath = URL::to('/')."/public/taskwork/";
		$countqa = count($qadetail);
		if($basicdetail){
			if($countqa == 0){
				return response()->json(['basicdetail' => $basicdetail, 'paymentdetail' => $paymentdetail, 'refrencedetail' => $refrencedetail, 'attachmentdetail' => $attachmentdetail,'workattachmentdetail' => $workattachmentdetail, 'tasklist' => $gettask, 'orderpath' => $orderpath, 'taskworkpath' => $taskworkpath,'message' => 'Order Detail'],200);
			}else{
				return response()->json(['basicdetail' => $basicdetail, 'paymentdetail' => $paymentdetail, 'refrencedetail' => $refrencedetail, 'qadetail' => $qadetail, 'attachmentdetail' => $attachmentdetail,'workattachmentdetail' => $workattachmentdetail, 'tasklist' => $gettask, 'orderpath' => $orderpath, 'taskworkpath' => $taskworkpath,'message' => 'Order Detail'],200);		
			}
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deleteorder(Request $request){
		$validate = Validator::make($request->all(), [
	      'order_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Order Id Required", 400);
		}
		$delete  = DB::table('order')
		->where('order_id','=',$request->order_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]);
		DB::table('orderpayment')
		->where('order_id','=',$request->order_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]);
		DB::table('orderrefrence')
		->where('order_id','=',$request->order_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]);
		DB::table('orderqa')
		->where('order_id','=',$request->order_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]);
		if($delete){
			return response()->json(['message' => 'Order Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function removefromorder(Request $request){
		if (isset($request->orderattachment_id)) {
			$delete  = DB::table('orderattachment')
			->where('orderattachment_id','=',$request->orderattachment_id)
			->update([
			'status_id' 	=> 2,
			'deleted_by'	=> $request->user_id,
			'deleted_at'	=> date('Y-m-d h:i:s'),
			]);
		}else if (isset($request->orderpayment_id)) {
			$delete  = DB::table('orderpayment')
			->where('orderpayment_id','=',$request->orderpayment_id)
			->update([
			'status_id' 	=> 2,
			'deleted_by'	=> $request->user_id,
			'deleted_at'	=> date('Y-m-d h:i:s'),
			]);
		}else if (isset($request->orderqa_id)) {
			$delete  = DB::table('orderqa')
			->where('orderqa_id','=',$request->orderqa_id)
			->update([
			'status_id' 	=> 2,
			'deleted_by'	=> $request->user_id,
			'deleted_at'	=> date('Y-m-d h:i:s'),
			]);
		}else if (isset($request->orderrefrence_id)) {
			$delete  = DB::table('orderrefrence')
			->where('orderrefrence_id','=',$request->orderrefrence_id)
			->update([
			'status_id' 	=> 2,
			'deleted_by'	=> $request->user_id,
			'deleted_at'	=> date('Y-m-d h:i:s'),
			]);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
		if($delete){
			return response()->json(['message' => 'Successfully Removed From Order'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function ordertotalamount(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'type'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Order Id Required", 400);
		}
		if($request->type == "Deal"){
			$validate = Validator::make($request->all(), [ 
				'order_token'	=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json("Order Token Required", 400);
			}
			$totalamount = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('order_token','=',$request->order_token)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
		}else{
			$validate = Validator::make($request->all(), [ 
				'order_id'	=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json("Order Id Required", 400);
			}
			$totalamount = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('order_id','=',$request->order_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
		}
	
		if($totalamount){
			return response()->json([ 'totalamount' => $totalamount,'message' => 'Order Total Amount'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function forwardedorderlist(Request $request){
		$orderlist = DB::table('basicorderdetail')
		->select('*')
		->groupBy('order_token')
		->where('orderstatus_id','=',$request->orderstatus_id)
		->where('order_pickby','=',null)
		->where('status_id','=',1)
		->orderBy('order_id','DESC')
		->paginate(30);
		if(isset($orderlist)){
			return response()->json(['data' => $orderlist, 'message' => 'Forwarded Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Forwarded Order List'],200);
		}
	}
	public function pickedorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'orderstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json("Order Status Id Required", 400);
		}
		if($request->orderstatus_id == 4 && $request->role_id == 6 || $request->orderstatus_id == 4 && $request->role_id == 7){
			if ($request->role_id == 10){
				$orderlist = DB::table('basicorderdetail')
				->select('*')
				->groupBy('order_token')
				->whereIn('orderstatus_id',[2,3,4])
				->where('order_pickby','=',$request->user_id)
				->where('status_id','=',1)
				->orderBy('order_id','DESC')
				->paginate(30);
			}else if ($request->role_id == 6 || $request->role_id == 7){
				$orderlist = DB::table('basicorderdetail')
				->select('*')
				->groupBy('order_token')
				->whereIn('orderstatus_id',[2,3,4])
				->where('created_by','=',$request->user_id)
				->where('status_id','=',1)
				->orderBy('order_id','DESC')
				->paginate(30);
			}else if ($request->role_id == 3){
				$brandid = DB::table('userbarnd')
				->select('brand_id')
				->where('user_id','=',$request->user_id)
				->where('status_id','=',1)
				->get();
				// dd($brandid);
				$sortbrandid = array();
				foreach ($brandid as $brandids) {
					$sortbrandid[] = $brandids->brand_id;
				}
				$orderlist = DB::table('basicorderdetail')
				->select('*')
				->groupBy('order_token')
				->whereIn('orderstatus_id',[2,3,4])
				->whereIn('brand_id',$sortbrandid)
				->where('status_id','=',1)
				->orderBy('order_id','DESC')
				->paginate(30);
			}else{
				$orderlist = DB::table('basicorderdetail')
				->select('*')
				->groupBy('order_token')
				->whereIn('orderstatus_id',[2,3,4])
				->where('status_id','=',1)
				->orderBy('order_id','DESC')
				->paginate(30);
			}
		}else{
			if ($request->role_id == 10){
				$orderlist = DB::table('basicorderdetail')
				->select('*')
				->groupBy('order_token')
				->where('orderstatus_id','=',$request->orderstatus_id)
				->where('order_pickby','=',$request->user_id)
				->where('status_id','=',1)
				->orderBy('order_id','DESC')
				->paginate(30);
			}else if ($request->role_id == 6 || $request->role_id == 7){
				$orderlist = DB::table('basicorderdetail')
				->select('*')
				->groupBy('order_token')
				->where('orderstatus_id','=',$request->orderstatus_id)
				->where('created_by','=',$request->user_id)
				->where('status_id','=',1)
				->orderBy('order_id','DESC')
				->paginate(30);
			}else if ($request->role_id == 3){
				$brandid = DB::table('userbarnd')
				->select('brand_id')
				->where('user_id','=',$request->user_id)
				->where('status_id','=',1)
				->get();
				// dd($brandid);
				$sortbrandid = array();
				foreach ($brandid as $brandids) {
					$sortbrandid[] = $brandids->brand_id;
				}
				$orderlist = DB::table('basicorderdetail')
				->select('*')
				->groupBy('order_token')
				->where('orderstatus_id','=',$request->orderstatus_id)
				->whereIn('brand_id',$sortbrandid)
				->where('status_id','=',1)
				->orderBy('order_id','DESC')
				->paginate(30);
			}else{
				$orderlist = DB::table('basicorderdetail')
				->select('*')
				->groupBy('order_token')
				->where('orderstatus_id','=',$request->orderstatus_id)
				->where('status_id','=',1)
				->orderBy('order_id','DESC')
				->paginate(30);
			}
		}
		if(isset($orderlist)){
			return response()->json(['data' => $orderlist,'message' => 'Picked Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Order List'],200);
		}
	}
	public function pickorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_token'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Lead Id Required", 400);
		}
		$update  = DB::table('order')
		->where('order_token','=',$request->order_token)
		->update([
			'order_pickby'		=> $request->user_id,
			'orderstatus_id'	=> 3,
		]); 
		if($update){
			return response()->json(['message' => 'Order Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function unpickorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_token'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Oredr Id Required", 400);
		}
		$update  = DB::table('order')
		->where('order_token','=',$request->order_token)
		->update([
			'order_pickby'		=> null,
			'orderstatus_id'	=> 2,
		]); 
		if($update){
			return response()->json(['message' => 'Order Un-Pick Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateorderstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'orderstatus_id'	=> 'required',
		  'type'			=> 'required',
		]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->type == "Deal"){
			$validate = Validator::make($request->all(), [ 
			'order_token'			=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json($validate->errors(), 400);
			}
			$update  = DB::table('order')
			->where('order_token','=',$request->order_token)
			->update([
				'orderstatus_id'	=> $request->orderstatus_id,
			]); 
		}else{
			$validate = Validator::make($request->all(), [ 
			'order_id'				=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json($validate->errors(), 400);
			}
			$update  = DB::table('order')
			->where('order_id','=',$request->order_id)
			->update([
				'orderstatus_id'	=> $request->orderstatus_id,
			]); 
		}
		if($update){
			return response()->json(['message' => 'Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function orderprogress(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'type'		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Type Required", 400);
		}
		if($request->type == "Deal"){
			$validate = Validator::make($request->all(), [ 
				'order_token'	=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json("Order Token Required", 400);
			}
			$totaltask = DB::table('task')
			->select('task_id')
			->where('order_token','=',$request->order_token)
			->where('status_id','=',1)
			->count();
			$completetask = DB::table('task')
			->select('task_id')
			->where('taskstatus_id','>=',3)
			->where('order_token','=',$request->order_token)
			->where('status_id','=',1)
			->count();
		}else{
			$validate = Validator::make($request->all(), [ 
				'order_id'	=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json("Order Id Required", 400);
			}
			$totaltask = DB::table('task')
			->select('task_id')
			->where('order_id','=',$request->order_id)
			->where('status_id','=',1)
			->count();
			$completetask = DB::table('task')
			->select('task_id')
			->where('taskstatus_id','>=',3)
			->where('order_id','=',$request->order_id)
			->where('status_id','=',1)
			->count();
		}
		$progresspercent = $completetask/$totaltask*100;
		// if($totaltask){
			return response()->json(['totaltask' => $totaltask,'completetask' => $completetask, 'progresspercent' => $progresspercent, 'message' => 'Order Progress'],200);
		// }else{
		// 	return response()->json("Oops! Something Went Wrong", 400);
		// }
	}
	public function orderpaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'orderpaymentstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Orderstatus Id Required", 400);
		}
		if ($request->role_id <= 2) {
			$paymentlist = DB::table('orderpaymentdetails')
			->select('*')
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('status_id','=',1)
			->orderBy('orderpayment_duedate','DESC')
			->paginate(30);	
		}elseif ($request->role_id == 3) {
			$brandid = DB::table('userbarnd')
			->select('brand_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->get();
			$sortbrandid = array();
			foreach ($brandid as $brandids) {
				$sortbrandid[] = $brandids->brand_id;
			}
			$paymentlist = DB::table('orderpaymentdetails')
			->select('*')
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->whereIn('brand_id',$sortbrandid)
			->where('status_id','=',1)
			->orderBy('orderpayment_duedate','DESC')
			->paginate(30);	
		}else{
			$paymentlist = DB::table('orderpaymentdetails')
			->select('*')
			->where('orderpaymentstatus_id','=',$request->orderpaymentstatus_id)
			->where('created_by','=',$request->user_id)
			->where('status_id','=',1)
			->orderBy('orderpayment_duedate','DESC')
			->paginate(30);	
		}
		if(isset($paymentlist)){
			return response()->json(['data' => $paymentlist, 'message' => 'Order Payment List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Order Payment List'],200);
		}
	}
	public function updateorderpaymentstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'orderpayment_id'			=> 'required',
	      'orderpaymentstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$update  = DB::table('orderpayment')
		->where('orderpayment_id','=',$request->orderpayment_id)
		->update([
			'orderpaymentstatus_id'	=> $request->orderpaymentstatus_id,
		]); 
		if($update){
			return response()->json(['message' => 'Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function grouporderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_token'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json("Order Token Required", 400);
		}
		$orderlist = DB::table('basicorderdetail')
		->select('*')
		->where('order_token','=',$request->order_token)
		->where('status_id','=',1)
		->orderBy('order_id','DESC')
		->get();
		if(isset($orderlist)){
			return response()->json(['data' => $orderlist,'message' => 'Picked Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Picked Order List'],200);
		}
	}
	public function clientwiseorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'lead_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Lead Id Required", 400);
		}
		$personaldetails = DB::table('leadcompletedetails')
		->select('*')
		->where('lead_id','=',$request->lead_id)
		->where('status_id','=',1)
		->first();
		$orderlist = DB::table('basicorderdetail')
		->select('*')
		->groupBy('order_token')
		->where('lead_id','=',$request->lead_id)
		->where('status_id','=',1)
		->orderBy('order_id','DESC')
		->paginate(30);
		if(isset($personaldetails)){
			return response()->json(['personaldetails' => $personaldetails, 'orderlist' => $orderlist, 'message' => 'Client Area'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Client Area'],200);
		}
	}
	public function previousorderhistory(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'oldorder_clientemail'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Client Email Is Required", 400);
		}
		$orderlist = DB::table('oldorder')
		->select('*')
		->where('oldorder_clientemail','=',$request->oldorder_clientemail)
		->orderBy('oldorder_id','DESC')
		->paginate(30);
		if(isset($orderlist)){
			return response()->json(['data' => $orderlist, 'message' => 'Previous Order History'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Previous Order History'],200);
		}
	}
	public function updatemultiorderstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'orderstatus_id'	=> 'required',
		  'type'			=> 'required',
		]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->type == "Deal"){
			$validate = Validator::make($request->all(), [ 
			'order_token'			=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json($validate->errors(), 400);
			}
			$update  = DB::table('order')
			->whereIn('order_token',$request->order_token)
			->update([
				'orderstatus_id'	=> $request->orderstatus_id,
			]); 
		}else{
			$validate = Validator::make($request->all(), [ 
			'order_id'				=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json($validate->errors(), 400);
			}
			$update  = DB::table('order')
			->whereIn('order_id',$request->order_id)
			->update([
				'orderstatus_id'	=> $request->orderstatus_id,
			]); 
		}
		if($update){
			return response()->json(['message' => 'Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}