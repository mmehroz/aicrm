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

class uiorderController extends Controller
{
	public function createuiorder(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'uiorder_title'  	     => 'required',
	    	'uiorder_deadlinedate'   => 'required',
	    	'uiorder_detail'  	     => 'required',
            'uiorder_logo'  	     => 'required',
            'uiorder_images'  	     => 'required',
            'uiorder_document'  	 => 'required',
            'uiorder_theme'  	     => 'required',
            // 'uiorder_other'  	     => 'required',
        ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$add = array(
		'uiorder_title'		    => $request->uiorder_title,
		'uiorder_deadlinedate'  => $request->uiorder_deadlinedate,
		'uiorder_detail' 		=> $request->uiorder_detail,
        'uiorder_date' 		    => date('Y-m-d'),
        'uiorderstatus_id' 		=> 1,
        'brand_id' 		        => $request->brand_id,
		'status_id'		 		=> 1,
		'created_by'	 		=> $request->user_id,
		'created_at'	 		=> date('Y-m-d h:i:s'),
		);
		DB::table('uiorder')->insert($add);
        $uiorder_id = DB::getPdo()->lastInsertId();
        if (isset($request->uiorder_logo)) {
			$logoattachment = $request->uiorder_logo;
	    	$index = 0 ;
	    	$logofilename = array();
			foreach($logoattachment as $logoattachments){
				$savelogoattachment = array();
	    		if($logoattachments->isValid()){
	    			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $uiorder_id;
					$extension = $logoattachments->getClientOriginalExtension();
		            $logofilename = $logoattachments->getClientOriginalName();
		            $logofilename = $logoattachments->move(public_path('uiorder_logo/'.$foldername),$logofilename);
		            $logofilename = $logoattachments->getClientOriginalName();
				  	$savelogoattachment = array(
					'uiattachment_name'	    => $logofilename,
					'uiorder_id'			=> $uiorder_id,
                    'uiattachmenttype_id'	=> 1,
                    'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
	    	DB::table('uiattachment')->insert($savelogoattachment);
	    	}
    	}
        if (isset($request->uiorder_images)) {
			$attachment = $request->uiorder_images;
	    	$index = 0 ;
	    	$attachmentname = array();
			foreach($attachment as $attachments){
				$saveattachment = array();
	    		if($attachments->isValid()){
	    			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $uiorder_id;
					$extension = $attachments->getClientOriginalExtension();
		            $attachmentname = $attachments->getClientOriginalName();
		            $attachmentname = $attachments->move(public_path('uiorder_images/'.$foldername),$attachmentname);
		            $attachmentname = $attachments->getClientOriginalName();
				  	$saveattachment = array(
					'uiattachment_name'	    => $attachmentname,
					'uiorder_id'			=> $uiorder_id,
                    'uiattachmenttype_id'	=> 2,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
	    	DB::table('uiattachment')->insert($saveattachment);
	    	}
    	}
        if (isset($request->uiorder_document)) {
			$document = $request->uiorder_document;
	    	$index = 0 ;
	    	$documentname = array();
			foreach($document as $documents){
				$savedocument = array();
	    		if($documents->isValid()){
	    			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $uiorder_id;
					$extension = $documents->getClientOriginalExtension();
		            $documentname = $documents->getClientOriginalName();
		            $documentname = $documents->move(public_path('uiorder_document/'.$foldername),$documentname);
		            $documentname = $documents->getClientOriginalName();
				  	$savedocument = array(
					'uiattachment_name'	    => $documentname,
					'uiorder_id'			=> $uiorder_id,
                    'uiattachmenttype_id'	=> 3,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
	    	DB::table('uiattachment')->insert($savedocument);
	    	}
    	}
        if (isset($request->uiorder_theme)) {
			$theme = $request->uiorder_theme;
	    	$index = 0 ;
	    	$themename = array();
			foreach($theme as $themes){
				$savetheme = array();
	    		if($themes->isValid()){
	    			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $uiorder_id;
					$extension = $themes->getClientOriginalExtension();
		            $themename = $themes->getClientOriginalName();
		            $themename = $themes->move(public_path('uiorder_theme/'.$foldername),$themename);
		            $themename = $themes->getClientOriginalName();
				  	$savetheme = array(
					'uiattachment_name'	    => $themename,
					'uiorder_id'			=> $uiorder_id,
                    'uiattachmenttype_id'	=> 4,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
	    	DB::table('uiattachment')->insert($savetheme);
	    	}
    	}
        if (isset($request->uiorder_other)) {
			$other = $request->uiorder_other;
	    	$index = 0 ;
	    	$othername = array();
			foreach($other as $others){
				$saveother = array();
	    		if($others->isValid()){
	    			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $uiorder_id;
					$extension = $others->getClientOriginalExtension();
		            $othername = $others->getClientOriginalName();
		            $othername = $others->move(public_path('uiorder_other/'.$foldername),$othername);
		            $othername = $others->getClientOriginalName();
				  	$saveother = array(
					'uiattachment_name'	    => $othername,
					'uiorder_id'			=> $uiorder_id,
                    'uiattachmenttype_id'	=> 4,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
	    	DB::table('uiattachment')->insert($saveother);
	    	}
    	}
		if($add){
			return response()->json(['message' => 'Saved Successfully'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
    public function uiorderlist(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'brand_id'  		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if($request->role_id == 1 || $request->role_id == 3 || $request->role_id == 10 || $request->role_id == 11){
			$basic = DB::table('uiorderdetail')
			->select('*')
			->where('status_id','=',1)
			->where('brand_id','=',$request->brand_id)
			->paginate(30);
		}else if($request->role_id == 6 || $request->role_id == 7){
			$basic = DB::table('uiorderdetail')
			->select('*')
			->where('status_id','=',1)
			->where('created_by','=',$request->user_id)
			->where('brand_id','=',$request->brand_id)
			->paginate(30);
		}else{
			$basic = DB::table('uiorderdetail')
			->select('*')
			->where('status_id','=',1)
			->where('uiorder_assignto','=',$request->user_id)
			->where('brand_id','=',$request->brand_id)
			->paginate(30);
		}
	    if($basic){
			return response()->json([ 'data' => $basic, 'message' => 'Ui Order List'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
	public function updateuiorderstatus(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'uiorder_id'			=> 'required',
		]);
     	if ($validate->fails()) {    
			return response()->json("UI Order Id Is Required", 400);
		}
		if($request->uiorderstatus_id == 2){
			$validate = Validator::make($request->all(), [ 
				'uiorder_assignto'	=> 'required',
			]);
			if ($validate->fails()) {    
				return response()->json("Assign User Id Is Required", 400);
			}
			$update  = DB::table('uiorder')
			->where('uiorder_id','=',$request->uiorder_id)
			->update([
				'uiorder_assignto'	=> $request->uiorder_assignto,
				'uiorderstatus_id'	=> 2,
			]); 
		}else{
			$update  = DB::table('uiorder')
			->where('uiorder_id','=',$request->uiorder_id)
			->update([
				'uiorderstatus_id'	=> 3,
			]); 
		}
		if($update){
			return response()->json(['message' => 'Ui Order Status Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function uiorderdetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	    	'uiorder_id'  		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$data = DB::table('uiorderdetail')
		->select('*')
		->where('status_id','=',1)
		->where('uiorder_id','=',$request->uiorder_id)
		->first();
		$logo = DB::table('uiattachment')
		->select('*')
		->where('status_id','=',1)
		->where('uiattachmenttype_id','=',1)
		->where('uiorder_id','=',$request->uiorder_id)
		->get();
		$images = DB::table('uiattachment')
		->select('*')
		->where('status_id','=',1)
		->where('uiattachmenttype_id','=',2)
		->where('uiorder_id','=',$request->uiorder_id)
		->get();
		$document = DB::table('uiattachment')
		->select('*')
		->where('status_id','=',1)
		->where('uiattachmenttype_id','=',3)
		->where('uiorder_id','=',$request->uiorder_id)
		->get();
		$theme = DB::table('uiattachment')
		->select('*')
		->where('status_id','=',1)
		->where('uiattachmenttype_id','=',4)
		->where('uiorder_id','=',$request->uiorder_id)
		->get();
		$other = DB::table('uiattachment')
		->select('*')
		->where('status_id','=',1)
		->where('uiattachmenttype_id','=',5)
		->where('uiorder_id','=',$request->uiorder_id)
		->get();
		$logopath = URL::to('/')."/public/uiorder_logo/".$request->uiorder_id.'/';
		$imagespath = URL::to('/')."/public/uiorder_images/".$request->uiorder_id.'/';
		$documentpath = URL::to('/')."/public/uiorder_document/".$request->uiorder_id.'/';
		$themepath = URL::to('/')."/public/uiorder_theme/".$request->uiorder_id.'/';
		$otherpath = URL::to('/')."/public/uiorder_other/".$request->uiorder_id.'/';
		if($data){
			return response()->json(['data' => $data, 'logo' => $logo, 'images' => $images, 'document' => $document, 'theme' => $theme, 'other' => $other, 'logopath' => $logopath, 'imagespath' => $imagespath, 'documentpath' => $documentpath, 'themepath' => $themepath, 'otherpath' => $otherpath, 'message' => 'Ui Order Detail'],200);
		}else{
			return response()->json(['message' => 'Oops! Something Went Wrong.'],400);
		}
	}
}