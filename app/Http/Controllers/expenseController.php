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
	public function addexpense(Request $request){
		$validate = Validator::make($request->all(), [
	    	'expense_title'  			=> 'required',
		    'expense_description' 		=> 'required',
		    'expense_modeofpayment' 	=> 'required',
		    'expense_amount' 			=> 'required',
		    'expense_paidby' 			=> 'required',
		    'expense_date' 				=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$expensemonth = explode('-', $request->expense_date);
		$expense_month = $expensemonth[0].'-'.$expensemonth[1];
		$adds = array(
		'expense_title' 			=> $request->expense_title,
		'expense_description'		=> $request->expense_description,
		'expense_modeofpayment' 	=> $request->expense_modeofpayment,
		'expense_amount' 			=> $request->expense_amount,
		'expense_paidby' 			=> $request->expense_paidby,
		'expense_date'				=> $request->expense_date,
		'expense_month'				=> $expense_month,
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
	public function expenselist(Request $request){
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
		$data = DB::table('expense')
		->select('*')
		->where('expense_month','=',$setyearmonth)
		->where('status_id','=',1)
		->get();		
		if($data){
			return response()->json(['data' => $data,'message' => 'Expense List'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Expense List'],200);
		}
	}
}