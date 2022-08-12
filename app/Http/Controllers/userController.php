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

class userController extends Controller
{
	public $emptyarray = array();
	public function adduser(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'user_name' 		=> 'required',
	      'user_email'		=> 'required',
	      'user_username'	=> 'required',
	      'user_target'		=> 'required',
	      'user_password' 	=> 'required',
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$validateunique = Validator::make($request->all(), [ 
	      'user_email' 	=> 'unique:user,user_email',
	    ]);
     	if ($validateunique->fails()) {    
			return response()->json("User Email Already Exist", 400);
		}
		$validatepicture = Validator::make($request->all(), [ 
	    	'user_picture'=>'mimes:jpeg,bmp,png,jpg|max:5120',
	    ]);
		if ($validatepicture->fails()) {    
			return response()->json("Invalid Format", 400);
		}
		$userpicturename;
    	if ($request->has('user_picture')) {
    		if( $request->user_picture->isValid()){
	            $number = rand(1,999);
		        $numb = $number / 7 ;
				$name = "user_picture";
		        $extension = $request->user_picture->extension();
	            $userpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	            $userpicturename = $request->user_picture->move(public_path('user_picture/'),$userpicturename);
			    $img = Image::make($userpicturename)->resize(800,800, function($constraint) {
	                    $constraint->aspectRatio();
	            });
	            $img->save($userpicturename);
			    $userpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	        }
        }else{
	        $userpicturename = 'no_image.jpg'; 
        }
        $validatecover = Validator::make($request->all(), [ 
	    	'user_coverpicture'=>'mimes:jpeg,bmp,png,jpg|max:5120',
	    ]);
		if ($validatecover->fails()) {    
			return response()->json("Invalid Cover Format", 400);
		}
		$usercoverpicturename;
    	if ($request->has('user_coverpicture')) {
    		if( $request->user_coverpicture->isValid()){
	            $number = rand(1,999);
		        $numb = $number / 7 ;
				$name = "user_coverpicture";
		        $extension = $request->user_coverpicture->extension();
	            $usercoverpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	            $usercoverpicturename = $request->user_coverpicture->move(public_path('user_coverpicture/'),$usercoverpicturename);
			    $img = Image::make($usercoverpicturename)->resize(800,800, function($constraint) {
	                    $constraint->aspectRatio();
	            });
	            $img->save($usercoverpicturename);
			    $usercoverpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	        }
        }else{
	        $usercoverpicturename = 'no_image.jpg'; 
        }
        $user_token =  $this->generateRandomString(50);
		$adds[] = array(
		'user_name' 			=> $request->user_name,
		'user_email'			=> $request->user_email,
		'user_username' 		=> $request->user_username,
		'user_target'			=> $request->user_target,
		'user_password' 		=> $request->user_password,
		'user_picture'			=> $userpicturename,
		'user_coverpicture'		=> $usercoverpicturename,
		'user_loginstatus' 		=> "Offline",
		'user_token' 			=> $user_token,
		'role_id' 				=> $request->role_id,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		$initialsave = DB::table('user')->insert($adds);
		$user_id = DB::getPdo()->lastInsertId();
		if (isset($request->brand)) {
			foreach ($request->brand as $brands) {
				$save = array(
				'user_id'		=> $user_id,
				'brand_id'		=> $brands,
				'status_id' 	=> 1,
				'created_by'	=> $request->user_id,
				'created_at'	=> date('Y-m-d h:i:s'),
				);
				DB::table('userbarnd')->insert($save);
			}
		}
		if($initialsave){
			return response()->json(['message' => 'User Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateuser(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'edituser_id'		=> 'required',
	      'user_name' 		=> 'required',
	      'user_email'		=> 'required',
	      'user_username' 	=> 'required',
	      'user_target'		=> 'required',
	      'role_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getuseremail = DB::table('user')
		->select('user.user_email')
		->where('user.user_id','=',$request->edituser_id)
		->first();
		if ($getuseremail->user_email != $request->user_email) {
			$validateunique = Validator::make($request->all(), [ 
		      'user_email' 		=> 'unique:user,user_email',
		    ]);
	     	if ($validateunique->fails()) {    
				return response()->json("User Email Already Exist", 400);
			}
		}
		$userpicturename;
    	if ($request->has('user_picture')) {
			$validatepicture = Validator::make($request->all(), [ 
		    	'user_picture'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatepicture->fails()) {    
				return response()->json("Invalid Format", 400);
			}
			if( $request->user_picture->isValid()){
	            $number = rand(1,999);
		        $numb = $number / 7 ;
				$name = "user_picture";
		        $extension = $request->user_picture->extension();
	            $userpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	            $userpicturename = $request->user_picture->move(public_path('user_picture/'),$userpicturename);
			    $img = Image::make($userpicturename)->resize(800,800, function($constraint) {
	                    $constraint->aspectRatio();
	            });
	            $img->save($userpicturename);
			    $userpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	        }
        }else{
	        $userpicturename = 'no_image.jpg'; 
        }
        if ($request->has('user_coverpicture')) {
			$validatecoverpicture = Validator::make($request->all(), [ 
		    	'user_coverpicture'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatecoverpicture->fails()) {    
				return response()->json("Invalid Format", 400);
			}
			if( $request->user_coverpicture->isValid()){
	            $number = rand(1,999);
		        $numb = $number / 7 ;
				$name = "user_coverpicture";
		        $extension = $request->user_coverpicture->extension();
	            $usercoverpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	            $usercoverpicturename = $request->user_coverpicture->move(public_path('user_coverpicture/'),$usercoverpicturename);
			    $img = Image::make($usercoverpicturename)->resize(800,800, function($constraint) {
	                    $constraint->aspectRatio();
	            });
	            $img->save($usercoverpicturename);
			    $usercoverpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	        }
        }else{
	        $usercoverpicturename = 'no_image.jpg'; 
        }
	    $updateuser  = DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'user_name' 	=> $request->user_name,
			'user_email'	=> $request->user_email,
			'user_username'	=> $request->user_username,
			'user_target'	=> $request->user_target,
			'role_id' 		=> $request->role_id,
			'status_id'		=> 1,
			'updated_by'	=> $request->user_id,
			'updated_at'	=> date('Y-m-d h:i:s'),
		]); 
		if ($userpicturename != 'no_image.jpg') {
			DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'user_picture'			=> $userpicturename,
			]); 
		}
		if ($usercoverpicturename != 'no_image.jpg') {
			DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'user_coverpicture'			=> $usercoverpicturename,
			]); 
		}
		if ($request->user_password != "") {
			DB::table('user')
			->where('user_id','=',$request->edituser_id)
			->update([
			'user_password' 		=> $request->user_password,
			]); 
		}
		if (isset($request->brand)) {
			foreach ($request->brand as $brands) {
				$checkbrand = DB::table('userbarnd')
				->select('user_id')
				->where('status_id','=',1)
				->where('brand_id','=',$brands)
				->where('user_id','=',$request->edituser_id)
				->count();
				if ($checkbrand == 0) {
					$addmorebrand = array(
					'user_id'		=> $request->edituser_id,
					'brand_id'		=> $brands,
					'status_id' 	=> 1,
					'created_by'	=> $request->user_id,
					'created_at'	=> date('Y-m-d h:i:s'),
					);
					DB::table('userbarnd')->insert($addmorebrand);
				}
			}
		}
		if($updateuser){
			return response()->json(['message' => 'User Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function userlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'brand_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Brand Id Required", 400);
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
		$getusers = DB::table('user')
		->select('user_id','user_name','user_email','user_target','user_loginstatus','user_picture','user_coverpicture')
		->where('status_id','=',1)
		->whereIn('user_id',$sortuserid)
		->get();
		$profilepath = URL::to('/')."/public/user_picture/";
		$coverpath = URL::to('/')."/public/user_coverpicture/";
		if(isset($getusers)){
			return response()->json(['data' => $getusers,'profilepath' => $profilepath, 'coverpath' => $coverpath, 'message' => 'User List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'User List'],200);
		}
	}
	public function alluserlist(Request $request){
		$getusers = DB::table('user')
		->select('user_id','user_name','user_email','user_target','user_loginstatus','user_picture','user_coverpicture')
		->where('status_id','=',1)
		->paginate(100);
		$profilepath = URL::to('/')."/public/user_picture/";
		$coverpath = URL::to('/')."/public/user_coverpicture/";
		if(isset($getusers)){
			return response()->json(['data' => $getusers,'profilepath' => $profilepath, 'coverpath' => $coverpath, 'message' => 'User List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'User List'],200);
		}
	}
	public function userdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'edituser_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Edit User Id Required", 400);
		}
		$getbrandid = DB::table('userbarnd')
		->select('brand_id')
		->where('status_id','=',1)
		->where('user_id','=',$request->edituser_id)
		->get();
		$brand = array();
		foreach ($getbrandid as $getbrandids) {
			$brand[] = DB::table('brand')
			->select('brand_name')
			->where('status_id','=',1)
			->where('brand_id','=',$getbrandids->brand_id)
			->first();
		}
		$sortbrands = array();
		foreach ($brand as $brands) {
			if($brands != null) {
				$sortbrands[] = $brands->brand_name;
			}
		}
		$getuserdetails = DB::table('user')
		->select('user_id','user_name','user_email','user_username','user_target','user_loginstatus','user_picture','user_coverpicture','role_id')
		->where('user_id','=',$request->edituser_id)
		->where('status_id','=',1)
		->first();
		$profilepath = URL::to('/')."/public/user_picture/";
		$coverpath = URL::to('/')."/public/user_coverpicture/";
		if($getuserdetails){
			return response()->json(['brands' => $sortbrands, 'data' => $getuserdetails,'profilepath' => $profilepath, 'coverpath' => $coverpath, 'message' => 'User Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deleteuser(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'edituser_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Edit User Id Required", 400);
		}
		$updateuserstatus  = DB::table('user')
		->where('user_id','=',$request->edituser_id)
		->update([
			'status_id' 	=> 2,
			'deleted_by'	=> $request->user_id,
			'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($updateuserstatus){
			return response()->json(['message' => 'User Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateusercoverpicture(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'user_coverpicture'	=> 'required',
	      'edituser_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$validatepicture = Validator::make($request->all(), [ 
	    	'user_coverpicture'=>'mimes:jpeg,bmp,png,jpg|max:10120',
	    ]);
		if ($validatepicture->fails()) {    
			return response()->json("Invalid Format", 400);
		}
		$usercoverpicturename;
    	if ($request->has('user_coverpicture')) {
        		if( $request->user_coverpicture->isValid()){
		            $number = rand(1,999);
			        $numb = $number / 7 ;
					$name = "user_coverpicture";
			        $extension = $request->user_coverpicture->extension();
		            $usercoverpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		            $usercoverpicturename = $request->user_coverpicture->move(public_path('user_coverpicture/'),$usercoverpicturename);
				    $img = Image::make($usercoverpicturename)->resize(800,800, function($constraint) {
		                    $constraint->aspectRatio();
		            });
		            $img->save($usercoverpicturename);
				    $usercoverpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		        }
        }else{
	        $usercoverpicturename = 'no_image.jpg'; 
        }
		$updatecoverpicture = DB::table('user')
			->where('user_id','=',$request->user_id)
			->update([
			'user_coverpicture'		=> $usercoverpicturename,
		]); 
		if($updatecoverpicture){
			return response()->json(['data' => $usercoverpicturename,'message' => 'User Cover Picture Uploaded Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public  function generateRandomString($length = 20){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
	}
}