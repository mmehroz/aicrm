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

class leadgenerateController extends Controller
{
	public $emptyarray = array();
	public function generatelead(Request $request){
		$checkemail = DB::table('lead')
		->select('lead_email')
		->where('lead_email','=',$request->lead_email)
		->where('status_id','!=',2)
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
		'lead_pickby' 			=> null,
		'lead_date' 			=> date('Y-m-d'),
		'leadstatus_id'		 	=> 1,
		'brand_id'		 		=> $request->brand_id,
		'leadtype_id'	 		=> 2,
		'status_id'		 		=> 3,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('lead')->insert($adds);
        $lead_id = DB::getPdo()->lastInsertId();
        $validate = Validator::make($request->all(), [ 
            'order_title'		    => 'required',
            'order_deadlinedate'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
          $order_token = openssl_random_pseudo_bytes(7);
          $order_token = bin2hex($order_token);
              $basic = array(
              'order_title' 		=> $request->order_title,
              'order_deadlinedate' 	=> $request->order_deadlinedate,
              'order_description' 	=> $request->order_description,
              'order_assignto'		=> $request->order_assignto,
              'order_token' 		=> $order_token,
              'order_date' 			=> date('Y-m-d'),
              'ordertype_id'		=> $request->ordertype_id,
              'lead_id'				=> $lead_id,
              'brand_id'			=> $request->brand_id,
              'orderstatus_id'		=> 2,
              'status_id'			=> 3,
              'created_by'			=> $request->user_id,
              'created_at'			=> date('Y-m-d h:i:s'),
              );
              DB::table('order')->insert($basic);
              $order_id = DB::getPdo()->lastInsertId();
              if (isset($request->payment)) {
                    foreach ($request->payment as $payments) {
                        $payment = array(
                        'orderpayment_title'	=> $payments['orderpayment_title'],
                        'orderpayment_amount'	=> $payments['orderpayment_amount'],
                        'orderpayment_duedate'	=> $payments['orderpayment_duedate'],
                        'order_id'				=> $order_id,
                        'brand_id'				=> $request->brand_id,
                        'lead_id'				=> $lead_id,
                        'order_token' 			=> $order_token,
                        'status_id' 			=> 3,
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
                      'order_id'			=> $order_id,
                      'order_token' 		=> $order_token,
                      'status_id' 			=> 3,
                      'created_by'			=> $request->user_id,
                      'created_at'			=> date('Y-m-d h:i:s'),
                      );
                      DB::table('orderrefrence')->insert($refrence);
                  }
              }
              if (isset($request->question)) {
                  foreach ($request->question as $questions) {
                      $question = array(
                      'orderqa_answer'	    => $questions['orderqa_answer'],
                      'orderquestion_id'	=> $questions['orderquestion_id'],
                      'order_id'			=> $order_id,
                      'order_token' 		=> $order_token,
                      'status_id' 		    => 3,
                      'created_by'		    => $request->user_id,
                      'created_at'		    => date('Y-m-d h:i:s'),
                      );
                      DB::table('orderqa')->insert($question);
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
                      $foldername = $order_token;
                      $extension = $attachments->getClientOriginalExtension();
                      $filename = $attachments->getClientOriginalName();
                      $filename = $attachments->move(public_path('order/'.$foldername),$filename);
                      $filename = $attachments->getClientOriginalName();
                      $saveattachment = array(
                      'orderattachment_name'	=> $filename,
                      'order_id'				=> $order_id,
                      'order_token'	    		=> $order_token,
                      'status_id' 		    	=> 3,
                      'created_by'			    => $request->user_id,
                      'created_at'			    => date('Y-m-d h:i:s'),
                      );
                  }else{
                      return response()->json("Invalid File", 400);
                  }
              DB::table('orderattachment')->insert($saveattachment);
              }
              }
            $lead = array(
                'leadgenerate_date'	=> date('Y-m-d'),
                'leadstatus_id'	    => 1,
                'lead_id'	        => $lead_id,
                'order_id'	        => $order_id,
                'brand_id'	        => $request->brand_id,
                'status_id' 	    => 1,
                'created_by'	    => $request->user_id,
                'created_at'	    => date('Y-m-d h:i:s'),
            );
            DB::table('leadgenerate')->insert($lead);
      	if($save){
			return response()->json(['message' => 'Lead Generated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}