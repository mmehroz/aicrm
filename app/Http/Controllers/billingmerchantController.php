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

class billingmerchantController extends Controller
{
	public function addbillingmerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
            'brand'                          => 'required',
            'billingmerchant_logo'              => 'required',
            'billingmerchant_title' 			=> 'required',
            'billingmerchant_email'		        => 'required',
            'billingmerchant_website' 			=> 'required',
            'billingmerchant_openingbalance'	=> 'required',
            'billingmerchant_fee' 			    => 'required',
            'billingmerchant_otherinfo'		    => 'required',
		]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
        $validateunique = Validator::make($request->all(), [ 
            'billingmerchant_email' => 'unique:billingmerchant,billingmerchant_email',
        ]);
        if ($validateunique->fails()) {    
            return response()->json($validate->errors(), 400);
        }
        $validatelogo = Validator::make($request->all(), [ 
            'billingmerchant_logo' => 'mimes:jpeg,bmp,png,jpg|max:5120',
        ]);
        if ($validatelogo->fails()) {    
            return response()->json("Invalid Format", 400);
        }
        $billingmerchantlogo;
        if ($request->has('billingmerchant_logo')) {
            if( $request->billingmerchant_logo->isValid()){
                $number = rand(1,999);
                $numb = $number / 7 ;
                $name = "logo";
                $extension = $request->billingmerchant_logo->extension();
                $billingmerchantlogo  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
                $billingmerchantlogo = $request->billingmerchant_logo->move(public_path('billingmerchantlogo/'),$billingmerchantlogo);
                $img = Image::make($billingmerchantlogo)->resize(800,800, function($constraint) {
                        $constraint->aspectRatio();
                });
                $img->save($billingmerchantlogo);
                $billingmerchantlogo = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
            }else{
                return response()->json("Invalid Format", 400);
            }
        }
        foreach($request->brand as $brand_id){
            $adds[] = array(
                'billingmerchant_logo'		        => $billingmerchantlogo,
                'billingmerchant_title' 	        => $request->billingmerchant_title,
                'billingmerchant_email' 	        => $request->billingmerchant_email,
                'billingmerchant_website' 	        => $request->billingmerchant_website,
                'billingmerchant_openingbalance'	=> $request->billingmerchant_openingbalance,
                'billingmerchant_fee'		 		=> $request->billingmerchant_fee,
                'billingmerchant_otherinfo' 		=> $request->billingmerchant_otherinfo,
                'brand_id'                		    => $brand_id,
                'status_id'		 			        => 1,
                'created_by'	 			        => $request->user_id,
                'created_at'	 			        => date('Y-m-d h:i:s'),
            );
        }
        	$save = DB::table('billingmerchant')->insert($adds);
		if($save){
			return response()->json(['message' => 'Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatebillingmerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
            'billingmerchant_title' 			=> 'required',
            'billingmerchant_email'		        => 'required',
            'billingmerchant_website' 			=> 'required',
            'billingmerchant_openingbalance'	=> 'required',
            'billingmerchant_fee' 			    => 'required',
            'billingmerchant_otherinfo'		    => 'required',
		]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
        $getemail = DB::table('billingmerchant')
        ->where('billingmerchant_id','=',$request->billingmerchant_id)
        ->select('billingmerchant_email')
        ->first();
        if(isset($getemail->billingmerchant_email)){
            if ($getemail->billingmerchant_email != $request->billingmerchant_email) {
                $validateunique = Validator::make($request->all(), [ 
                    'billingmerchant_email' => 'unique:billingmerchant,billingmerchant_email',
                ]);
                if ($validateunique->fails()) {    
                    return response()->json("Email Already Exist", 400);
                }
            }
        }
        if(isset($request->billingmerchant_logo)){
            $validatelogo = Validator::make($request->all(), [ 
                'billingmerchant_logo'=>'mimes:jpeg,bmp,png,jpg|max:5120|required',
            ]);
            if ($validatelogo->fails()) {    
                return response()->json("Invalid Format", 400);
            }
        $billingmerchantlogo;
        if( $request->billingmerchant_logo->isValid()){
            $number = rand(1,999);
            $numb = $number / 7 ;
            $name = "logo";
            $extension = $request->billingmerchant_logo->extension();
            $billingmerchantlogo  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
            $billingmerchantlogo = $request->billingmerchant_logo->move(public_path('billingmerchantlogo/'),$billingmerchantlogo);
            $img = Image::make($billingmerchantlogo)->resize(800,800, function($constraint) {
                    $constraint->aspectRatio();
            });
            $img->save($billingmerchantlogo);
            $billingmerchantlogo = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
            DB::table('billingmerchant')
			->where('billingmerchant_id','=',$request->billingmerchant_id)
			->update([
			'billingmerchant_logo'			=> $billingmerchantlogo,
			]); 
        }else{
            return response()->json("Invalid Format", 400);
        }
        }
        $update  = DB::table('billingmerchant')
		->where('billingmerchant_id','=',$request->billingmerchant_id)
		->update([
            'billingmerchant_title' 			=> $request->billingmerchant_title,
            'billingmerchant_email' 	        => $request->billingmerchant_email,
            'billingmerchant_website' 			=> $request->billingmerchant_website,
            'billingmerchant_openingbalance'	=> $request->billingmerchant_openingbalance,
            'billingmerchant_fee'		 		=> $request->billingmerchant_fee,
            'billingmerchant_otherinfo' 		=> $request->billingmerchant_otherinfo,
            'updated_by'	 			        => $request->user_id,
            'updated_at'	 			        => date('Y-m-d h:i:s'),
		]);
		if($update){
			return response()->json(['message' => 'Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function billingmerchantlist(Request $request){
        $validate = Validator::make($request->all(), [ 
            'brand_id'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
		$getlist = DB::table('billingmerchant')
		->select('*')
		->where('brand_id','=',$request->brand_id)
        ->where('status_id','=',1)
		->get();
        $logopath = URL::to('/')."/public/billingmerchantlogo/";
		if($getlist){
		    return response()->json(['data' => $getlist, 'logopath' => $logopath, 'message' => 'Billing Merchant List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function billingmerchantdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
            'billingmerchant_id'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
		$getdetails = DB::table('billingmerchant')
		->select('*')
		->where('billingmerchant_id','=',$request->billingmerchant_id)
		->where('status_id','=',1)
		->first();
        if($getdetails){
            $logopath = URL::to('/')."/public/billingmerchantlogo/".$getdetails->billingmerchant_logo;
            return response()->json(['data' => $getdetails, 'logopath' => $logopath, 'message' => 'Billing Merchant Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deletebillingmerchant(Request $request){
		$validate = Validator::make($request->all(), [ 
            'billingmerchant_id'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
		$delete  = DB::table('billingmerchant')
        ->where('billingmerchant_id','=',$request->billingmerchant_id)
        ->update([
            'status_id'   => 2,
        ]); 
		if($delete){
		    return response()->json(['message' => 'Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
    public function addwithdrawalamount(Request $request){
		$validate = Validator::make($request->all(), [ 
            'withdrawal_amount' => 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
        $adds[] = array(
		'withdrawal_amount'     => $request->withdrawal_amount,
		'withdrawal_date' 	    => date('Y-m-d'),
        'withdrawal_month' 	    => $request->withdrawal_month,
        'withdrawal_comment' 	=> $request->withdrawal_comment,
        'withdrawaltype_id'     => $request->withdrawaltype_id,
        'billingmerchant_id'    => $request->billingmerchant_id,
		'status_id'		        => 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('withdrawal')->insert($adds);
		if($save){
			return response()->json(['message' => 'Withdrawal Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
    public function withdrawalamountlist(Request $request){
		$validate = Validator::make($request->all(), [ 
            'withdrawal_month'	=> 'required',
            'billingmerchant_id'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json($validate->errors(), 400);
        }
        $getdetails = DB::table('withdrawaldetails')
		->select('*')
		->where('withdrawal_month','=',$request->withdrawal_month)
        ->where('billingmerchant_id','=',$request->billingmerchant_id)
		->where('status_id','=',1)
		->get();
		if($getdetails){
		    return response()->json(['data' => $getdetails,'message' => 'Withdrawal List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
    public function withdrawaltype(Request $request){
		$data = DB::table('withdrawaltype')
		->select('*')
		->where('status_id','=',1)
		->get();
        if($data){
		    return response()->json(['data' => $data, 'message' => 'Withdrawal Type List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
    public function billingmerchantreport(Request $request){
        $validate = Validator::make($request->all(), [ 
            'yearmonth'	    => 'required',
            'brand_id'	    => 'required',
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
        $firstdatewithzero = $setyearmonth.'-01';
        $firstdate = $setyearmonth.'-01';
        
        $merchatdetail = DB::table('billingmerchant')
		->select('*')
		->where('brand_id','=',$request->brand_id)
        ->where('status_id','=',1)
		->get();
        $stats = array();
        $index=0;
        foreach($merchatdetail as $merchatdetails){
            $firstbalance = $merchatdetails->billingmerchant_openingbalance;
            $ppaidbalance = DB::table('orderpayment')
            ->select('orderpayment_amount')
            ->where('orderpayment_date','<',$firstdatewithzero)
            ->where('orderpaymentstatus_id','=',3)
            ->where('merchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('orderpayment_amount');
            $precoverybalance = DB::table('orderpayment')
            ->select('orderpayment_amount')
            ->where('orderpayment_recoverydate','<',$firstdatewithzero)
            ->where('orderpaymentstatus_id','=',7)
            ->where('merchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('orderpayment_amount');
            $previouspaidbalance = $ppaidbalance+$precoverybalance;
            $previousfeededuction =  $merchatdetails->billingmerchant_fee / 100 * $previouspaidbalance;
            $previoustotalwithdrawl = DB::table('withdrawal')
            ->select('withdrawal_amount')
            ->where('withdrawal_month','<',$request->yearmonth)
            ->where('billingmerchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('withdrawal_amount');
            $previousnetbalance = $previouspaidbalance+$firstbalance-$previoustotalwithdrawl-$previousfeededuction;
            $openingbalance = $previousnetbalance;
            $cpaidbalance = DB::table('orderpayment')
            ->select('orderpayment_amount')
            ->where('orderpayment_date','like',$setyearmonth.'%')
            ->where('orderpaymentstatus_id','=',3)
            ->where('merchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('orderpayment_amount');
            $crecoverybalance = DB::table('orderpayment')
            ->select('orderpayment_amount')
            ->where('orderpayment_recoverydate','like',$setyearmonth.'%')
            ->where('orderpaymentstatus_id','=',7)
            ->where('merchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('orderpayment_amount');
            $paidbalance = $cpaidbalance+$crecoverybalance;
            $feededuction =  $merchatdetails->billingmerchant_fee / 100 * $paidbalance;
            $loandeduction = 30 / 100 * $paidbalance;
            $cash = DB::table('withdrawal')
            ->select('withdrawal_amount')
            ->where('withdrawaltype_id','=',1)
            ->where('withdrawal_month','=',$request->yearmonth)
            ->where('billingmerchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('withdrawal_amount');
            $loan = DB::table('withdrawal')
            ->select('withdrawal_amount')
            ->where('withdrawaltype_id','=',2)
            ->where('withdrawal_month','=',$request->yearmonth)
            ->where('billingmerchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('withdrawal_amount');
            $vendor = DB::table('withdrawal')
            ->select('withdrawal_amount')
            ->where('withdrawaltype_id','=',3)
            ->where('withdrawal_month','=',$request->yearmonth)
            ->where('billingmerchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('withdrawal_amount');
            $subscription = DB::table('withdrawal')
            ->select('withdrawal_amount')
            ->where('withdrawaltype_id','=',4)
            ->where('withdrawal_month','=',$request->yearmonth)
            ->where('billingmerchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('withdrawal_amount');
            $totalwithdrawl = DB::table('withdrawal')
            ->select('withdrawal_amount')
            ->where('withdrawal_month','=',$request->yearmonth)
            ->where('billingmerchant_id','=',$merchatdetails->billingmerchant_id)
            ->where('status_id','=',1)
            ->sum('withdrawal_amount');
            $netcurrentbalance = $paidbalance+$openingbalance-$loan-$feededuction;
            $grosstotalbalance = $openingbalance+$paidbalance;
            $netbalance = $grosstotalbalance-$totalwithdrawl-$feededuction;

            $merchatdetails->billingmerchant_openingbalance 	= $openingbalance;
            $merchatdetails->paidbalance 		                = $paidbalance;
			$merchatdetails->feededuction 		                = $feededuction;
			$merchatdetails->loandeduction 	                    = $loan;
			$merchatdetails->netcurrentbalance                  = $netcurrentbalance;
			$merchatdetails->grosstotalbalance                  = $grosstotalbalance;
			$merchatdetails->cash 			                    = $cash;
			$merchatdetails->loan 		                        = $loandeduction;
			$merchatdetails->vendor	 	                        = $vendor;
			$merchatdetails->subscription 	                    = $subscription;
			$merchatdetails->totalwithdrawl                     = $totalwithdrawl;
			$merchatdetails->netbalance 		                = $netbalance;
            $stats[$index] 		                                = $merchatdetails;
            $index++;
        }
     	$logopath = URL::to('/')."/public/billingmerchantlogo/";
		if($stats){
		    return response()->json(['data' => $stats, 'logopath' => $logopath, 'message' => 'Billing Merchant Report'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}