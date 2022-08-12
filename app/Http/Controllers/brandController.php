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

class brandController extends Controller
{
	public $emptyarray = array();
	public function createbrand(Request $request){
		$validate = Validator::make($request->all(), [ 
		      'brand_name' 			=> 'required',
		      'brand_email'			=> 'required',
		      'brand_logo' 			=> 'required',
		      'brand_cover'			=> 'required',
		      'brand_website' 		=> 'required',
		      'brandtype_id'		=> 'required',
		      'brand_description'	=> 'required',
		    ]);
	     	if ($validate->fails()) {    
				return response()->json($validate->errors(), 400);
			}
			$validateunique = Validator::make($request->all(), [ 
		      'brand_email' 		=> 'unique:brand,brand_email',
		    ]);
	     	if ($validateunique->fails()) {    
				return response()->json("Brand Email Already Exist", 400);
			}
			$validatecover = Validator::make($request->all(), [ 
		    	'brand_cover'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatecover->fails()) {    
				return response()->json("Invalid Image Format", 400);
			}
	        $validatelogo = Validator::make($request->all(), [ 
		    	'brand_logo'=>'mimes:jpeg,bmp,png,jpg|max:5120',
		    ]);
			if ($validatelogo->fails()) {    
				return response()->json("Invalid Format", 400);
			}
			$brandcover;
        	if ($request->has('brand_cover')) {
            		if( $request->brand_cover->isValid()){
			            $number = rand(1,999);
				        $numb = $number / 7 ;
						$name = "brand_cover";
				        $extension = $request->brand_cover->extension();
			            $brandcover  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			            $brandcover = $request->brand_cover->move(public_path('brand_cover/'),$brandcover);
					    $img = Image::make($brandcover)->resize(800,800, function($constraint) {
			                    $constraint->aspectRatio();
			            });
			            $img->save($brandcover);
					    $brandcover = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			        }
            }else{
    	        $brandcover = 'no_image.jpg'; 
	        }
			$brandlogo;
        	if ($request->has('brand_logo')) {
            		if( $request->brand_logo->isValid()){
			            $number = rand(1,999);
				        $numb = $number / 7 ;
						$name = "brand_logo";
				        $extension = $request->brand_logo->extension();
			            $brandlogo  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			            $brandlogo = $request->brand_logo->move(public_path('brand_logo/'),$brandlogo);
					    $img = Image::make($brandlogo)->resize(800,800, function($constraint) {
			                    $constraint->aspectRatio();
			            });
			            $img->save($brandlogo);
					    $brandlogo = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
			        }
            }else{
    	        $brandlogo = 'no_image.jpg'; 
	        }
			$adds[] = array(
			'brand_name' 		=> $request->brand_name,
			'brand_email' 		=> $request->brand_email,
			'brand_logo'		=> $brandlogo,
			'brand_cover' 		=> $brandcover,
			'brand_website' 	=> $request->brand_website,
			'brandtype_id' 		=> $request->brandtype_id,
			'brand_description' => $request->brand_description,
			'status_id'			=> 1,
			'created_by'		=> $request->user_id,
			'created_at'		=> date('Y-m-d h:i:s'),
			);
			$save = DB::table('brand')->insert($adds);
		if($save){
			return response()->json(['data' => $adds,'message' => 'Brand Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatebrand(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'brand_id' 			=> 'required',
	      'brand_name' 			=> 'required',
	      'brand_email'			=> 'required',
	      'brand_website' 		=> 'required',
	      'brandtype_id'		=> 'required',
	      'brand_description'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getbrandemail = DB::table('brand')
		->where('brand_id','=',$request->brand_id)
		->select('brand_email')
		->first();
		if ($getbrandemail->brand_email != $request->brand_email) {
		$validateunique = Validator::make($request->all(), [ 
	      'brand_email' => 'unique:brand,brand_email',
	    ]);
     	if ($validateunique->fails()) {    
			return response()->json("Brand Email Already Exist", 400);
		}
		}
		$validatecover = Validator::make($request->all(), [ 
	    	'brand_cover'=>'mimes:jpeg,bmp,png,jpg|max:5120',
	    ]);
		if ($validatecover->fails()) {    
			return response()->json("Invalid Format", 400);
		}
        $validatelogo = Validator::make($request->all(), [ 
	    	'brand_logo'=>'mimes:jpeg,bmp,png,jpg|max:5120',
	    ]);
		if ($validatelogo->fails()) {    
			return response()->json("Invalid Format", 400);
		}
		$brandcover;
    	if ($request->has('brand_cover')) {
        		if( $request->brand_cover->isValid()){
		            $number = rand(1,999);
			        $numb = $number / 7 ;
					$name = "brand_cover";
			        $extension = $request->brand_cover->extension();
		            $brandcover  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		            $brandcover = $request->brand_cover->move(public_path('brand_cover/'),$brandcover);
				    $img = Image::make($brandcover)->resize(800,800, function($constraint) {
		                    $constraint->aspectRatio();
		            });
		            $img->save($brandcover);
				    $brandcover = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		        }
        }else{
	        $brandcover = 'no_image.jpg'; 
        }
		$brandlogo;
    	if ($request->has('brand_logo')) {
        		if($request->brand_logo->isValid()){
		            $number = rand(1,999);
			        $numb = $number / 7 ;
					$name = "brand_logo";
			        $extension = $request->brand_logo->extension();
		            $brandlogo  = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		            $brandlogo = $request->brand_logo->move(public_path('brand_logo/'),$brandlogo);
				    $img = Image::make($brandlogo)->resize(800,800, function($constraint) {
		                    $constraint->aspectRatio();
		            });
		            $img->save($brandlogo);
				    $brandlogo = date('Y-m-d')."_".$numb."_".$name."_.".$extension;
		        }
        }else{
	        $brandlogo = 'no_image.jpg'; 
        }
		$update  = DB::table('brand')
		->where('brand_id','=',$request->brand_id)
		->update([
		'brand_name' 			=> $request->brand_name,
		'brand_email' 			=> $request->brand_email,
		'brand_website' 		=> $request->brand_website,
		'brandtype_id'				=> $request->brandtype_id,
		'brand_description'		=> $request->brand_description,
		'updated_by'	 		=> $request->user_id,
		'updated_at'	 		=> date('Y-m-d h:i:s'),
		]);
		if ($brandcover != 'no_image.jpg') {
			$updatebanner  = DB::table('brand')
			->where('brand_id','=',$request->brand_id)
			->update([
			'brand_cover'			=> $brandcover,
			]); 
		}
		if ($brandlogo != 'no_image.jpg') {
			$updatelogo  = DB::table('brand')
			->where('brand_id','=',$request->brand_id)
			->update([
			'brand_logo'			=> $brandlogo,
			]); 
		}
		if($update){
			return response()->json(['message' => 'Brand Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function brandlist(Request $request){
		$brandlist = DB::table('brand')
		->select('brand_id','brand_name','brand_email','brand_logo','brand_cover','brand_website','brandtype_id','brand_description')
		->where('status_id','=',1)
		->get();
		$logopath = URL::to('/')."/public/brand_logo/";
		$coverpath = URL::to('/')."/public/brand_cover/";
		if(isset($brandlist)){
			return response()->json(['data' => $brandlist, 'logopath' => $logopath, 'coverpath' => $coverpath, 'message' => 'Brand List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Brand List'],200);
		}
	}
	public function branddetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Brand Id Required", 400);
		}
		$branddetail = DB::table('branddetail')
		->select('*')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->first();
		$logopath = URL::to('/')."/public/brand_logo/";
		$coverpath = URL::to('/')."/public/brand_cover/";
		if($branddetail){
			return response()->json(['data' => $branddetail,'logopath' => $logopath, 'coverpath' => $coverpath,'message' => 'Brand Detail'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deletebrand(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Brand Id Required", 400);
		}
		$delete  = DB::table('brand')
		->where('brand_id','=',$request->brand_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($delete){
			return response()->json(['message' => 'Brand Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}