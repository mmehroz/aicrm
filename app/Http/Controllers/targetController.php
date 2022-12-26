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

class targetController extends Controller
{
	public function addtarget(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'usertarget_target'  	=> 'required',
	    	'usertarget_month'  	=> 'required',
	    	'usertarget_userid'  	=> 'required',
            'brand_id'  	        => 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$addtarget[] = array(
		'usertarget_target'		=> $request->usertarget_target,
		'usertarget_month'		=> $request->usertarget_month,
		'user_id' 				=> $request->usertarget_userid,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		DB::table('usertarget')->insert($addtarget);
		if($addtarget){
			return response()->json(['message' => 'Target Added Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function updatetarget(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'usertarget_id'  		=> 'required',
	    	'usertarget_target'  	=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$updatetarget = DB::table('usertarget')
			->where('usertarget_id','=',$request->usertarget_id)
			->update([
			'usertarget_target'			=> $request->usertarget_target,
		]); 
		if($updatetarget){
			return response()->json(['message' => 'Target Updated Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function nontargetlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'usertarget_month'  => 'required',
	    	'brand_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getuserid = DB::table('userbarnd')
		->select('user_id')
		->where('status_id','=',1)
		->where('brand_id','=',$request->brand_id)
		->get();
		$sortuserid = array();
		foreach ($getuserid as $getuserids) {
			$sortuserid[] = $getuserids->user_id;
		}
        $gettargetlist = DB::table('targetlist')
		->select('*')
		->where('usertarget_month','=',$request->usertarget_month)
		->where('status_id','=',1)
		->get();	
		$targetedemployee = array();
		foreach ($gettargetlist as $gettargetlists) {
			$targetedemployee[] = $gettargetlists->user_id;
		}
		$nontargetemployeedata = DB::table('user')
		->select('*')
		->whereNotIn('user_id', $targetedemployee)
		->whereIn('role_id',[6,7,8])
		->whereIn('user_id',$sortuserid)
		->where('status_id','=',1)
        ->get();
        $profilepath = URL::to('/')."/public/user_picture/";
		if($nontargetemployeedata){
			return response()->json(['nontargetemployeedata' => $nontargetemployeedata, 'profilepath' => $profilepath, 'message' => 'Non Target List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
    public function targetlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'usertarget_month'  => 'required',
	    	'brand_id'  		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
        $getuserid = DB::table('userbarnd')
		->select('user_id')
		->where('status_id','=',1)
		->where('brand_id','=',$request->brand_id)
		->get();
		$sortuserid = array();
		foreach ($getuserid as $getuserids) {
			$sortuserid[] = $getuserids->user_id;
		}
		$targetemployeedata = DB::table('targetlist')
		->select('*')
		->where('usertarget_month','=',$request->usertarget_month)
        ->whereIn('user_id',$sortuserid)
		->where('status_id','=',1)
		->get();
        $profilepath = URL::to('/')."/public/user_picture/";
		if($targetemployeedata){
			return response()->json(['targetemployeedata' => $targetemployeedata, 'profilepath' => $profilepath, 'message' => 'Target List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function usertargetdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'target_userid'		=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getusertargetlist = DB::table('targetlist')
		->select('*')
		->where('user_id','=',$request->target_userid)
		->where('status_id','=',1)
		->get();	
		if($getusertargetlist){
			return response()->json(['usertargetata' => $getusertargetlist, 'message' => 'User Target Details'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
}