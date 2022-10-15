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

class dashboardController extends Controller
{
	public function admindashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'yearmonth'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getyearandmonth = explode('-', $request->yearmonth);
		$getfirstdate = $request->yearmonth."-01";
		$noofdays = date('t');
		$list=array();
		for($d=1; $d<=$noofdays; $d++)
		{
		    $time=mktime(12, 0, 0, $getyearandmonth[1], $d, $getyearandmonth[0]);          
		    if (date('m', $time)==$getyearandmonth[1])       
		        $list[]=date('Y-m-d', $time);
		}
		$grosssale = DB::table('orderpayment')
		->select('orderpayment_amount')
		->whereIn('orderpayment_date', $list)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$unpaidsale = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',2)
		->whereIn('orderpayment_date', $list)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$paidsale = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',3)
		->whereIn('orderpayment_date', $list)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$cancel = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',4)
		->whereIn('orderpayment_date', $list)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$refund = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',5)
		->whereIn('orderpayment_date', $list)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$chargeback = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',6)
		->whereIn('orderpayment_date', $list)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$recovery = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',7)
		->whereIn('orderpayment_date', $list)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$ppcassigned = DB::table('assignppc')
		->select('assignppc_amount')
		->where('assignppc_month','=',$request->yearmonth)
		->where('status_id','=',1)
		->sum('assignppc_amount');
		$ppcspend = DB::table('ppc')
		->select('ppc_amount')
		->whereIn('ppc_date', $list)
		->where('status_id','=',1)
		->sum('ppc_amount');
		$remainingppc = $ppcassigned-$ppcspend;
		$gettotaltarget = DB::table('user')
		->select('user_target')
		->where('status_id','=',1)
		->sum('user_target');
		$gettotalachieve = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->whereIn('orderpayment_date',$list)
		->sum('orderpayment_amount');
		$remainingtarget = $gettotaltarget-$gettotalachieve;
		$topdata = array(
			'grosssale' 		=> $grosssale,
			'paidsale' 			=> $paidsale,
			'unpaidsale' 		=> $unpaidsale,
			'cancel' 			=> $cancel,
			'refund' 			=> $refund,
			'chargeback' 		=> $chargeback,
			'recovery' 			=> $recovery,
			'totaltarget' 		=> $gettotaltarget,
			'totalachieve'	 	=> $gettotalachieve,
			'remainingtarget' 	=> $remainingtarget,
			'ppcassigned' 		=> $ppcassigned,
			'ppcspend' 			=> $ppcspend,
			'remainingppc' 		=> $remainingppc,
		);
		$getupcommingpayments = DB::table('orderpaymentdetails')
		->select('order_title','orderpayment_title','orderpayment_amount','user_name','user_picture')
		->where('orderpaymentstatus_id','!=',3)
		->whereIn('orderpayment_duedate',$list)
		->where('status_id','=',1)
		->get();
		$graphdata = array();
		$yearindex = 0;
		for ($i=1; $i < 13 ; $i++) { 
			if ($i <= 9) {
				$totalincome = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('orderpayment_date','<=',$getyearandmonth[0].'-0'.$i.'01')
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$paidincome = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('orderpaymentstatus_id','=',3)
				->where('orderpayment_paiddate','<=',$getyearandmonth[0].'-0'.$i.'01')
				->where('status_id','=',1)
				->sum('orderpayment_amount');
			}else{
				$totalincome = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('orderpayment_date','<=',$getyearandmonth[0].'-0'.$i.'01')
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$paidincome = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('orderpaymentstatus_id','=',3)
				->where('orderpayment_paiddate','<=',$getyearandmonth[0].'-'.$i.'01')
				->where('status_id','=',1)
				->sum('orderpayment_amount');
			}
			$reminingincome = $totalincome-$paidincome;
			$graphdata[$yearindex]['total'] = $totalincome;
			$graphdata[$yearindex]['paid'] = $paidincome;
			$graphdata[$yearindex]['remaining'] = $reminingincome;
			$yearindex++;
		}
		$getuser = DB::table('user')
		->select('user_id','user_name','user_picture','user_target')
		->where('role_id','=',5)
		->where('status_id','=',1)
		->get();
		$topagent = array();
		$topindex=0;
		foreach ($getuser as $getusers) {
			$getachieve = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('orderpayment_date','like',$request->yearmonth.'%')
			->where('created_by','=',$getusers->user_id)
			->sum('orderpayment_amount');
			if ($getachieve != 0) {
				$getusers->achieve = $getachieve;
				$topagent[$topindex] = $getusers;	
			}
			$topindex++;
		}
		$getbrand = DB::table('brand')
		->select('brand_id','brand_name','brand_logo')
		->where('status_id','=',1)
		->get();
		$topbrand = array();
		$brandindex=0;
		foreach ($getbrand as $getbrands) {
			$getlead = DB::table('lead')
			->select('lead_id')
			->where('status_id','=',1)
			->where('brand_id','=',$getbrands->brand_id)
			->get('lead_id')->toArray();
			$sortlead = array();
			foreach ($getlead as $getleads) {
				$sortlead[] = $getleads->lead_id;
			}
			$getorder = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->whereIn('lead_id',$sortlead)
			->get();
			$sortorder = array();
			foreach ($getorder as $getorders) {
				$sortorder[] = $getorders->order_id;	
			}
			$getorderpaymenttotal = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpayment_date','like',$request->yearmonth.'%')
			->where('status_id','=',1)
			->whereIn('order_id',$sortorder)
			->sum('orderpayment_amount');
			$getorderpaymentpaid = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpayment_paiddate','like',$request->yearmonth.'%')
			->where('orderpaymentstatus_id','=',3)
			->where('status_id','=',1)
			->whereIn('order_id',$sortorder)
			->sum('orderpayment_amount');
			if ($getorderpaymenttotal != 0) {
				$getbrands->totalamount = $getorderpaymenttotal;
				$getbrands->paidamount = $getorderpaymentpaid;
				$topbrand[$brandindex] = $getbrands;	
			}
			$brandindex++;
		}
		$pendingtask = DB::table('tasklist')
		->select('task_id','task_title','task_deadlinedate','taskstatus_name','creator')
		->where('taskstatus_id','<=',3)
		->where('status_id','=',1)
		->where('task_deadlinedate','like',$request->yearmonth.'%')
		->get();
		$getcar = DB::connection('mysql2')->table('car')
		->select('car_id','car_name','car_rent')
		->where('status_id','=',2)
		->get();
		$carexpense = array();
		$carindex=0;
		foreach ($getcar as $getcars) {
			$getcarrentaddition = DB::connection('mysql2')->table('caraddition')
			->select('caraddition_rent')
			->where('caraddition_date','>=',$request->yearmonth)
			->where('status_id','=',1)
			->where('car_id','=',$getcars->car_id)
			->sum('caraddition_rent');
			$getcars->car_rent = $getcars->car_rent+$getcarrentaddition;
			$getcarassign = DB::connection('mysql2')->table('carassigndetails')
			->select('elsemployees_name')
			->where('carassign_month','=',$request->yearmonth)
			->where('status_id','=',1)
			->where('car_id','=',$getcars->car_id)
			->first();
			if (isset($getcarassign->elsemployees_name)) {
				$getcars->assignto = $getcarassign->elsemployees_name;	
			}else{
				$getcars->assignto = "Not Assigned";
			}
			$carexpense[$carindex] = $getcars;
			$carindex++;
		}
		$vanexpense = DB::connection('mysql2')->table('expense')
		->select('expense_title','expense_amount')
		->where('expense_yearandmonth','=',$request->yearmonth)
		->where('expensetype_id','=',4)
		->where('status_id','=',2)
		->get();
		$otherexpense = DB::connection('mysql2')->table('expense')
		->select('expense_title','expense_amount')
		->where('expense_yearandmonth','=',$request->yearmonth)
		->where('expensetype_id','!=',4)
		->where('status_id','=',2)
		->get();
		$userpicturepath = URL::to('/')."/public/user_picture/";
		$brandlogopath = URL::to('/')."/public/brand_logo/";
		return response()->json(['topdata' => $topdata, 'upcommingpayments' => $getupcommingpayments, 'graphdata' => $graphdata, 'topagent' => $topagent, 'topbrand' => $topbrand, 'pendingtask' => $pendingtask, 'carexpense' => $carexpense, 'vanexpense' => $vanexpense, 'otherexpense' => $otherexpense, 'userpicturepath' => $userpicturepath, 'brandlogopath' => $brandlogopath,'message' => 'Admin Dashboard'],200);
	}
	public function adminbranddetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'yearmonth'	=> 'required',
	      'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$branddetails = DB::table('brand')
		->select('*')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->first();
		$getyearandmonth = explode('-', $request->yearmonth);
		$getfirstdate = $request->yearmonth."-01";
		$noofdays = date('t');
		$list=array();
		for($d=1; $d<=$noofdays; $d++)
		{
		    $time=mktime(12, 0, 0, $getyearandmonth[1], $d, $getyearandmonth[0]);          
		    if (date('m', $time)==$getyearandmonth[1])       
		        $list[]=date('Y-m-d', $time);
		}
		$getbranduserid = DB::table('userbarnd')
		->select('user_id')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->get();
		$sortuserbrand = array();
		foreach ($getbranduserid as $getbranduserids) {
			$sortuserbrand[] = $getbranduserids->user_id;
		}
		$ppcassignindollar = DB::table('assignppc')
		->select('assignppc_amount')
		->where('assignppc_month','=',$request->yearmonth)
		->whereIn('user_id',$sortuserbrand)
		->where('status_id','=',1)
		->sum('assignppc_amount');
		$ppcspendindollar = DB::table('ppc')
		->select('ppc_amount')
		->whereIn('ppc_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->sum('ppc_amount');
		$remainingppcindollar = $ppcassignindollar-$ppcspendindollar;
		$totalincomeindollar = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','!=',3)
		->whereIn('orderpayment_date', $list)
		->whereIn('created_by',$sortuserbrand)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$paidincomeindollar = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',3)
		->whereIn('orderpayment_paiddate', $list)
		->whereIn('created_by',$sortuserbrand)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$remaininngincomeindollar = $totalincomeindollar-$paidincomeindollar;
		$stats = array(
			'ppcassignindollar' 		=> $ppcassignindollar,
			'ppcspendindollar' 			=> $ppcspendindollar,
			'remainingppcindollar' 		=> $remainingppcindollar,
			'totalincomeindollar' 		=> $totalincomeindollar,
			'paidincomeindollar' 		=> $paidincomeindollar,
			'remaininngincomeindollar' 	=> $remaininngincomeindollar,
		);
		$daywisepaidincome = array();
		$index = 0;
		foreach ($list as $lists) {
			$totalincomeindollar = DB::table('orderpayment')
			->select('orderpayment_amount')
			->whereIn('orderpayment_date', $list)
			// ->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$paidincomeindollar = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',3)
			->whereIn('orderpayment_paiddate', $list)
			// ->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$remainingincomeindollar = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','!=',3)
			->whereIn('orderpayment_date', $list)
			// ->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$daywisepaidincome[$index]['total'] = $totalincomeindollar;
			$daywisepaidincome[$index]['paid'] = $paidincomeindollar;
			$daywisepaidincome[$index]['remaining'] = $remainingincomeindollar;
			$index++;
		}
		$graphdata = array();
		$yearindex = 0;
		$getuser = DB::table('user')
		->select('user_id','user_name','user_picture','user_target')
		->whereIn('user_id',$sortuserbrand)
		->where('role_id','=',5)
		->where('status_id','=',1)
		->get();
		$saleagent = array();
		$saleindex=0;
		foreach ($getuser as $getusers) {
			$getachieve = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('orderpayment_date','like',$request->yearmonth.'%')
			->where('created_by','=',$getusers->user_id)
			->sum('orderpayment_amount');
			$getusers->achieve = $getachieve;
			$getpaid = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',3)
			->where('status_id','=',1)
			->where('orderpayment_paiddate','like',$request->yearmonth.'%')
			->where('created_by','=',$getusers->user_id)
			->sum('orderpayment_amount');
			$getusers->paid = $getpaid;
			$getcancel = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',4)
			->where('status_id','=',1)
			->where('orderpayment_date','like',$request->yearmonth.'%')
			->where('created_by','=',$getusers->user_id)
			->sum('orderpayment_amount');
			$getusers->cancel = $getcancel;
			$saleagent[$saleindex] = $getusers;	
			$saleindex++;
		}
		$payments = DB::table('orderpaymentdetails')
		->select('order_title','orderpayment_title','orderpayment_amount','user_name','user_picture')
		->where('orderpaymentstatus_id','!=',3)
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserbrand)
		->whereIn('orderpayment_duedate',$list)
		->get();
		$userpicturepath = URL::to('/')."/public/user_picture/";
		$brandlogopath = URL::to('/')."/public/brand_logo/";
		return response()->json(['branddetails' => $branddetails,'stats' => $stats, 'daywisepaidincome' => $daywisepaidincome, 'saleagent' => $saleagent, 'payments' => $payments, 'userpicturepath' => $userpicturepath, 'brandlogopath' => $brandlogopath,'message' => 'Admin Dashboard'],200);
	}
	public function portaladmindashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'yearmonth'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$getyearandmonth = explode('-', $request->yearmonth);
		$getfirstdate = $request->yearmonth."-01";
		$getsalary = DB::connection('mysql2')->table('payrollexpense')
		->where('elsemployees_dofjoining','<',$getfirstdate)
		->where('elsemployees_status','=',2)
		->select('Salary')
		->sum('Salary');
		$getincrement = DB::connection('mysql2')->table('increment')
        ->where('increment_year','=',$getyearandmonth[0])
        ->where('increment_month','=',$getyearandmonth[1])
        ->where('status_id','=',2)
        ->select('increment_amount')
        ->sum('increment_amount');
        $totalsalary = $getsalary+$getincrement;
		$topdata = array(
			'totalsalary' 				=> $totalsalary,
		);
		$getcar = DB::connection('mysql2')->table('car')
		->select('car_id','car_name','car_rent')
		->where('status_id','=',2)
		->get();
		$carexpense = array();
		$carindex=0;
		foreach ($getcar as $getcars) {
			$getcarrentaddition = DB::connection('mysql2')->table('caraddition')
			->select('caraddition_rent')
			->where('caraddition_date','>=',$request->yearmonth)
			->where('status_id','=',1)
			->where('car_id','=',$getcars->car_id)
			->sum('caraddition_rent');
			$getcars->car_rent = $getcars->car_rent+$getcarrentaddition;
			$getcarassign = DB::connection('mysql2')->table('carassigndetails')
			->select('elsemployees_name')
			->where('carassign_month','=',$request->yearmonth)
			->where('status_id','=',1)
			->where('car_id','=',$getcars->car_id)
			->first();
			if (isset($getcarassign->elsemployees_name)) {
				$getcars->assignto = $getcarassign->elsemployees_name;	
			}else{
				$getcars->assignto = "Not Assigned";
			}
			$carexpense[$carindex] = $getcars;
			$carindex++;
		}
		$vanexpense = DB::connection('mysql2')->table('expense')
		->select('expense_title','expense_amount')
		->where('expense_yearandmonth','=',$request->yearmonth)
		->where('expensetype_id','=',4)
		->where('status_id','=',2)
		->get();
		$otherexpense = DB::connection('mysql2')->table('expense')
		->select('expense_title','expense_amount')
		->where('expense_yearandmonth','=',$request->yearmonth)
		->where('expensetype_id','!=',4)
		->where('status_id','=',2)
		->get();
		$userpicturepath = URL::to('/')."/public/user_picture/";
		$brandlogopath = URL::to('/')."/public/brand_logo/";
		return response()->json(['topdata' => $topdata, 'carexpense' => $carexpense, 'vanexpense' => $vanexpense, 'otherexpense' => $otherexpense,'message' => 'Admin Dashboard'],200);
	}
}