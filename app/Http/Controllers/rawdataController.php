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
	public function uploadrawdata(Request $request)
	{
		$validate = Validator::make($request->all(), [ 
	    	'rawdatafile'  		=> 'required',
			'rawdatasheet_id'	=> 'required',
	    ]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$file = $request->file('rawdatafile');
		if ($file) {
			$filename = $file->getClientOriginalName();
			$extension = $file->getClientOriginalExtension();
			$tempPath = $file->getRealPath();
			$fileSize = $file->getSize();
			$valid_extension = "csv";
			$maxFileSize = 2097152;
			if ($extension == $valid_extension) {
				if ($fileSize <= $maxFileSize) {
				$location = 'rawdataupload';
				$file->move(public_path('rawdataupload/'),$filename);
				// In case the uploaded file path is to be stored in the database 
				$filepath = public_path($location . "/" . $filename);
				$file = fopen($filepath, "r");
				$importData_arr = array();
				$i = 0;
				//Read the contents of the uploaded file 
				while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
					$num = count($filedata);
					// Skip first row (Remove below comment if you want to skip the first row)
					if ($i == 0) {
						$i++;
						continue;
					}
					for ($c = 0; $c < $num; $c++) {
						$importData_arr[$i][] = $filedata[$c];
					}
					$i++;
				}
				fclose($file);
				$j = 0;
				foreach ($importData_arr as $importData) {
					try {
						$adds = array(
							'rawdata_name'			=> $importData[0],
							'rawdata_altname'		=> $importData[1],
							'rawdata_email'			=> $importData[2],
							'rawdata_phone'			=> $importData[3],
							'rawdata_dob'			=> $importData[4],
							'rawdata_city'			=> $importData[5],
							'rawdata_state'			=> $importData[6],
							'rawdata_zip'			=> $importData[7],
							'rawdata_country'		=> $importData[8],
							'rawdata_address'		=> $importData[9],
							'rawdata_mmin'			=> $importData[10],
							'rawdata_card'			=> $importData[11],
							'rawdata_bank'			=> $importData[12],
							'rawdata_bankno'		=> $importData[13],
							'rawdata_card2'			=> $importData[14],
							'rawdata_cardtype'		=> $importData[15],
							'rawdata_ssn'			=> $importData[16],
							'rawdata_cvc'			=> $importData[17],
							'rawdata_cvc2'			=> $importData[18],
							'rawdata_cvc3'			=> $importData[19],
							'rawdata_cvc4'			=> $importData[20],
							'rawdata_cc#'			=> $importData[21],
							'rawdata_exp'			=> $importData[22],
							'rawdata_exp2'			=> $importData[23],
							'rawdata_exp3'			=> $importData[24],
							'rawdata_details'		=> $importData[25],
							'rawdata_details2'		=> $importData[26],
							'rawdata_details3'		=> $importData[27],
							'rawdata_details4'		=> $importData[28],
							'rawdata_details5'		=> $importData[29],
							'rawdata_details6'		=> $importData[30],
							'rawdata_details7'		=> $importData[31],
							'rawdata_charge'		=> $importData[32],
							'rawdata_charge2'		=> $importData[33],
							'rawdata_charge3'		=> $importData[34],
							'rawdata_random'		=> $importData[35],
							'rawdata_random2'		=> $importData[36],
							'rawdata_random3'		=> $importData[37],
							'rawdata_random4'		=> $importData[38],
							'rawdata_random5'		=> $importData[39],
							'rawdata_random6'		=> $importData[40],
							'rawdata_random7'		=> $importData[41],
							'rawdata_random8'		=> $importData[42],
							'rawdata_random9'		=> $importData[43],
							'rawdata_random10'		=> $importData[44],
							'rawdata_random11'		=> $importData[45],
							'rawdata_random12'		=> $importData[46],
							'rawdata_random13'		=> $importData[47],
							'rawdata_random14'		=> $importData[48],
							'rawdata_random15'		=> $importData[49],
							'rawdata_random16'		=> $importData[50],
							'rawdatasheet_id'		=> $request->rawdatasheet_id,
							'status_id'				=> 1,
							'created_by'			=> $request->user_id,
							'created_at'			=> date('Y-m-d h:i:s'),
						);
					DB::table('rawdata')->insert($adds);
					} catch (\Exception $e) {
						DB::rollBack();
						return response()->json("Oops! Something Went Wrong", 400);
					}
				}
					return response()->json(['message' => 'Successfully Uploaded'],200);
				} else {
					return response()->json("File Size Too Large", 400);
				}
			} else {
					return response()->json("Invalid Format", 400);
			}
		} else {
				return response()->json("No File Uploaded, Invalid Upload", 400);
		}
	}
}