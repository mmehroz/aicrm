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

class patchqueryController extends Controller
{
	public $emptyarray = array();
    public function createpatchquery(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'patchtype_id' 		    => 'required',
	      'patchback_id'		    => 'required',
	      'patchquery_title'	    => 'required',
	      'patchquery_height'		=> 'required',
	      'patchquery_width' 	    => 'required',
	      'patchquery_quantity'	    => 'required',
          'patchquery_clientbudget'	=> 'required',
          'patchquery_otherdetails'	=> 'required',
          'brand_id'				=> 'required',
		]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
        $adds[] = array(
            'patchquery_title' 			=> $request->patchquery_title,
            'patchquery_height'			=> $request->patchquery_height,
            'patchquery_width' 		    => $request->patchquery_width,
            'patchquery_quantity'		=> $request->patchquery_quantity,
            'patchquery_clientbudget' 	=> $request->patchquery_clientbudget,
            'patchtype_id'			    => $request->patchtype_id,
			'patchback_id'			    => $request->patchback_id,
            'patchquery_otherdetails'	=> $request->patchquery_otherdetails,
			'patchquery_date'			=> date('Y-m-d'),
			'patchquerystatus_id'		=> 1,
			'brand_id'				    => $request->brand_id,
            'status_id'		 		    => 1,
            'created_by'	 		    => $request->user_id,
            'created_at'	 		    => date('Y-m-d h:i:s'),
        );
        $save = DB::table('patchquery')->insert($adds);
        $patchquery_id = DB::getPdo()->lastInsertId();
        if (isset($request->patchqueryattachment)) {
            $patchqueryattachment = $request->patchqueryattachment;
            $index = 0 ;
            $filename = array();
            foreach($patchqueryattachment as $patchqueryattachments){
                $saveattachment = array();
                if($patchqueryattachments->isValid()){
                    $number = rand(1,999);
                    $numb = $number / 7 ;
                    $foldername = $patchquery_id;
                    $extension = $patchqueryattachments->getClientOriginalExtension();
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $filename = $patchqueryattachments->move(public_path('patchquery/'.$foldername),$filename);
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $saveattachment = array(
                    'patchqueryattachment_name'	=> $filename,
                    'patchquery_id'				=> $patchquery_id,
                    'status_id' 			    => 1,
                    'created_by'			    => $request->user_id,
                    'created_at'			    => date('Y-m-d h:i:s'),
                    );
                }else{
                    return response()->json("Invalid File", 400);
                }
                DB::table('patchqueryattachment')->insert($saveattachment);
            }
        }
        if($save){
			return response()->json(['message' => 'Patch Query Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
    public function patchquerylist(Request $request){
		$validate = Validator::make($request->all(), [ 
			'patchquerystatus_id'	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->role_id <= 2){
			$data = DB::table('patchquery')
			->select('patchquery_id','patchquery_title','patchquery_date','patchquery_clientbudget','patchquery_amount','patchquery_deliverycost')
			->where('patchquerystatus_id','=',$request->patchquerystatus_id )
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
			$data = DB::table('patchquery')
			->select('patchquery_id','patchquery_title','patchquery_date','patchquery_clientbudget','patchquery_amount','patchquery_deliverycost')
			->where('patchquerystatus_id','=',$request->patchquerystatus_id )
			->whereIn('brand_id',$sortbrand)
			->where('status_id','=',1)
			->paginate(30);
		}else{
			$data = DB::table('patchquery')
			->select('patchquery_id','patchquery_title','patchquery_date','patchquery_clientbudget','patchquery_amount','patchquery_deliverycost')
			->where('created_by','=',$request->user_id)
			->where('patchquerystatus_id','=',$request->patchquerystatus_id )
			->where('status_id','=',1)
			->paginate(30);
		}
		if(isset($data)){
			return response()->json(['data' => $data, 'message' => 'Patch Query List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Patch Query List'],200);
		}
	}
    public function patchquerydetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'patchquery_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Patch Query Id Required", 400);
		}
        $data = DB::table('patchquerydetails')
		->select('*')
		->where('status_id','=',1)
		->where('patchquery_id','=',$request->patchquery_id)
        ->first();
		$clientattachments = DB::table('patchqueryattachment')
		->select('patchqueryattachment_id','patchqueryattachment_name')
		->where('patchqueryattachmenttype_id','=',1)
        ->where('status_id','=',1)
		->where('patchquery_id','=',$request->patchquery_id)
        ->get();
        $vendorattachments = DB::table('patchqueryattachment')
		->select('patchqueryattachment_id','patchqueryattachment_name')
		->where('patchqueryattachmenttype_id','=',2)
        ->where('status_id','=',1)
		->where('patchquery_id','=',$request->patchquery_id)
        ->get();
        $proposaltachments = DB::table('patchqueryattachment')
		->select('patchqueryattachment_id','patchqueryattachment_name')
		->where('patchqueryattachmenttype_id','=',3)
        ->where('status_id','=',1)
		->where('patchquery_id','=',$request->patchquery_id)
        ->get();
		$patchquerypath = URL::to('/')."/public/patchquery/".$request->patchquery_id.'/';
		if($data){
			return response()->json(['data' => $data,'patchquerypath' => $patchquerypath, 'clientattachments' => $clientattachments, 'vendorattachments' => $vendorattachments, 'proposaltachments' => $proposaltachments, 'message' => 'Patch Query Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
    public function movepatchquery(Request $request){
        $validate = Validator::make($request->all(), [ 
            'patchquery_id'			=> 'required',
			'patchquerystatus_id'	=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json("Patch Query Id Required", 400);
        }
		$update  = DB::table('patchquery')
		->where('patchquery_id','=',$request->patchquery_id)
		->update([
			'patchquerystatus_id' 	=> $request->patchquerystatus_id,
			'updated_by'		    => $request->user_id,
			'updated_by'		    => date('Y-m-d h:i:s'),
		]); 
		if($update){
			return response()->json(['message' => 'Moved Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
    public function updatepatchquery(Request $request){
		$validate = Validator::make($request->all(), [ 
			'patchtype_id' 		        => 'required',
			'patchback_id'		        => 'required',
			'patchquery_title'	        => 'required',
			'patchquery_height'		    => 'required',
			'patchquery_width' 	        => 'required',
			'patchquery_quantity'	    => 'required',
			'patchquery_amount'	        => 'required',
			'patchquery_deliverycost'	=> 'required',
			'patchquery_otherdetails'	=> 'required',
			'vendorproduction_id'		=> 'required',
			'patchquerystatus_id'	    => 'required',
		]);
		if ($validate->fails()) {    
		  	return response()->json($validate->errors(), 400);
		}
		$update  = DB::table('patchquery')
		->where('patchquery_id','=',$request->patchquery_id)
		->update([
			'patchquery_title' 			=> $request->patchquery_title,
			'patchquery_height'			=> $request->patchquery_height,
			'patchquery_width' 		    => $request->patchquery_width,
			'patchquery_quantity'		=> $request->patchquery_quantity,
			'patchquery_amount'	        => $request->patchquery_amount,
			'patchquery_deliverycost'	=> $request->patchquery_deliverycost,
            'patchquery_otherdetails'	=> $request->patchquery_otherdetails,
			'patchtype_id'			    => $request->patchtype_id,
			'patchback_id'			    => $request->patchback_id,
			'vendorproduction_id'	    => $request->vendorproduction_id,
            'patchquerystatus_id'	    => $request->patchquerystatus_id,
			'updated_by'			    => $request->user_id,
			'updated_by'			    => date('Y-m-d h:i:s'),
		]);
		if (isset($request->patchqueryattachment)) {
            $patchqueryattachment = $request->patchqueryattachment;
            $index = 0 ;
            $filename = array();
            foreach($patchqueryattachment as $patchqueryattachments){
                $saveattachment = array();
                if($patchqueryattachments->isValid()){
                    $number = rand(1,999);
                    $numb = $number / 7 ;
                    $foldername = $request->patchquery_id;
                    $extension = $patchqueryattachments->getClientOriginalExtension();
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $filename = $patchqueryattachments->move(public_path('patchquery/'.$foldername),$filename);
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $saveattachment = array(
                    'patchqueryattachment_name'	=> $filename,
                    'patchquery_id'				=> $request->patchquery_id,
                    'status_id' 			    => 1,
                    'created_by'			    => $request->user_id,
                    'created_at'			    => date('Y-m-d h:i:s'),
                    );
                }else{
                    return response()->json("Invalid File", 400);
                }
                DB::table('patchqueryattachment')->insert($saveattachment);
            }
        }
		if($update){
			return response()->json(['message' => 'Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deletepatchquery(Request $request){
        $validate = Validator::make($request->all(), [ 
            'patchquery_id'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json("Patch Query Id Required", 400);
        }
		$updateuserstatus  = DB::table('patchquery')
		->where('patchquery_id','=',$request->patchquery_id)
		->update([
			'status_id' 	=> 2,
			'deleted_by'	=> $request->user_id,
			'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($updateuserstatus){
			return response()->json(['message' => 'Patch Query Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}