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

class vendorController extends Controller
{
	public $emptyarray = array();
	public function addvendor(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'vendor_name' 		=> 'required',
	      'vendor_email'		=> 'required',
	      'vendor_contact'	    => 'required',
	      'vendor_address'		=> 'required',
	      'vendor_website' 	    => 'required',
	      'vendor_otherinfo'	=> 'required',
        ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$validateunique = Validator::make($request->all(), [ 
	      'vendor_email' 	=> 'unique:vendor,vendor_email',
	    ]);
     	if ($validateunique->fails()) {    
			return response()->json("Email Already Exist", 400);
		}
		$validatepicture = Validator::make($request->all(), [ 
	    	'vendor_picture'=>'mimes:jpeg,bmp,png,jpg|max:5120',
	    ]);
		if ($validatepicture->fails()) {    
			return response()->json("Invalid Format", 400);
		}
		$vendorpicturename;
    	if ($request->has('vendor_picture')) {
    		if( $request->vendor_picture->isValid()){
	            $number = rand(1,999);
		        $numb = $number / 7 ;
				$name = "vendor_picture";
		        $extension = $request->vendor_picture->extension();
	            $vendorpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	            $vendorpicturename = $request->vendor_picture->move(public_path('vendor_picture/'),$vendorpicturename);
			    $img = Image::make($vendorpicturename)->resize(800,800, function($constraint) {
	                    $constraint->aspectRatio();
	            });
	            $img->save($vendorpicturename);
			    $vendorpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	        }
        }else{
	        $vendorpicturename = 'no_image.jpg'; 
        }
        $validatecover = Validator::make($request->all(), [ 
	    	'vendor_coverpicture'=>'mimes:jpeg,bmp,png,jpg|max:5120',
	    ]);
		if ($validatecover->fails()) {    
			return response()->json("Invalid Cover Format", 400);
		}
		$vendorcoverpicturename;
    	if ($request->has('vendor_coverpicture')) {
    		if( $request->vendor_coverpicture->isValid()){
	            $number = rand(1,999);
		        $numb = $number / 7 ;
				$name = "vendor_coverpicture";
		        $extension = $request->vendor_coverpicture->extension();
	            $vendorcoverpicturename  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	            $vendorcoverpicturename = $request->vendor_coverpicture->move(public_path('vendor_coverpicture/'),$vendorcoverpicturename);
			    $img = Image::make($vendorcoverpicturename)->resize(800,800, function($constraint) {
	                    $constraint->aspectRatio();
	            });
	            $img->save($vendorcoverpicturename);
			    $vendorcoverpicturename = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
	        }
        }else{
	        $vendorcoverpicturename = 'no_image.jpg'; 
        }
        $adds[] = array(
		'vendor_name' 			=> $request->vendor_name,
		'vendor_email'			=> $request->vendor_email,
		'vendor_contact' 		=> $request->vendor_contact,
		'vendor_address'		=> $request->vendor_address,
		'vendor_website' 		=> $request->vendor_website,
		'vendor_otherinfo'		=> $request->vendor_otherinfo,
		'vendor_picture'		=> $vendorpicturename,
		'vendor_coverpicture'	=> $vendorcoverpicturename,
        'vendortype_id'	        => $request->vendortype_id,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		$initialsave = DB::table('vendor')->insert($adds);
		if($initialsave){
			return response()->json(['message' => 'Vendor Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
    public function vendortype(Request $request){
		$data = DB::table('vendortype')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($data)){
			return response()->json(['data' => $data, 'message' => 'Vendor Type'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Vendor Type'],200);
		}
	}
	public function vendorlist(Request $request){
		$validate = Validator::make($request->all(), [ 
			'vendortype_id' 	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->vendortype_id == 0){
			$data = DB::table('vendor')
			->select('*')
			->where('status_id','=',1)
			->get();
		}else{
			$data = DB::table('vendor')
			->select('*')
			->where('vendortype_id','=',$request->vendortype_id)
			->where('status_id','=',1)
			->get();
		}
		$profilepath = URL::to('/')."/public/vendor_otherinfo/";
		$coverpath = URL::to('/')."/public/vendor_coverpicture/";
		if(isset($data)){
			return response()->json(['data' => $data,'profilepath' => $profilepath, 'coverpath' => $coverpath, 'message' => 'User List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'User List'],200);
		}
	}
	public function vendordetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'vendor_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Vendor Id Required", 400);
		}
        $data = DB::table('vendor')
		->select('*')
		->where('status_id','=',1)
		->where('vendor_id','=',$request->vendor_id)
        ->first();
		$profilepath = URL::to('/')."/public/vendor_picture/";
		$coverpath = URL::to('/')."/public/vendor_coverpicture/";
		if($data){
			return response()->json(['data' => $data,'profilepath' => $profilepath, 'coverpath' => $coverpath, 'message' => 'User Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deletevendor(Request $request){
        $validate = Validator::make($request->all(), [ 
            'vendor_id'	=> 'required',
        ]);
        if ($validate->fails()) {    
            return response()->json("Vendor Id Required", 400);
        }
		$updateuserstatus  = DB::table('vendor')
		->where('vendor_id','=',$request->vendor_id)
		->update([
			'status_id' 	=> 2,
			'deleted_by'	=> $request->user_id,
			'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($updateuserstatus){
			return response()->json(['message' => 'Vendor Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}