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

class taskController extends Controller      
{
	public $emptyarray = array();
	public function creattask(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_title' 			=> 'required',
	      'task_description' 	=> 'required',
	      'order_id' 			=> 'required',
	      'order_token' 		=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$task_token = openssl_random_pseudo_bytes(7);
    	$task_token = bin2hex($task_token);
		$basic = array(
		'task_title' 		=> $request->task_title,
		'task_description' 	=> $request->task_description,
		'task_token' 		=> $task_token,
		'taskstatus_id'		=> 1,
		'order_id'			=> $request->order_id,
		'order_token'		=> $request->order_token,
		'status_id'			=> 1,
		'created_by'		=> $request->user_id,
		'created_at'		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('task')->insert($basic);
		$task_id = DB::getPdo()->lastInsertId();
		if (isset($request->member)) {
			foreach ($request->member as $members) {
				$member = array(
				'task_id'		=> $task_id,
				'user_id'		=> $members['user_id'],
				'status_id' 	=> 1,
				'created_by'	=> $request->user_id,
				'created_at'	=> date('Y-m-d h:i:s'),
				);
				DB::table('taskmember')->insert($member);
			}
		}
		if (isset($request->attachment)) {
			$attachment = $request->attachment;
	    	$index = 0 ;
	    	$filename = array();
			foreach($attachment as $attachments){
				$saveattachment = array();
	    		if($attachments->isValid()){
	    			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $task_token;
					$extension = $attachments->getClientOriginalExtension();
		            $filename = $attachments->getClientOriginalName();
		            $filename = $attachments->move(public_path('task/'.$foldername),$filename);
		            $filename = $attachments->getClientOriginalName();
				  	$saveattachment = array(
					'taskattachment_name'	=> $filename,
					'task_id'				=> $task_id,
					'task_token'			=> $task_token,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
	    	DB::table('taskattachment')->insert($saveattachment);
	    	}
    	}
		if($save){
			return response()->json(['message' => 'Task Created Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updatetask(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_id' 			=> 'required',
	      'task_token' 			=> 'required',
	      'task_title'	 		=> 'required',
	      'task_description'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$update  = DB::table('task')
		->where('task_id','=',$request->task_id)
		->update([
		'task_title' 			=> $request->task_title,
		'task_description' 		=> $request->task_description,
		'updated_by'			=> $request->user_id,
		'updated_at'			=> date('Y-m-d h:i:s'),
		]);
		if (isset($request->member)) {
			foreach ($request->member as $members) {
				$member = array(
				'task_id'		=> $request->task_id,
				'user_id'		=> $members['user_id'],
				'status_id' 	=> 1,
				'created_by'	=> $request->user_id,
				'created_at'	=> date('Y-m-d h:i:s'),
				);
				DB::table('taskmember')->insert($member);
			}
		}
		if (isset($request->attachment)) {
			$attachment = $request->attachment;
	    	$index = 0 ;
	    	$filename = array();
			foreach($attachment as $attachments){
				$saveattachment = array();
	    		if($attachments->isValid()){
	    			$number = rand(1,999);
			        $numb = $number / 7 ;
			        $foldername = $request->task_token;
					$extension = $attachments->getClientOriginalExtension();
		            $filename = $attachments->getClientOriginalName();
		            $filename = $attachments->move(public_path('task/'.$foldername),$filename);
		            $filename = $attachments->getClientOriginalName();
				  	$saveattachment = array(
					'taskattachment_name'	=> $filename,
					'task_id'				=> $request->task_id,
					'task_token'			=> $request->task_token,
					'status_id' 			=> 1,
					'created_by'			=> $request->user_id,
					'created_at'			=> date('Y-m-d h:i:s'),
					);
			    }else{
					return response()->json("Invalid File", 400);
				}
	    	DB::table('taskattachment')->insert($saveattachment);
	    	}
    	}
		if($update){
			return response()->json(['message' => 'Task Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function tasklist(Request $request){
		$tasklist = DB::table('task')
		->select('task_id','task_title','task_description','task_token','taskstatus_id')
		->where('status_id','=',1)
		->paginate(30);
		$taskpath = URL::to('/')."/public/task/".$basicdetail->task_token."/";
		if(isset($tasklist)){
			return response()->json(['data' => $tasklist, 'taskpath' => $taskpath, 'message' => 'Task List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Task List'],200);
		}
	}
	public function statuswisetasklist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'taskstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Task Status Id Required", 400);
		}
		$tasklist = DB::table('task')
		->select('task_id','task_title','task_description','task_token','taskstatus_id')
		->where('taskstatus_id','=',$request->taskstatus_id)
		->where('status_id','=',1)
		->paginate(30);
		$taskpath = URL::to('/')."/public/task/".$basicdetail->task_token."/";
		if(isset($tasklist)){
			return response()->json(['data' => $tasklist, 'taskpath' => $taskpath, 'message' => 'Task List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Task List'],200);
		}
	}
	public function taskdetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Task Id Required", 400);
		}
		$basicdetail = DB::table('task')
		->select('*')
		->where('task_id','=',$request->task_id)
		->where('status_id','=',1)
		->first();
		$taskmember = DB::table('taskmember')
		->select('user_id')
		->where('task_id','=',$request->task_id)
		->where('status_id','=',1)
		->get();
		$sorttaskmember = array();
		foreach ($taskmember as $taskmembers) {
			$sorttaskmember[] = $taskmembers->user_id;
		}
		$memberdetail = DB::table('user')
		->select('user_id','user_name','user_email','user_picture')
		->whereIn('user_id',$sorttaskmember)
		->where('status_id','=',1)
		->get();
		$attachmentdetail = DB::table('taskattachment')
		->select('*')
		->where('task_id','=',$request->task_id)
		->where('status_id','=',1)
		->get();
		$taskpath = URL::to('/')."/public/task/".$basicdetail->task_token."/";
		if($basicdetail){
			return response()->json(['basicdetail' => $basicdetail, 'memberdetail' => $memberdetail, 'attachmentdetail' => $attachmentdetail, 'taskpath' => $taskpath,'message' => 'Task Detail'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function deletetask(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Task Id Required", 400);
		}
		$delete  = DB::table('task')
		->where('task_id','=',$request->task_id)
		->update([
		'status_id' 	=> 2,
		'deleted_by'	=> $request->user_id,
		'deleted_at'	=> date('Y-m-d h:i:s'),
		]);
		if($delete){
			return response()->json(['message' => 'Task Deleted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function removefromtask(Request $request){
		if (isset($request->taskattachment_id)) {
			$delete  = DB::table('taskattachment')
			->where('taskattachment_id','=',$request->taskattachment_id )
			->update([
			'status_id' 	=> 2,
			'deleted_by'	=> $request->user_id,
			'deleted_at'	=> date('Y-m-d h:i:s'),
			]);
		}else if (isset($request->taskmember_id)) {
			$delete  = DB::table('taskmember')
			->where('taskmember_id','=',$request->taskmember_id)
			->update([
			'status_id' 	=> 2,
			'updated_by'	=> $request->user_id,
			'updared_at'	=> date('Y-m-d h:i:s'),
			]);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
		if($delete){
			return response()->json(['message' => 'Successfully Removed From Task'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function orderwisetasklist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Order Id Required", 400);
		}
		$tasklist = DB::table('task')
		->select('task_id','task_title','task_description','task_token','taskstatus_id')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->paginate(30);
		if(isset($tasklist)){
			return response()->json(['data' => $tasklist, 'message' => 'Task List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Task List'],200);
		}
	}
	public function addmembertotask(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_id' 			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if (isset($request->member)) {
			foreach ($request->member as $members) {
				$checkmember = DB::table('taskmember')
				->select('user_id')
				->where('task_id','=',$request->task_id)
				->where('user_id','=',$members['user_id'])
				->where('status_id','=',1)
				->count();
				if ($checkmember > 0) {
					return response()->json(['message' => 'Member Already Exist'],200);		
				}else{
					$member = array(
					'task_id'		=> $request->task_id,
					'user_id'		=> $members['user_id'],
					'status_id' 	=> 1,
					'created_by'	=> $request->user_id,
					'created_at'	=> date('Y-m-d h:i:s'),
					);
					$save = DB::table('taskmember')->insert($member);
				}
			}
		}else{
			return response()->json(['message' => 'Please Select Member To Add'],400);
		}
		if($save){
			return response()->json(['message' => 'Members Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}