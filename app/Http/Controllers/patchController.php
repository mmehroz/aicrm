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

class patchController extends Controller
{
	public $emptyarray = array();
    public function patchtype(Request $request){
		$data = DB::table('patchtype')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($data)){
			return response()->json(['data' => $data, 'message' => 'Patches Type'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Patches Type'],200);
		}
	}
    public function patchback(Request $request){
		$data = DB::table('patchback')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($data)){
			return response()->json(['data' => $data, 'message' => 'Patches Back'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Patches Back'],200);
		}
	}
	public function createpatchorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'patchtype_id' 		    => 'required',
	      'patchback_id'		    => 'required',
	      'patch_title'	            => 'required',
	      'patch_height'		    => 'required',
	      'patch_width' 	        => 'required',
	      'patch_quantity'	        => 'required',
          'patch_amount'	        => 'required',
          'patch_deliverycost'	    => 'required',
		  'patch_vendorcost'	    => 'required',
		  'patch_isorderorsample'	=> 'required',
          'patch_shippingaddress'	=> 'required',
		  'vendorproduction_id'		=> 'required',
		  'vendordelivery_id'		=> 'required',
		  'brand_id'				=> 'required',
		  'lead_id'					=> 'required',
        ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
        $adds[] = array(
            'patch_title' 			=> $request->patch_title,
            'patch_height'			=> $request->patch_height,
            'patch_width' 		    => $request->patch_width,
            'patch_quantity'		=> $request->patch_quantity,
            'patch_amount' 		    => $request->patch_amount,
            'patch_deliverycost'	=> $request->patch_deliverycost,
			'patch_vendorcost'		=> $request->patch_vendorcost,
			'patch_isorderorsample'	=> $request->patch_isorderorsample,
            'patch_shippingaddress'	=> $request->patch_shippingaddress,
			'patchtype_id'			=> $request->patchtype_id,
			'patchback_id'			=> $request->patchback_id,
            'vendorproduction_id'	=> $request->vendorproduction_id,
            'vendordelivery_id'	    => $request->vendordelivery_id,
            'patch_otherdetails'	=> $request->patch_otherdetails,
			'lead_id'				=> $request->lead_id,
			'patch_date'			=> date('Y-m-d'),
			'patchstatus_id'		=> 1,
			'brand_id'				=> $request->brand_id,
            'status_id'		 		=> 1,
            'created_by'	 		=> $request->user_id,
            'created_at'	 		=> date('Y-m-d h:i:s'),
        );
        $save = DB::table('patch')->insert($adds);
        $patch_id = DB::getPdo()->lastInsertId();
        if (isset($request->patchattachment)) {
            $patchattachment = $request->patchattachment;
            $index = 0 ;
            $filename = array();
            foreach($patchattachment as $patchattachments){
                $saveattachment = array();
                if($patchattachments->isValid()){
                    $number = rand(1,999);
                    $numb = $number / 7 ;
                    $foldername = $patch_id;
                    $extension = $patchattachments->getClientOriginalExtension();
                    $filename = $patchattachments->getClientOriginalName();
                    $filename = $patchattachments->move(public_path('patch/'.$foldername),$filename);
                    $filename = $patchattachments->getClientOriginalName();
                    $saveattachment = array(
                    'patchattachment_name'	=> $filename,
                    'patch_id'				=> $patch_id,
                    'status_id' 			=> 1,
                    'created_by'			=> $request->user_id,
                    'created_at'			=> date('Y-m-d h:i:s'),
                    );
                }else{
                    return response()->json("Invalid File", 400);
                }
                DB::table('patchattachment')->insert($saveattachment);
            }
        }
       if($save){
			DB::table('lead')
			->where('lead_id','=',$request->lead_id)
			->update([
			'leadstatus_id'	=> 3,
			]);
			return response()->json(['message' => 'Patch Order Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
 	public function patchorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
			'patchstatus_id'				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->role_id <= 2){
			$data = DB::table('patch')
			->select('patch_id','patch_title','patch_date','patch_amount','patch_deliverycost')
			->where('patchstatus_id','=',$request->patchstatus_id)
			->where('status_id','=',1)
			->paginate(30);
		}elseif($request->role_id == 3){
			$brand = DB::table('userbarnd')
			->select('brand_id')
			->where('user_id','=',$request->user_id)
			->where('status_id','=',1)
			->get();
			$sortbrand = array();
			foreach($brand as $brands){
				$sortbrand[] = $brands->brand_id;
			}
			$data = DB::table('patch')
			->select('patch_id','patch_title','patch_date','patch_amount','patch_deliverycost')
			->where('patchstatus_id','=',$request->patchstatus_id)
			->whereIn('brand_id',$sortbrand)
			->where('status_id','=',1)
			->paginate(30);
		}else{
			$data = DB::table('patch')
			->select('patch_id','patch_title','patch_date','patch_amount','patch_deliverycost')
			->where('created_by','=',$request->user_id)
			->where('patchstatus_id','=',$request->patchstatus_id)
			->where('status_id','=',1)
			->paginate(30);
		}
		if(isset($data)){
			return response()->json(['data' => $data, 'message' => 'Patch Order List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Patch Order List'],200);
		}
	}
	public function patchorderdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'patch_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Patch Id Required", 400);
		}
        $data = DB::table('patchdetails')
		->select('*')
		->where('status_id','=',1)
		->where('patch_id','=',$request->patch_id)
        ->first();
		$attachments = DB::table('patchattachment')
		->select('patchattachment_id','patchattachment_name')
		->where('status_id','=',1)
		->where('patch_id','=',$request->patch_id)
        ->get();
		$patchpath = URL::to('/')."/public/patch/".$request->patch_id.'/';
		if($data){
			return response()->json(['data' => $data,'patchpath' => $patchpath, 'attachments' => $attachments, 'message' => 'Patch Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deletepatchorder(Request $request){
        $validate = Validator::make($request->all(), [ 
            'patch_id'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json("Patch Id Required", 400);
        }
		$updateuserstatus  = DB::table('patch')
		->where('patch_id','=',$request->patch_id)
		->update([
			'status_id' 	=> 2,
			'deleted_by'	=> $request->user_id,
			'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($updateuserstatus){
			return response()->json(['message' => 'Patch Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function movepatchorder(Request $request){
        $validate = Validator::make($request->all(), [ 
            'patch_id'			=> 'required',
			'patchstatus_id'	=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json("Patch Id Required", 400);
        }
		$updateuserstatus  = DB::table('patch')
		->where('patch_id','=',$request->patch_id)
		->update([
			'patchstatus_id' 	=> $request->patchstatus_id,
			'updated_by'		=> $request->user_id,
			'updated_by'		=> date('Y-m-d h:i:s'),
		]); 
		if($updateuserstatus){
			return response()->json(['message' => 'Moved Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatepatchorder(Request $request){
		$validate = Validator::make($request->all(), [ 
			'patchtype_id' 		    => 'required',
			'patchback_id'		    => 'required',
			'patch_title'	        => 'required',
			'patch_height'		    => 'required',
			'patch_width' 	        => 'required',
			'patch_quantity'	    => 'required',
			'patch_amount'	        => 'required',
			'patch_deliverycost'	=> 'required',
			'patch_vendorcost'		=> 'required',
			'patch_isorderorsample'	=> 'required',
			'patch_shippingaddress'	=> 'required',
			'vendorproduction_id'	=> 'required',
			'vendordelivery_id'		=> 'required',
			'patch_otherdetails'	=> 'required',
		]);
		if ($validate->fails()) {    
		  	return response()->json($validate->errors(), 400);
		}
		$update  = DB::table('patch')
		->where('patch_id','=',$request->patch_id)
		->update([
			'patch_title' 			=> $request->patch_title,
			'patch_height'			=> $request->patch_height,
			'patch_width' 		    => $request->patch_width,
			'patch_quantity'		=> $request->patch_quantity,
			'patch_amount' 		    => $request->patch_amount,
			'patch_deliverycost'	=> $request->patch_deliverycost,
			'patch_vendorcost'		=> $request->patch_vendorcost,
			'patch_isorderorsample'	=> $request->patch_isorderorsample,
			'patch_shippingaddress'	=> $request->patch_shippingaddress,
			'patchtype_id'			=> $request->patchtype_id,
			'patchback_id'			=> $request->patchback_id,
			'vendorproduction_id'	=> $request->vendorproduction_id,
			'vendordelivery_id'	    => $request->vendordelivery_id,
			'patch_otherdetails'	=> $request->patch_otherdetails,
			'updated_by'			=> $request->user_id,
			'updated_by'			=> date('Y-m-d h:i:s'),
		]);
		if (isset($request->patchattachment)) {
            $patchattachment = $request->patchattachment;
            $index = 0 ;
            $filename = array();
            foreach($patchattachment as $patchattachments){
                $saveattachment = array();
                if($patchattachments->isValid()){
                    $number = rand(1,999);
                    $numb = $number / 7 ;
                    $foldername = $request->patch_id;
                    $extension = $patchattachments->getClientOriginalExtension();
                    $filename = $patchattachments->getClientOriginalName();
                    $filename = $patchattachments->move(public_path('patch/'.$foldername),$filename);
                    $filename = $patchattachments->getClientOriginalName();
                    $saveattachment = array(
                    'patchattachment_name'	=> $filename,
                    'patch_id'				=> $request->patch_id,
                    'status_id' 			=> 1,
                    'created_by'			=> $request->user_id,
                    'created_at'			=> date('Y-m-d h:i:s'),
                    );
                }else{
                    return response()->json("Invalid File", 400);
                }
                DB::table('patchattachment')->insert($saveattachment);
            }
        }
		if($update){
			return response()->json(['message' => 'Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatebillingpatchstatus(Request $request){
        $validate = Validator::make($request->all(), [ 
            'patch_id'				=> 'required',
			'patch_biillingstatus'	=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
		$updateuserstatus  = DB::table('patch')
		->where('patch_id','=',$request->patch_id)
		->update([
			'patch_biillingstatus' 	=> $request->patch_biillingstatus == 1 ? "Paid" : "Cancel",
			'updated_by'			=> $request->user_id,
			'updated_by'			=> date('Y-m-d h:i:s'),
		]); 
		if($updateuserstatus){
			return response()->json(['message' => 'Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function patchbillingorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
            'patch_biillingstatus'	=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
		if($request->patch_biillingstatus == 0){
			$billingstatus = "Pending";
		}elseif($request->patch_biillingstatus == 1){
			$billingstatus = "Paid";
		}else{
			$billingstatus = "Cancel";
		}
		$data = DB::table('patch')
		->select('*')
		->where('patch_biillingstatus','=',$billingstatus)
		->where('status_id','=',1)
		->get();
		if(isset($data)){
			return response()->json(['data' => $data, 'message' => 'Billing Patch Orders List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Billing Patch Order List'],200);
		}
	}
	public function addpatchpayment(Request $request){
        $validate = Validator::make($request->all(), [ 
			'patchpayment_amount'	=> 'required',
			'patchpayment_comment'	=> 'required',
            'patch_id'				=> 'required',
			'patchpaymenttype_id'	=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
		$adds[] = array(
			'patchpayment_amount' 	=> $request->patchpayment_amount,
			'patchpayment_comment' 	=> $request->patchpayment_comment,
			'patch_id' 				=> $request->patch_id,
			'patchpaymenttype_id' 	=> $request->patchpaymenttype_id,
			'status_id'				=> 1,
			'created_by'			=> $request->user_id,
			'created_at'			=> date('Y-m-d h:i:s'),
		); 
        $save = DB::table('patchpayment')->insert($adds);
		if($save){
			return response()->json(['message' => 'Payment Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function patchpaymentlist(Request $request){
		$validate = Validator::make($request->all(), [ 
            'patch_id'	=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
		$data = DB::table('patchpaymentdetails')
		->select('*')
		->where('patch_id','=',$request->patch_id)
		->where('status_id','=',1)
		->get();
		if(isset($data)){
			return response()->json(['data' => $data, 'message' => 'Patch Payment List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Patch Payment List'],200);
		}
	}
	public function patchorderreport(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'yearmonth'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$getyearandmonth = explode('-', $setyearmonth);
		$getfirstdate = $setyearmonth."-01";
		if($yearmonth[1] == "1" || $yearmonth[1] == "3" || $yearmonth[1] == "5" || $yearmonth[1] == "7" || $yearmonth[1] == "8" || $yearmonth[1] == "10" || $yearmonth[1] == "12"){
			$noofdays = 31;
		}elseif($yearmonth[1] == "2"){
			$noofdays = 28;
		}else{
			$noofdays = 30;
		}
		$list=array();
		for($d=1; $d<=$noofdays; $d++)
		{
		    $time=mktime(12, 0, 0, $getyearandmonth[1], $d, $getyearandmonth[0]);          
		    if (date('m', $time)==$getyearandmonth[1])       
		        $list[]=date('Y-m-d', $time);
		}
		$patchorder = DB::table('patchdetails')
		->select('patch_id','user_name','patch_date','lead_name','patch_title','patch_isorderorsample','patch_quantity','delivery_vendor','production_vendor','patch_amount','patch_deliverycost','patch_vendorcost','patchstatus_name')
		->whereIn('patch_date', $list)
		->where('status_id','=',1)
		->get();
		$patchorderdata = array();
		$poindex=0;
		foreach($patchorder as $patchorders){
			$patchorders->vendorcostperpiece = $patchorders->patch_vendorcost/$patchorders->patch_quantity;
			$patchorders->paidtovendor = 0;
			$patchorders->vendorremaining = 0;
			$patchorders->paidtoshipper = 0;
			$patchorders->shipperremaining = 0;
			$patchorders->totalcost = $patchorders->patch_vendorcost+$patchorders->patch_deliverycost;
			$patchorderdata[$poindex] = $patchorders;
			$poindex++;
		}
		return response()->json(['patchorderdata' => $patchorderdata, 'message' => 'Patch Order Report'],200);
	}
}