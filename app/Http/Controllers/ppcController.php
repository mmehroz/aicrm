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

class ppcController extends Controller
{
	public $emptyarray = array();
	public function addppc( Request $request){
		$validate = Validator::make($request->all(), [ 
	      'ppc_amount' 			=> 'required',
	      'ppc_date' 			=> 'required',
	      'ppc_description'		=> 'required',
	      'brand_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds = array(
		'ppc_amount' 		=> $request->ppc_amount,
		'ppc_date' 			=> $request->ppc_date,
		'ppc_description'	=> $request->ppc_description,
		'brand_id' 			=> $request->brand_id,
		'status_id'			=> 1,
		'created_by'		=> $request->user_id,
		'created_at'		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('ppc')->insert($adds);
		if($save){
			return response()->json(['data' => $adds,'message' => 'Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateppc(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'ppc_id' 				=> 'required',
	      'ppc_amount' 			=> 'required',
	      'ppc_date' 			=> 'required',
	      'ppc_description'		=> 'required',
	      'brand_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$update  = DB::table('ppc')
		->where('ppc_id','=',$request->ppc_id)
		->update([
		'ppc_amount' 		=> $request->ppc_amount,
		'ppc_date' 			=> $request->ppc_date,
		'ppc_description'	=> $request->ppc_description,
		'brand_id' 			=> $request->brand_id,
		'updated_by'	 	=> $request->user_id,
		'updated_at'	 	=> date('Y-m-d h:i:s'),
		]);
		if($update){
			return response()->json(['message' => 'Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function ppclist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      // 'from' 		=> 'required',
	      // 'to' 			=> 'required',
			  'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$ppclist = DB::table('ppcdetail')
		->select('*')
		// ->whereBetween('ppc_date', [$request->from, $request->to])
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->get();
		if(isset($ppclist)){
			return response()->json(['data' => $ppclist, 'message' => 'PPC Log'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'PPC Log'],200);
		}
	}
	public function ppcdetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'ppc_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Brand Id Required", 400);
		}
		$ppcdetail = DB::table('ppcdetail')
		->select('*')
		->where('ppc_id','=',$request->ppc_id)
		->where('status_id','=',1)
		->first();
		if($ppcdetail){
			return response()->json(['data' => $ppcdetail,'message' => 'Brand Detail'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deleteppc(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'ppc_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Brand Id Required", 400);
		}
		$delete  = DB::table('ppc')
		->where('ppc_id','=',$request->ppc_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($delete){
			return response()->json(['message' => 'Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function assignppc( Request $request){
		$validate = Validator::make($request->all(), [ 
	      'assignppc_amount' 			=> 'required',
	      'assignppc_month' 			=> 'required',
	      'assignppc_description'		=> 'required',
	      'assignuser_id'				=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds = array(
		'assignppc_amount' 		=> $request->assignppc_amount,
		'assignppc_month' 		=> $request->assignppc_month,
		'assignppc_description'	=> $request->assignppc_description,
		'user_id' 				=> $request->assignuser_id,
		'status_id'				=> 1,
		'created_by'			=> $request->user_id,
		'created_at'			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('assignppc')->insert($adds);
		if($save){
			return response()->json(['data' => $adds,'message' => 'Saved Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateassignppc(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'assignppc_id' 			=> 'required',
	      'assignppc_amount' 		=> 'required',
	      'assignppc_month' 		=> 'required',
	      'assignppc_description'	=> 'required',
	      'assignuser_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$update  = DB::table('assignppc')
		->where('assignppc_id','=',$request->assignppc_id)
		->update([
		'assignppc_amount' 		=> $request->assignppc_amount,
		'assignppc_month' 		=> $request->assignppc_month,
		'assignppc_description'	=> $request->assignppc_description,
		'user_id' 				=> $request->assignuser_id,
		'updated_by'	 		=> $request->user_id,
		'updated_at'	 		=> date('Y-m-d h:i:s'),
		]);
		if($update){
			return response()->json(['message' => 'Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function assignppclist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      // 'assignppc_month' 		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$assignppclist = DB::table('assignppcdetail')
		->select('*')
		// ->whereBetween('assignppc_month', '=', $request->assignppc_month)
		->where('status_id','=',1)
		->get();
		if(isset($assignppclist)){
			return response()->json(['data' => $assignppclist, 'message' => 'PPC Log'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'PPC Log'],200);
		}
	}
	public function assignppcdetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'assignppc_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Assign PPC Id Required", 400);
		}
		$assignppcdetail = DB::table('assignppcdetail')
		->select('*')
		->where('assignppc_id','=',$request->assignppc_id)
		->where('status_id','=',1)
		->first();
		if($assignppcdetail){
			return response()->json(['data' => $assignppcdetail,'message' => 'Assign PPC Detail'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deleteassignppc(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'assignppc_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Assign PPC Id Required", 400);
		}
		$delete  = DB::table('assignppc')
		->where('assignppc_id','=',$request->assignppc_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]); 
		if($delete){
			return response()->json(['message' => 'Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function monthlyppcbudget(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'assignppc_month' => 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$budget = DB::table('assignppc')
		->select('*')
		->where('assignppc_month', '=', $request->assignppc_month)
		->where('user_id', '=', $request->user_id)
		->where('status_id','=',1)
		->sum('assignppc_amount');
		if(isset($budget)){
			return response()->json(['budget' => $budget, 'message' => 'Monthly PPC Budget'],200);
		}else{
			return response()->json(['budget' => 0, 'message' => 'PPC Log'],200);
		}
	}
}