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

class expenseController extends Controller
{
	public $emptyarray = array();
	public function addexpensetype(Request $request){
		$validate = Validator::make($request->all(), [
			'expensetype_name' 		=> 'required',
			'expensetype_proposed' 	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds = array(
			'expensetype_name'		=> $request->expensetype_name,
			'expensetype_proposed' 	=> $request->expensetype_proposed,
			'status_id'	 			=> 1,
		);
		$save = DB::table('expensetype')->insert($adds);
		if($save){
			return response()->json(['message' => 'Expense Type Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function updateexpensetype(Request $request){
		$validate = Validator::make($request->all(), [
			'expensetype_id' 		=> 'required',
			'expensetype_name' 		=> 'required',
			'expensetype_proposed' 	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$adds = array(
			'expensetype_name'		=> $request->expensetype_name,
			'expensetype_proposed' 	=> $request->expensetype_proposed,
		);
		$save = DB::table('expensetype')
		->where('expensetype_id','=',$request->expensetype_id)
		->update($adds);
		if($save){
			return response()->json(['message' => 'Expense Type Updated Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function expensetype(Request $request){
		$data = DB::table('expensetype')
		->select('*')
		->where('status_id','=',1)
		->get();		
		if($data){
			return response()->json(['data' => $data,'message' => 'Expense Type List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Expense Type List'],200);
		}
	}
	public function addexpenseactual(Request $request){
		$validate = Validator::make($request->all(), [
			'expenseactual_amount' 	=> 'required',
			'expenseactual_month' 	=> 'required',
			'expensetype_id' 		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$expensemonth = explode('-', $request->expenseactual_month);
		if ( $expensemonth[ 1 ] <= 9 ) {
            $setexpensemonth = $expensemonth[ 0 ].'-0'.$expensemonth[ 1 ];
        } else {
            $setexpensemonth = $expensemonth[ 0 ].'-'.$expensemonth[ 1 ];
        }
		$adds = array(
			'expenseactual_amount'		=> $request->expenseactual_amount,
			'expenseactual_month'		=> $setexpensemonth,
			'expensetype_id' 			=> $request->expensetype_id,
			'status_id'	 				=> 1,
			'created_by'		 		=> $request->user_id,
			'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('expenseactual')->insert($adds);
		if($save){
			return response()->json(['message' => 'Expense Actual Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function addexpense(Request $request){
		$validate = Validator::make($request->all(), [
			'expense_for' 				=> 'required',
			'expense_description' 		=> 'required',
		    'expense_modeofpayment' 	=> 'required',
		    'expense_amount' 			=> 'required',
		    'expense_paidby' 			=> 'required',
		  	'expense_date' 				=> 'required',
	    	'expensetype_id'  				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$expensemonth = explode('-', $request->expense_date);
		$expense_month = $expensemonth[0].'-'.$expensemonth[1];
		$adds = array(
			'expense_for'				=> $request->expense_for,
			'expense_description'		=> $request->expense_description,
			'expense_modeofpayment' 	=> $request->expense_modeofpayment,
			'expense_amount' 			=> $request->expense_amount,
			'expense_paidby' 			=> $request->expense_paidby,
			'expense_date'				=> $request->expense_date,
			'expense_month'				=> $expense_month,
			'expensetype_id' 			=> $request->expensetype_id,
			'status_id'	 				=> 1,
			'created_by'		 		=> $request->user_id,
			'created_at'	 			=> date('Y-m-d h:i:s'),
		);
		$save = DB::table('expense')->insert($adds);
		if($save){
			return response()->json(['message' => 'Expense Added Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function expensereport(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'expense_month'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode( '-', $request->expense_month );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
		$data = DB::table('expensetype')
		->select('*')
		->where('status_id','=',1)
		->get();
		$owner = DB::table('owners')
		->select('owners_name')
		->where('status_id','=',1)
		->get();
		$sortdata = array();
		$index = 0;
		foreach($data as $datas){
			$actual = DB::table('expenseactual')
			->select('expenseactual_amount')
			->where('expenseactual_month','=',$setyearmonth)
			->where('expensetype_id','=',$datas->expensetype_id)
			->sum('expenseactual_amount');
			$paidarray = array();
			$pindex = 0;
			foreach($owner as $owners){
				$a;
				$paid = DB::table('expense')
				->select('expense_amount')
				->where('expense_month','=',$setyearmonth)
				->where('expensetype_id','=',$datas->expensetype_id)
				->where('expense_paidby','=',$owners->owners_name)
				->sum('expense_amount');
				if($pindex == 0){
					$datas->Salman = $paid;
				}elseif($pindex == 1){
					$datas->Nadir = $paid;
				}elseif($pindex == 2){
					$datas->Sameel = $paid;
				}else{
					$datas->Aun = $paid;
				}
				$pindex++;
			}
			$sumpaid = DB::table('expense')
			->select('expense_amount')
			->where('expense_month','=',$setyearmonth)
			->where('expensetype_id','=',$datas->expensetype_id)
			->sum('expense_amount');
			$sumremaining = $actual-$sumpaid;
			$datas->sumpaid = $sumpaid;
			$datas->sumremaining = $sumremaining;
			$datas->actual = $actual;
			$detail = DB::table('expensedetails')
			->select('*')
			->where('expense_month','=',$setyearmonth)
			->where('expensetype_id','=',$datas->expensetype_id)
			->get();
			$datas->detail = $detail;
			$sortdata[$index] = $datas;
			$index++;
		}
		if($data){
			return response()->json(['data' => $sortdata, 'message' => 'Expense Report'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Expense Report'],200);
		}
	}
}