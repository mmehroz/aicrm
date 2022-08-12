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
	      'order_title' 			=> 'required',
	      'order_deadlinedate' 		=> 'required',
	      'order_description' 		=> 'required',
	      'ordertype_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$order_token = openssl_random_pseudo_bytes(7);
    	$order_token = bin2hex($order_token);
		$basic = array(
		'order_title' 			=> $request->order_title,
		'order_deadlinedate' 	=> $request->order_deadlinedate,
		'order_description' 	=> $request->order_description,
		'order_token' 			=> $order_token,
		'ordertype_id'			=> $request->ordertype_id,
		'orderstatus_id'		=> 1,
		'status_id'				=> 1,
		'created_by'			=> $request->user_id,
		'created_at'			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('order')->insert($basic);
		$order_id = DB::getPdo()->lastInsertId();
		if (isset($request->payment)) {
			foreach ($request->payment as $payments) {
				$payment = array(
				'orderpayment_title'	=> $payments['orderpayment_title'],
				'orderpayment_amount'	=> $payments['orderpayment_amount'],
				'order_id'				=> $order_id,
				'order_token' 			=> $order_token,
				'status_id' 			=> 1,
				'created_by'			=> $request->user_id,
				'created_at'			=> date('Y-m-d h:i:s'),
				);
				DB::table('orderpayment')->insert($payment);
			}
		}
		if (isset($request->refrence)) {
			foreach ($request->refrence as $refrences) {
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
		if (isset($request->question)) {
			foreach ($request->question as $questions) {
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
		$attachment = $request->attachment;
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
		if($save){
			return response()->json(['message' => 'Order Created Successfully'],200);
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
				DB::table('orderpayment')
				->where('orderpayment_id','=',$payments['orderpayment_id'])
				->update([
				'orderpayment_title'	=> $payments['orderpayment_title'],
				'orderpayment_amount'	=> $payments['orderpayment_amount'],
				'updated_by'			=> $request->user_id,
				'updated_at'			=> date('Y-m-d h:i:s'),
				]);
			}
		}
		if (isset($request->refrence)) {
			foreach ($request->refrence as $refrences) {
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
		if (isset($request->question)) {
			foreach ($request->question as $questions) {
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
		$orderlist = DB::table('order')
		->select('order_id','order_title','order_deadlinedate','order_description','order_token')
		->where('status_id','=',1)
		->paginate(30);
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
		$orderpath = URL::to('/')."/public/order/".$basicdetail->order_token.'/';
		if($basicdetail){
			return response()->json(['basicdetail' => $basicdetail, 'paymentdetail' => $paymentdetail, 'refrencedetail' => $refrencedetail, 'qadetail' => $qadetail, 'attachmentdetail' => $attachmentdetail, 'orderpath' => $orderpath,'message' => 'Order Detail'],200);
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
}