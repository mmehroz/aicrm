<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use ZipArchive;
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
	      'task_deadlinedate' 	=> 'required',
	      'order_id' 			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$order_details = DB::table('order')
		->select('order_token','brand_id')
		->where('order_id','=',$request->order_id)
		->where('status_id','=',1)
		->first();
		$task_token = openssl_random_pseudo_bytes(7);
    	$task_token = bin2hex($task_token);
		$basic = array(
		'task_title' 		=> $request->task_title,
		'task_description' 	=> $request->task_description,
		'task_deadlinedate' => $request->task_deadlinedate,
		'task_manager' 		=> $request->task_manager,
		'task_token' 		=> $task_token,
		'task_date' 		=> date('Y-m-d'),
		'taskstatus_id'		=> 1,
		'order_id'			=> $request->order_id,
		'order_token'		=> $order_details->order_token,
		'brand_id'		    => $order_details->brand_id,
		'status_id'			=> 1,
		'created_by'		=> $request->user_id,
		'created_at'		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('task')->insert($basic);
		$task_id = DB::getPdo()->lastInsertId();
		if (!empty($request->task_manager)) {
			$member = array(
			'task_id'		=> $task_id,
			'user_id'		=> $request->task_manager,
			'status_id' 	=> 1,
			'created_by'	=> $request->user_id,
			'created_at'	=> date('Y-m-d h:i:s'),
			);
			DB::table('taskmember')->insert($member);
		}
		if (isset($request->member)) {
			foreach ($request->member as $members) {
				if ($request->task_manager != $members['user_id']) {
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
		}
		if (isset($request->orderattachment)) {
			foreach ($request->orderattachment as $orderattachments) {
				$orderattachment = DB::table('orderattachment')
				->select('*')
				->where('orderattachment_id','=',$orderattachments)
				->first();
				$saveattachment = array(
				'taskattachment_name'	=> $orderattachment->orderattachment_name,
				'task_id'				=> $task_id,
				'task_token'			=> $task_token,
				'attachmenttype'		=> 3,
				'status_id' 			=> 1,
				'created_by'			=> $request->user_id,
				'created_at'			=> date('Y-m-d h:i:s'),
				);
				DB::table('taskattachment')->insert($saveattachment);
				$getextension = explode('.', $orderattachment->orderattachment_name);
				if($getextension[1] == 'jpg' || $getextension[1] == 'jpeg' || $getextension[1] == 'png' ){
					$attachmentname = $orderattachment->orderattachment_name;
				}else{
					$attachmentname = 'no_image.jpg';
				}
				DB::table('task')
					->where('task_id','=',$task_id)
					->update([
					'task_cover' 		=> $attachmentname,
					'task_covertype' 	=> "Order",
				]);
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
			$getextension = explode('.', $filename);
				if($getextension[1] == 'jpg' || $getextension[1] == 'jpeg' || $getextension[1] == 'png' ){
					$attachmentname = $filename;
				}else{
					$attachmentname = 'no_image.jpg';
				}
				DB::table('task')
					->where('task_id','=',$task_id)
					->update([
					'task_cover' 		=> $attachmentname,
					'task_covertype' 	=> "Task",
				]);
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
	      // 'task_token' 			=> 'required',
	      'task_title'	 		=> 'required',
	      'task_description'	=> 'required',
	      'task_deadlinedate'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$task_token = DB::table('task')
		->select('task_token')
		->where('task_id','=',$request->task_id)
		->first();
		$update  = DB::table('task')
		->where('task_id','=',$request->task_id)
		->update([
		'task_title' 			=> $request->task_title,
		'task_description' 		=> $request->task_description,
		'task_deadlinedate' 	=> $request->task_deadlinedate,
		'task_manager' 			=> $request->task_manager,
		'updated_by'			=> $request->user_id,
		'updated_at'			=> date('Y-m-d h:i:s'),
		]);
		if (!empty($request->task_manager)) {
			$checkmember = DB::table('taskmember')
			->select('user_id')
			->where('task_id','=',$request->task_id)
			->where('user_id','=',$request->task_manager)
			->where('status_id','=',1)
			->count();
			if ($checkmember = 0) {
				$member = array(
				'task_id'		=> $request->task_id,
				'user_id'		=> $request->task_manager,
				'status_id' 	=> 1,
				'created_by'	=> $request->user_id,
				'created_at'	=> date('Y-m-d h:i:s'),
				);
				DB::table('taskmember')->insert($member);
			}
		}
		if (isset($request->member)) {
			foreach ($request->member as $members) {
				$checkmember = DB::table('taskmember')
				->select('user_id')
				->where('task_id','=',$request->task_id)
				->where('user_id','=',$members['user_id'])
				->where('status_id','=',1)
				->count();
				if ($checkmember = 0) {
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
					'task_id'				=> $request->task_id,
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
		if($update){
			return response()->json(['message' => 'Task Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function tasklist(Request $request){
		if ($request->role_id <= 3) {
			$tasklist = DB::table('tasklist')
			->select('*')
			->where('status_id','=',1)
			->orderBy('task_id','DESC')
			->paginate(30);
		}else if ($request->role_id == 6 || $request->role_id == 7) {
			$tasklist = DB::table('tasklist')
			->select('*')
			->where('ordercreator','=',$request->user_id)
			->where('status_id','=',1)
			->orderBy('task_id','DESC')
			->paginate(30);
		}else if ($request->role_id == 10) {
			$tasklist = DB::table('tasklist')
			->select('*')
			->where('created_by','=',$request->user_id)
			->where('status_id','=',1)
			->orderBy('task_id','DESC')
			->paginate(30);
		}else{
			$tasklist =  DB::table('memberstasklist')
			->select('*')
			->where('taskuser_id','=',$request->user_id)
			->where('task_workby','=',$request->user_id)
			->where('memberstatus_id','=',1)
			->where('status_id','=',1)
			->groupBy('task_id')
			->orderBy('task_id','DESC')
			->paginate(30);
		}
		if(isset($tasklist)){
			return response()->json(['data' => $tasklist, 'message' => 'Task List'],200);
		}else{
			return response()->json(['data' => $emptyarray, 'message' => 'Task List'],200);
		}
	}
	public function statuswisetasklist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Brand Id Required", 400);
		}
		$taskstatus = DB::table('taskstatus')
		->select('*')
		->where('status_id','=',1)
		->get();
		$task = array();
		foreach ($taskstatus as $taskstatuss) {
			if ($request->role_id <= 3) {
				$task[$taskstatuss->taskstatus_name] =  DB::table('tasklist')
				->select('*')
				->where('brand_id','=',$request->brand_id)
				->where('taskstatus_id','=',$taskstatuss->taskstatus_id)
				->where('status_id','=',1)
				->orderBy('task_id','DESC')
				->paginate(30);
			}else if ($request->role_id == 6 || $request->role_id == 7) {
				$task[$taskstatuss->taskstatus_name] =  DB::table('tasklist')
				->select('*')
				->where('ordercreator','=',$request->user_id)
				->where('brand_id','=',$request->brand_id)
				->where('taskstatus_id','=',$taskstatuss->taskstatus_id)
				->where('status_id','=',1)
				->orderBy('task_id','DESC')
				->paginate(30);
			}else if ($request->role_id == 10) {
				$task[$taskstatuss->taskstatus_name] =  DB::table('tasklist')
				->select('*')
				->where('created_by','=',$request->user_id)
				->where('brand_id','=',$request->brand_id)
				->where('taskstatus_id','=',$taskstatuss->taskstatus_id)
				->where('status_id','=',1)
				->orderBy('task_id','DESC')
				->paginate(30);
			}else{
				if($taskstatuss->taskstatus_id == 1){
					$task[$taskstatuss->taskstatus_name] =  DB::table('memberstasklist')
					->select('*')
					->where('brand_id','=',$request->brand_id)
					->where('taskstatus_id','=',$taskstatuss->taskstatus_id)
					->where('taskuser_id','=',$request->user_id)
					->where('memberstatus_id','=',1)
					->where('status_id','=',1)
					->groupBy('task_id')
					->orderBy('task_id','DESC')
					->paginate(30);
				}else{
					$task[$taskstatuss->taskstatus_name] =  DB::table('memberstasklist')
					->select('*')
					->where('brand_id','=',$request->brand_id)
					->where('taskstatus_id','=',$taskstatuss->taskstatus_id)
					->where('taskuser_id','=',$request->user_id)
					->where('task_workby','=',$request->user_id)
					->where('memberstatus_id','=',1)
					->where('status_id','=',1)
					->groupBy('task_id')
					->orderBy('task_id','DESC')
					->paginate(30);
				}
			}
		}
		$taskpath = URL::to('/')."/public/task/";
		if(isset($task)){
			return response()->json(['data' => $task, 'taskpath' => $taskpath, 'message' => 'Task List'],200);
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
		$basicdetail = DB::table('tasklist')
		->select('*')
		->where('task_id','=',$request->task_id)
		->where('status_id','=',1)
		->first();
		$attachmentdetail = DB::table('taskattachment')
		->select('*')
		->where('task_id','=',$request->task_id)
		->where('attachmenttype','=',1)
		->where('status_id','=',1)
		->get();
		$forwardedattachmentdetail = DB::table('taskattachment')
		->select('*')
		->where('task_id','=',$request->task_id)
		->where('attachmenttype','=',3)
		->where('status_id','=',1)
		->get();
		$taskpath = URL::to('/')."/public/task/".$basicdetail->task_token."/";
		$forwardedtaskpath = URL::to('/')."/public/order/".$basicdetail->order_token."/";
		$memberpath = URL::to('/')."/public/user_picture/";
		if($basicdetail){
			return response()->json(['basicdetail' => $basicdetail, 'attachmentdetail' => $attachmentdetail, 'forwardedattachmentdetail' => $forwardedattachmentdetail, 'taskpath' => $taskpath, 'forwardedtaskpath' => $forwardedtaskpath, 'memberpath' => $memberpath,'message' => 'Task Detail'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function taskmemberdetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Task Id Required", 400);
		}
		$taskmember = DB::table('taskmember')
		->select('user_id')
		->where('task_id','=',$request->task_id)
		->where('status_id','=',1)
		->get();
		$sorttaskmember = array();
		foreach ($taskmember as $taskmembers) {
			$sorttaskmember[] = $taskmembers->user_id;
		}
		$memberdetail = DB::table('taskmemberdetail')
		->select('*')
		->where('task_id','=',$request->task_id)
		->where('status_id','=',1)
		->get();
		$taskmanager = DB::table('task')
		->select('task_manager')
		->where('task_id','=',$request->task_id)
		->where('status_id','=',1)
		->first();
		$index=0;
		$sorttaskmemberdetails = array();
		foreach ($memberdetail as $memberdetails) {
			if ($taskmanager->task_manager == $memberdetails->user_id) {
				$memberdetail[$index]->ismanager = "Manager";
			}else{
				$memberdetail[$index]->ismanager = "";
			}
			$sorttaskmemberdetails[$index] = $memberdetails;
			$index++;
		}
		$memberpath = URL::to('/')."/public/user_picture/";
		if($memberdetail){
			return response()->json(['membersid' => $sorttaskmember, 'memberdetail' => $sorttaskmemberdetails, 'memberpath' => $memberpath,'message' => 'Task Detail'],200);
		}else{
			return response()->json(['membersid' => $emptyarray, 'memberdetail' => $emptyarray,'message' => 'Task Detail'],200);
		}
	}
	public function taskcommentdetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Task Id Required", 400);
		}
		$commentdetail = DB::table('taskcommentdetails')
		->select('*')
		->where('task_id','=',$request->task_id)
		->where('status_id','=',1)
		->orderBy('taskcomment_id','DESC')
		->get();
		$taskcommentdetails = array();
		$dindex = 0;
		if (isset($commentdetail)) {
			foreach ($commentdetail as $commentdetails) {
				$tagdetails = DB::table('taguserdetail')
				->select('user_name')
				->where('taskcomment_id','=',$commentdetails->taskcomment_id)
				->where('status_id','=',1)
				->get();
				if (isset($tagdetails)) {
					$index = 0;
					foreach ($tagdetails as $tagdetailss) {
						$commentdetails->taguser_name[$index] = $tagdetailss->user_name;
						$index++;
					}
				}
				$taskcommentdetails[$dindex] = $commentdetails;
				$dindex++;
			}
		}
		$taskcommentdetails = $this->paginate($taskcommentdetails);
		$path = URL::to('/')."/public/user_picture/";
		if($taskcommentdetails){
			return response()->json(['taskcommentdetails' => $taskcommentdetails, 'path' => $path,'message' => 'Task Comment Detail'],200);
		}else{
			return response()->json(['taskcommentdetails' => $emptyarray,'message' => 'Task Comment Detail'],200);
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
			$member_id = $request->user_id;
		}else if (isset($request->taskmember_id)) {
			$getmemberid = DB::table('taskmember')
			->select('taskmember_id')
			->where('task_id','=',$request->task_id)
			->where('user_id','=',$request->taskmember_id)
			->where('status_id','=',1)
			->get();
			$sortmember = array();
			foreach ($getmemberid as $getmemberids) {
				$sortmember[] = $getmemberids->taskmember_id;
			}
			$delete  = DB::table('taskmember')
			->whereIn('taskmember_id',$sortmember)
			->update([
			'status_id' 	=> 2,
			'updated_by'	=> $request->user_id,
			'updared_at'	=> date('Y-m-d h:i:s'),
			]);
			$member_id = $request->taskmember_id;
		}
		if($delete){
			return response()->json(['member_id' => $member_id,'message' => 'Successfully Removed From Task'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function orderwisetasklist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'order_token'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Order Id Required", 400);
		}
		$orderid = DB::table('order')
		->select('order_id')
		->where('order_token','=',$request->order_token)
		->where('status_id','=',1)
		->get();
		$sortoredrid = array();
        foreach($orderid as $orderids){
            $sortoredrid[] = $orderids->order_id;
		}
		$tasklist = DB::table('task')
		->select('task_id','task_title','task_description','task_token','taskstatus_id')
		->whereIn('order_id',$sortoredrid)
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
	      'member_id' 			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$checkmember = DB::table('taskmember')
		->select('user_id')
		->where('task_id','=',$request->task_id)
		->where('user_id','=',$request->member_id)
		->where('status_id','=',1)
		->count();
		if ($checkmember > 0) {
			return response()->json(['message' => 'Member Already Exist'],200);		
		}else{
			$member = array(
			'task_id'		=> $request->task_id,
			'user_id'		=> $request->member_id,
			'status_id' 	=> 1,
			'created_by'	=> $request->user_id,
			'created_at'	=> date('Y-m-d h:i:s'),
			);
			$save = DB::table('taskmember')->insert($member);
		}
		if($save){
			return response()->json(['member_id' => $request->member_id,'message' => 'Member Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function sendcommenttotask(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'taskcomment_comment'	=> 'required',
	      'task_id' 			=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$task_token = DB::table('task')
		->select('task_token')
		->where('task_id','=',$request->task_id)
		->first();
		$send = array(
		'taskcomment_comment'	=> $request->taskcomment_comment,
		'task_id'				=> $request->task_id,
		'task_token'			=> $task_token->task_token,
		'status_id' 			=> 1,
		'taskcomment_date'		=> date('Y-m-d'),
		'created_by'			=> $request->user_id,
		'created_at'			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('taskcomment')->insert($send);
		$taskcomment_id = DB::getPdo()->lastInsertId();
		if (isset($request->taguser)) {
			foreach ($request->taguser as $tagusers) {
				$checktag = DB::table('taguser')
				->select('taguser_id')
				->where('taskcomment_id','=',$taskcomment_id)
				->where('taguser_userid','=',$tagusers['user_id'])
				->where('status_id','=',1)
				->count();
				if ($checktag > 0) {
					return response()->json(['message' => 'Already Taged'],200);		
				}else{
					$tag = array(
					'taguser_userid'	=> $tagusers['user_id'],
					'taskcomment_id'	=> $taskcomment_id,
					'status_id' 		=> 1,
					'created_by'		=> $request->user_id,
					'created_at'		=> date('Y-m-d h:i:s'),
					);
					DB::table('taguser')->insert($tag);
				}
			}
		}
		if($save){
			return response()->json(['message' => 'Comment Posted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function sendreply(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'taskreply_reply'	=> 'required',
	      'taskcomment_id' 	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$send = array(
		'taskreply_reply'	=> $request->taskreply_reply,
		'taskreply_date'	=> date('Y-m-d'),
		'taskcomment_id'	=> $request->taskcomment_id,
		'status_id' 		=> 1,
		'created_by'		=> $request->user_id,
		'created_at'		=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('taskreply')->insert($send);
		$taskreply_id = DB::getPdo()->lastInsertId();
		$replydetail = DB::table('taskreplydetails')
		->select('*')
		->where('taskreply_id','=',$taskreply_id)
		->where('status_id','=',1)
		->first();
		if($replydetail){
			return response()->json(['replydetail' => $replydetail,'message' => 'Reply Posted Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function commentreplydetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'taskcomment_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Task Id Required", 400);
		}
		$replydetail = DB::table('taskreplydetails')
		->select('*')
		->where('taskcomment_id','=',$request->taskcomment_id)
		->where('status_id','=',1)
		->orderBy('taskreply_id','DESC')
		->get();
		$path = URL::to('/')."/public/user_picture/";
		if($replydetail){
			return response()->json(['replydetail' => $replydetail, 'path' => $path,'message' => 'Comment Reply Detail'],200);
		}else{
			return response()->json(['replydetail' => $emptyarray,'message' => 'Comment Reply Detail'],200);
		}
	}
	public function movetask(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_id'			=> 'required',
	      'taskstatus_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Task Id Required", 400);
		}
		if($request->taskstatus_id == 2){
			$move  = DB::table('task')
			->where('task_id','=',$request->task_id )
			->update([
			'task_workby'	=> $request->user_id,
			'updated_by'	=> $request->user_id,
			'updated_at'	=> date('Y-m-d h:i:s'),
			]);
		}
		$move  = DB::table('task')
		->where('task_id','=',$request->task_id )
		->update([
		'taskstatus_id'	=> $request->taskstatus_id,
		'updated_by'	=> $request->user_id,
		'updated_at'	=> date('Y-m-d h:i:s'),
		]);
		if($request->taskstatus_id == 3){
			$taskcount = DB::table('task')
			->select('order_id')
			->where('order_id','=',$request->order_id)
			->where('taskstatus_id','=',2)
			->where('status_id','=',1)
			->count();
			if($taskcount == 0){
				DB::table('order')
				->where('order_id','=',$request->order_id)
				->update([
					'orderstatus_id' 	=> 5,
				]);
			}
		}
		if($move){
			return response()->json(['message' => 'Task Move Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function submitwork(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_id' 	=> 'required',
	      'task_token' 	=> 'required',
		  'attachment' 	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		if (isset($request->attachment)) {
			// dd($attachment);
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
		            $filename = $attachments->move(public_path('taskwork/'.$foldername),$filename);
		            $filename = $attachments->getClientOriginalName();
				  	$saveattachment = array(
					'taskattachment_name'	=> $filename,
					'task_id'				=> $request->task_id,
					'task_token'			=> $request->task_token,
					'attachmenttype'		=> 2,
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
	}
	public function taskworkattachment(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'task_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Task Id Required", 400);
		}
		$workattachmentdetail = DB::table('taskattachmentdetails')
		->select('*')
		->where('task_id','=',$request->task_id)
		->where('attachmenttype','=',2)
		->where('status_id','=',1)
		->get();
		$taskworkpath = URL::to('/')."/public/taskwork/";
		if($workattachmentdetail){
			return response()->json(['workattachmentdetail' => $workattachmentdetail, 'taskworkpath' => $taskworkpath, 'message' => 'Task Work Attachments'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function paginate($items, $perPage = 10, $page = null, $options = []){
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return  new  LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
	public function downloadclientattachment(Request $request)
    {   
		$validate = Validator::make($request->all(), [ 
			'order_token'	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Task Token Required", 400);
		}
        $zip = new ZipArchive;
        $fileName = 'clientattachment.zip';
        if ($zip->open(public_path($fileName), ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE)
        {
            $files = File::files(public_path('order/'.$request->order_token));
            foreach ($files as $file) {
                $relativeNameInZipFile = basename($file);
                $zip->addFile($file, $relativeNameInZipFile);
            }
            $zip->close();
        }
    	return response()->download(public_path($fileName));
    }
	public function downloadworkattachment(Request $request)
    {   
		$validate = Validator::make($request->all(), [ 
			'task_token'	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json("Task Token Required", 400);
		}
        $zip = new ZipArchive;
        $fileName = 'workattachment.zip';
        if ($zip->open(public_path($fileName), ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE)
        {
            $files = File::files(public_path('taskwork/'.$request->task_token));
            foreach ($files as $file) {
                $relativeNameInZipFile = basename($file);
                $zip->addFile($file, $relativeNameInZipFile);
            }
            $zip->close();
        }
    	return response()->download(public_path($fileName));
    }
}