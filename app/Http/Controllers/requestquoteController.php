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

class requestquoteController extends Controller
{
	public function sendquoterequest(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'quote_title'  	        => 'required',
	    	'quote_deadlinedate'    => 'required',
	    	'quote_minbudget'  	    => 'required',
            'quote_maxbudget'  	    => 'required',
            'quote_description'  	=> 'required',
            'quote_projecttype'  	=> 'required',
            'quote_feature'  	    => 'required',
            'quote_devplatform'  	=> 'required',
            'quote_refrence'  	    => 'required',
			'quotestatus_id'  	    => 'required',
            
	    ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$removefeaturedash = explode(',', $request->quote_feature);
		$feature = array();
		foreach($removefeaturedash as $removefeaturedashs){
			if($removefeaturedashs != "-"){
				$feature[] = $removefeaturedashs;
			}
		}
		$mergefeature = implode(',', $feature);

		$removedevplatformdash = explode(',', $request->quote_devplatform);
		$devplatform = array();
		foreach($removedevplatformdash as $removedevplatformdashs){
			if($removedevplatformdashs != "-"){
				$devplatform[] = $removedevplatformdashs;
			}
		}
		$mergedevplatform = implode(',', $devplatform);

		$removerefrencedash = explode(',', $request->quote_refrence);
		$refrence = array();
		foreach($removerefrencedash as $removerefrencedashs){
			if($removerefrencedashs != "-"){
				$refrence[] = $removerefrencedashs;
			}
		}
		$mergerefrence = implode(',', $refrence);
		$add = array(
		'quote_title'		    => $request->quote_title,
		'quote_deadlinedate'    => $request->quote_deadlinedate,
		'quote_minbudget' 		=> $request->quote_minbudget,
        'quote_maxbudget'	    => $request->quote_maxbudget,
		'quote_description'	    => $request->quote_description,
		'quote_projecttype'     => $request->quote_projecttype,
        'quote_feature'		    => $mergefeature,
		'quote_devplatform'	    => $mergedevplatform,
		'quote_refrence' 		=> $mergerefrence,
        'quote_comment' 		=> $request->quote_comment,
        'quote_date' 		    => $request->quote_date,
        'quotestatus_id' 		=> $request->quotestatus_id,
        'brand_id' 		        => $request->brand_id,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		DB::table('quote')->insert($add);
        $quote_id = DB::getPdo()->lastInsertId();
        if (isset($request->quoteattachment_name)) {
			$attachment = $request->quoteattachment_name;
	    	$index = 0 ;
	    	$filename = array();
			foreach($attachment as $attachments){
				$saveattachment = array();
	    		if($attachments->isValid()){
	    			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $quote_id;
					$extension = $attachments->getClientOriginalExtension();
		            $filename = $attachments->getClientOriginalName();
		            $filename = $attachments->move(public_path('quote/'.$foldername),$filename);
		            $filename = $attachments->getClientOriginalName();
				  	$saveattachment = array(
					'quoteattachment_name'	=> $filename,
					'quote_id'				=> $quote_id,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
	    	DB::table('quoteattachment')->insert($saveattachment);
	    	}
    	}
		if($add){
			return response()->json(['message' => 'Quote Saved Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
    public function quotelist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'brand_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
        $basic = DB::table('quote')
		->select('*')
		->where('status_id','=',1)
		->where('quotestatus_id','=',$request->quotestatus_id)
        ->where('brand_id','=',$request->brand_id)
		->paginate(30);
	    if($basic){
			return response()->json([ 'data' => $basic, 'message' => 'Quotion List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
    public function quotedetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'quote_id'		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$quotedetails = DB::table('quotedetails')
		->select('*')
		->where('quote_id','=',$request->quote_id)
		->where('status_id','=',1)
		->first();
		$assignedusersids = explode(',', $quotedetails->quote_assignedusers);
		$assignedusers = DB::table('user')
		->select('user_id','user_name')
		->whereIn('user_id',$assignedusersids)
		->where('status_id','=',1)
		->get();
		$projecttype = explode(',', $quotedetails->quote_projecttype);
		$feature = explode(',', $quotedetails->quote_feature);
		$devplatform = explode(',', $quotedetails->quote_devplatform);
		$refrence = explode(',', $quotedetails->quote_refrence);
	    $attachments = DB::table('quoteattachment')
		->select('*')
        ->where('quote_id','=',$request->quote_id)
		->where('status_id','=',1)
		->get();
		$payments = DB::table('quotepayment')
		->select('*')
        ->where('quote_id','=',$request->quote_id)
		->where('status_id','=',1)
		->get();
        $branddetails = DB::table('brand')
		->select('*')
        ->where('brand_id','=',$quotedetails->brand_id)
		->where('status_id','=',1)
		->first();
        $quotepath = URL::to('/')."/public/quote/".$request->quote_id;
		$brandlogopath = URL::to('/')."/public/brand_logo/";
		$brandcoverpath = URL::to('/')."/public/brand_cover/";
		$brandproposalpath = URL::to('/')."/public/brand_proposal/";
		if($quotedetails){
			return response()->json(['quotedetails' => $quotedetails, 'payments' => $payments, 'assignedusers' => $assignedusers, 'attachments' => $attachments, 'branddetails' => $branddetails, 'quotepath' => $quotepath, 'projecttype' => $projecttype, 'features' => $feature, 'devplatform' => $devplatform, 'refrence' => $refrence, 'brandlogopath' => $brandlogopath, 'brandcoverpath' => $brandcoverpath , 'brandproposalpath' => $brandproposalpath, 'message' => 'Quotation Details'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
    public function deletequote(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'quote_id'    => 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$delete  = DB::table('quote')
		->where('quote_id','=',$request->quote_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($delete){
			return response()->json(['message' => 'Quote Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function movequoterequest(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'quote_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {
			return response()->json($validate->error(), 400);
		}
		$updatetarget = DB::table('quote')
			->where('quote_id','=',$request->quote_id)
			->update([
			'quotestatus_id'	=> $request->quotestatus_id,
		]); 
		if($updatetarget){
			return response()->json(['message' => 'Request Move Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function updatequoterequest(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'quote_id'  	        => 'required',
			'quote_title'  	        => 'required',
	    	'quote_deadlinedate'    => 'required',
	    	'quote_minbudget'  	    => 'required',
            'quote_maxbudget'  	    => 'required',
            'quote_description'  	=> 'required',
            'quote_projecttype'  	=> 'required',
            'quote_feature'  	    => 'required',
            'quote_devplatform'  	=> 'required',
            'quote_refrence'  	    => 'required',
			'quote_comment'  	    => 'required',
			'quotestatus_id'  	    => 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->isArray == 1){
			$assignedusers = implode(',', $request->assignedusers);	
		}else{
			$assignedusers = $request->assignedusers;	
		}
		$removefeaturedash = explode(',', $request->quote_feature);
		$feature = array();
		foreach($removefeaturedash as $removefeaturedashs){
			if($removefeaturedashs != "-"){
				$feature[] = $removefeaturedashs;
			}
		}
		$mergefeature = implode(',', $feature);

		$removedevplatformdash = explode(',', $request->quote_devplatform);
		$devplatform = array();
		foreach($removedevplatformdash as $removedevplatformdashs){
			if($removedevplatformdashs != "-"){
				$devplatform[] = $removedevplatformdashs;
			}
		}
		$mergedevplatform = implode(',', $devplatform);

		$removerefrencedash = explode(',', $request->quote_refrence);
		$refrence = array();
		foreach($removerefrencedash as $removerefrencedashs){
			if($removerefrencedashs != "-"){
				$refrence[] = $removerefrencedashs;
			}
		}
		$mergerefrence = implode(',', $refrence);
		$data = array(
		'quote_title'		    => $request->quote_title,
		'quote_deadlinedate'    => $request->quote_deadlinedate,
		'quote_minbudget' 		=> $request->quote_minbudget,
        'quote_maxbudget'	    => $request->quote_maxbudget,
		'quote_description'	    => $request->quote_description,
		'quote_projecttype'     => $request->quote_projecttype,
        'quote_feature'		    => $mergefeature,
		'quote_devplatform'	    => $mergedevplatform,
		'quote_refrence' 		=> $mergerefrence,
        'quote_comment' 		=> $request->quote_comment,
		'quote_days' 			=> $request->days,
		'quote_sumcost' 		=> $request->sumcost,
		'quote_assignedusers' 	=> $assignedusers,
        'quotestatus_id' 		=> $request->quotestatus_id,
        'updated_by'	 		=> $request->user_id,
		'updated_at'	 		=> date('Y-m-d h:i:s'),
		);
		$update = DB::table('quote')
			->where('quote_id','=',$request->quote_id)
			->update($data);
		if (isset($request->quoteattachment_name)) {
			$attachment = $request->quoteattachment_name;
	    	$index = 0 ;
	    	$filename = array();
			foreach($attachment as $attachments){
				$saveattachment = array();
	    		if($attachments->isValid()){
	    			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $request->quote_id;
					$extension = $attachments->getClientOriginalExtension();
		            $filename = $attachments->getClientOriginalName();
		            $filename = $attachments->move(public_path('quote/'.$foldername),$filename);
		            $filename = $attachments->getClientOriginalName();
				  	$saveattachment = array(
					'quoteattachment_name'	=> $filename,
					'quote_id'				=> $request->quote_id,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
	    	DB::table('quoteattachment')->insert($saveattachment);
	    	}
    	}
		if (isset($request->payment)) {
			foreach ($request->payment as $payments) {
				if ($payments['quotepayment_id'] ==  "-") {
					$payment = array(
					'quotepayment_title'		=> $payments['quotepayment_title'],
					'quotepayment_description'	=> $payments['quotepayment_description'],
					'quotepayment_amount'		=> $payments['quotepayment_amount'],
					'quotepayment_duedate'		=> $payments['quotepayment_duedate'],
					'quote_id'					=> $request->quote_id,
					'status_id' 				=> 1,
					'created_by'				=> $request->user_id,
					'created_at'				=> date('Y-m-d h:i:s'),
					);
					DB::table('quotepayment')->insert($payment);	
				}else{
					DB::table('quotepayment')
					->where('quotepayment_id','=',$payments['quotepayment_id'])
					->update([
					'quotepayment_title'		=> $payments['quotepayment_title'],
					'quotepayment_description'	=> $payments['quotepayment_description'],
					'quotepayment_amount'		=> $payments['quotepayment_amount'],
					'quotepayment_duedate'		=> $payments['quotepayment_duedate'],
					'updated_by'				=> $request->user_id,
					'updated_at'				=> date('Y-m-d h:i:s'),
					]);
				}
			}
		}
		if($update){
			return response()->json(['message' => 'Quote Updated Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function chekprojectcost(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'assignedusers'	=> 'required',
		  'days'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$cost = array();
		foreach($request->assignedusers as $assigneduserss){
			$batchid = DB::table('user')
			->where('user_id','=',$assigneduserss)
			->select('user_batchid')
			->first();
			$salary = DB::connection('mysql2')->table('payrollsalaries')
			->where('EMP_BADGE_ID','=',$batchid->user_batchid)
			->select('Salary')
			->sum('Salary');
			$increment = DB::connection('mysql2')->table('increment')
			->where('elsemployees_batchid','=',$batchid->user_batchid)
			->where('status_id','=',2)
			->select('increment_amount')
			->sum('increment_amount');
			$decrement = DB::connection('mysql2')->table('decrement')
			->where('elsemployees_batchid','=',$batchid->user_batchid)
			->where('status_id','=',2)
			->select('decrement_amount')
			->sum('decrement_amount');
			$sumsalary = $salary+$increment+$decrement;
			$perdaysalary = $sumsalary/22;
			$cost[] = $perdaysalary*$request->days;
		}
		$sumcost = array_sum($cost);
		return response()->json(['sumcost' => $sumcost, 'message' => 'Project Estimated Cost'],200);
	}
	public function deletequotepayment(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'quotepayment_id'    => 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$delete  = DB::table('quotepayment')
		->where('quotepayment_id','=',$request->quotepayment_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($delete){
			return response()->json(['message' => 'Quote Payment Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}