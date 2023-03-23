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

class rawdataController extends Controller
{
	public $emptyarray = array();
    public function rawdatasheetlist(Request $request){
		$data = DB::table('rawdatasheet')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($data)){
			return response()->json(['data' => $data, 'message' => 'Sheet List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Sheet List'],200);
		}
	}
   public function rawdatalist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'rawdatasheet_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
        $data = DB::table('rawdata')
		->select('*')
		->where('rawdatasheet_id','=',$request->rawdatasheet_id)
		->where('status_id','=',1)
		->paginate(30);
		if($data){
			return response()->json(['data' => $data, 'message' => 'Raw Data List'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function rawdatadetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'rawdata_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
        $data = DB::table('rawdata')
		->select('*')
		->where('rawdata_id','=',$request->rawdata_id)
		->where('status_id','=',1)
		->first();
		if($data){
			return response()->json(['data' => $data, 'message' => 'Raw Data Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function saverawdatafollowup(Request $request){
        $validate = Validator::make($request->all(), [ 
			'rawdatafollowup_comment'	=> 'required',
            'rawdata_id'				=> 'required',
		]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
		$adds[] = array(
			'rawdatafollowup_comment' 	=> $request->rawdatafollowup_comment,
			'rawdata_id' 				=> $request->rawdata_id,
			'status_id'				    => 1,
			'created_by'			    => $request->user_id,
			'created_at'			    => date('Y-m-d h:i:s'),
		);
        $save = DB::table('rawdatafollowup')->insert($adds);
		if($save){
			return response()->json(['message' => 'Payment Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function rawdatafollowuplist(Request $request){
		$validate = Validator::make($request->all(), [ 
            'rawdata_id'	=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
		$data = DB::table('rawdatafollowuplist')
		->select('*')
		->where('rawdata_id','=',$request->rawdata_id)
		->where('status_id','=',1)
		->get();
		if(isset($data)){
			return response()->json(['data' => $data, 'message' => 'Followup List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Followup List'],200);
		}
	}
}