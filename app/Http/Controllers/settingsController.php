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

class settingsController extends Controller
{
	public $emptyarray = array();
	public function role(Request $request){
		$validatetoken = Validator::make($request->all(), [ 
	      'role_id' => 'required',
    	]);
    	if ($validatetoken->fails()) {    
			return response()->json("Role Id Required", 400);
		}
		$getroles = DB::table('role')
		->select('role_id','role_name')
		->where('role_id','>=',$request->role_id)
		->where('status_id','=',1)
		->get();
		if (isset($getroles)) {
			return response()->json(['data' => $getroles,'message' => 'CRM Roles'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'CRM Roles'],200);
		}
	}
	public function countrylist(Request $request){
		$getcountry = DB::table('country')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($getcountry)){
			return response()->json(['data' => $getcountry,'message' => 'Country List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Country List'],200);
		}
	}
	public function stateslist(Request $request){
		$validatetoken = Validator::make($request->all(), [ 
	      'country_id' => 'required',
    	]);
    	if ($validatetoken->fails()) {    
			return response()->json("Country Id Required", 400);
		}
		$getstates = DB::table('state')
		->select('*')
		->where('country_id','=',$request->country_id)
		->where('status_id','=',1)
		->get();
		if(isset($getstates)){
			return response()->json(['data' => $getstates,'message' => 'States List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'State List'],200);
		}
	}
	public function citieslist(Request $request){
		$validatetoken = Validator::make($request->all(), [ 
	      'state_id' => 'required',
    	]);
    	if ($validatetoken->fails()) {    
			return response()->json("State Id Required", 400);
		}
		$getcity = DB::table('city')
		->select('*')
		->where('state_id','=',$request->state_id)
		->where('status_id','=',1)
		->get();
		if(isset($getcity)){
			return response()->json(['data' => $getcity,'message' => 'Cities List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'City List'],200);
		}
	}
	public function ordertype(Request $request){
		$ordertypelist = DB::table('ordertype')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($ordertypelist)){
			return response()->json(['data' => $ordertypelist, 'message' => 'Order Type List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Order Type List'],200);
		}
	}
	public function brandtype(Request $request){
		$brandtypelist = DB::table('brandtype')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($brandtypelist)){
			return response()->json(['data' => $brandtypelist, 'message' => 'Brand Type List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Brand Type List'],200);
		}
	}
	public function leadstatus(Request $request){
		$leadstatuslist = DB::table('leadstatus')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($leadstatuslist)){
			return response()->json(['data' => $leadstatuslist, 'message' => 'Lead Status List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Lead Status List'],200);
		}
	}
	public function orderstatus(Request $request){
		$orderstatuslist = DB::table('orderstatus')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($orderstatuslist)){
			return response()->json(['data' => $orderstatuslist, 'message' => 'Order Status List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Order Status List'],200);
		}
	}
	public function orderpaymentstatus(Request $request){
		$orderpaymentstatuslist = DB::table('orderpaymentstatus')
		->select('*')
		->where('status_id','=',1)
		->get();
		if(isset($orderpaymentstatuslist)){
			return response()->json(['data' => $orderpaymentstatuslist, 'message' => 'Order Payment Status List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Order Payment Status List'],200);
		}
	}
	public function taskstatus(Request $request){
		if($request->role_id > 11){
			$taskstatuslist = DB::table('taskstatus')
			->select('*')
			->whereIn('taskstatus_id',[2,3,9])
			->where('status_id','=',1)
			->get();
		}elseif($request->role_id == 6 || $request->role_id = 7){
			$taskstatuslist = DB::table('taskstatus')
			->select('*')
			->whereIn('taskstatus_id',[4,5,6,7,8])
			->where('status_id','=',1)
			->get();
		}else{
			$taskstatuslist = DB::table('taskstatus')
		    ->select('*')
		    ->where('status_id','=',1)
		    ->get();
		}
		
		if(isset($taskstatuslist)){
			return response()->json(['data' => $taskstatuslist, 'message' => 'Task Status List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Task Status List'],200);
		}
	}
	public function orderquestion(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'ordertype_id'			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Fields Required", 400);
		}
		$orderquestionlist = DB::table('orderquestion')
		->select('*')
		->where('ordertype_id','=',$request->ordertype_id)
		->where('status_id','=',1)
		->get();
		if($orderquestionlist){
			return response()->json(['data' => $orderquestionlist, 'message' => 'Order Question List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Order Question List'],200);
		}
	}
	public function patchquerystatus(Request $request){
		$list = DB::table('patchquerystatus')
		->select('*')
		->where('status_id','=',1)
		->get();
		if($list){
			return response()->json(['data' => $list, 'message' => 'Patch Query Status List'],200);
		}else{
			return response()->json(['data' => $emptyarray,'message' => 'Patch Query Status List'],200);
		}
	}
}