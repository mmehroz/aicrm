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

class loginController extends Controller
{
	public function login(Request $request){
    	$validate = Validator::make($request->all(), [ 
	      'email' 		=> 'required',
	      'password'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Enter Credentials To Signin", 400);
		}
		$getprofileinfo = DB::table('user')
		->select('*')
		->where('user_email','=',$request->email)
		->where('user_password','=',$request->password)
		->where('status_id','=',1)
		->first();
		if($getprofileinfo){
			$updateuser  = DB::table('user')
			->where('user_id','=',$getprofileinfo->user_id)
			->update([
			'user_loginstatus' 		=> "Online",
			]); 
			$getinfo = DB::table('loginuserinfo')
			->select('*')
			->where('user_id','=',$getprofileinfo->user_id)
			->where('status_id','=',1)
			->first();
			$getbrandid = DB::table('userbarnd')
			->select('brand_id')
			->where('status_id','=',1)
			->where('user_id','=',$getprofileinfo->user_id)
			->get();
			$brandid = array();
			$index='brand1';
			foreach ($getbrandid as $getbrandids) {
				$brandid[] = $getbrandids->brand_id;
				$getinfo->$index = $getbrandids->brand_id;
				$index++;
			}
			$brandtype = DB::table('brand')
			->select('brandtype_id')
			->where('status_id','=',1)
			->whereIn('brand_id',$brandid)
			->groupBy('brandtype_id')
			->get();
			$brandtype_id = array();
			foreach($brandtype as $brandtypes){
				$brandtype_id[] = $brandtypes->brandtype_id;
			}
			$getinfo->brandtype = $brandtype_id;
			$path = URL::to('/')."/public/user_picture/";
			$coverpath = URL::to('/')."/public/user_coverpicture/";
			return response()->json(['data' => $getinfo, 'path' => $path, 'coverpath' => $coverpath, 'message' => 'Login Successfully'],200);
		}else{
			return response()->json("Invalid Email Or Password", 400);
		}
	}
	public function logout(Request $request){
		$logoutuser  = DB::table('user')
			->where('user_id','=',$request->user_id)
			->update([
			'user_loginstatus' 		=> "Offline",
		]); 
		return response()->json(['message' => 'Logout Successfully'],200);
	}
}