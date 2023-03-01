<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use stdClass;
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
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$getyearandmonth = explode('-', $setyearmonth);
		$getfirstdate = $setyearmonth."-01";
		if($yearmonth[1] == "1" || $yearmonth[1] == "3" || $yearmonth[1] == "5" || $yearmonth[1] == "7" || $yearmonth[1] == "8" || $yearmonth[1] == "10" || $yearmonth[1] == "12"){
			$noofdays = 31;
		}elseif($yearmonth[1] == "2"){
			$noofdays = 28;
		}else{
			$noofdays = 30;
		}
		$list=array();
		for($d=1; $d<=$noofdays; $d++)
		{
		    $time=mktime(12, 0, 0, $getyearandmonth[1], $d, $getyearandmonth[0]);          
		    if (date('m', $time)==$getyearandmonth[1])       
		        $list[]=date('Y-m-d', $time);
		}
		$totalbrand = DB::table('brand')
		->select('brand_id')
		->where('status_id','=',1)
		->get();
		$brands = array();
		foreach($totalbrand as $totalbrands){
			$brands[] =  $totalbrands->brand_id;
		}
		$graphdatatotal = array();
		$index = 0;
		foreach ($list as $lists) {
			$totalincomeindollar = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpayment_date','=', $lists)
			->whereIn('brand_id',$brands)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$graphdatatotal[$index] = $totalincomeindollar;
			$index++;
		}
		$graphdatapaid = array();
		$paidindex = 0;
		foreach ($list as $lists) {
			$paidincomeindollar = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',3)
			->where('orderpayment_date','=', $lists)
			->whereIn('brand_id',$brands)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$graphdatapaid[$paidindex] = $paidincomeindollar;
			$paidindex++;
		}
		$graphdataremaining = array();
		$remainingindex = 0;
		foreach ($list as $lists) {
			$totalincomeindollar = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpayment_date','=', $lists)
			->whereIn('brand_id',$brands)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$paidincomeindollar = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',3)
			->where('orderpayment_date','=', $lists)
			->whereIn('brand_id',$brands)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$remainingincomeindollar = $totalincomeindollar-$paidincomeindollar;
			$graphdataremaining[$remainingindex] = $remainingincomeindollar;
			$remainingindex++;
		}
		$grosssale = DB::table('orderpayment')
		->select('orderpayment_amount')
		->whereIn('orderpayment_date', $list)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$invoicesale = DB::table('orderpayment')
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
		$totalunpaid = $grosssale-$invoicesale-$paidsale-$cancel-$refund-$chargeback-$recovery;
		$ppcassigned = DB::table('assignppc')
		->select('assignppc_amount')
		->where('assignppc_month','=',$setyearmonth)
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
		$previousrecover = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',7)
		->where('orderpayment_date','<',$getfirstdate)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$previouscancel = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',4)
		->where('orderpayment_date','<',$getfirstdate)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$previouspaid = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('orderpaymentstatus_id','=',3)
		->where('orderpayment_date','<',$getfirstdate)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$previousunpaid = DB::table('orderpayment')
		->select('orderpayment_amount')
		->whereNotIn('orderpaymentstatus_id',[3,4,7])
		->where('orderpayment_date','<',$getfirstdate)
		->where('status_id','=',1)
		->sum('orderpayment_amount');
		$remainingtarget = $gettotaltarget-$gettotalachieve;
		$topdata = array(
			'grosssale' 		=> $grosssale,
			'paidsale' 			=> $paidsale,
			'invoicesale' 		=> $invoicesale,
			'cancel' 			=> $cancel,
			'refund' 			=> $refund,
			'chargeback' 		=> $chargeback,
			'recovery' 			=> $recovery,
			'totalunpaid' 		=> $totalunpaid,
			'totaltarget' 		=> $gettotaltarget,
			'totalachieve'	 	=> $gettotalachieve,
			'remainingtarget' 	=> $remainingtarget,
			'ppcassigned' 		=> $ppcassigned,
			'ppcspend' 			=> $ppcspend,
			'remainingppc' 		=> $remainingppc,
			'previousrecover' 	=> $previousrecover,
			'previouscancel' 	=> $previouscancel,
			'previouspaid' 		=> $previouspaid,
			'previousunpaid' 	=> $remainingppc,
		);
		$getupcommingpayments = DB::table('orderpaymentdetails')
		->select('order_title','orderpayment_title','orderpayment_amount','user_name','user_picture')
		->where('orderpaymentstatus_id','!=',3)
		->whereIn('orderpayment_duedate',$list)
		->where('status_id','=',1)
		->get();
		$getuser = DB::table('user')
		->select('user_id','user_name','user_picture','user_target')
		->where('role_id','=',7)
		->where('status_id','=',1)
		->get();
		$pendingtask = DB::table('tasklist')
		->select('task_id','task_title','task_deadlinedate','taskstatus_name','creator')
		// ->where('taskstatus_id','>',2)
		->where('status_id','=',1)
		->where('task_date','=',date('Y-m-d'))
		->get();
		$userpicturepath = URL::to('/')."/public/user_picture/";
		$brandlogopath = URL::to('/')."/public/brand_logo/";
		return response()->json(['topdata' => $topdata,'test' => $list, 'upcommingpayments' => $getupcommingpayments, 'pendingtask' => $pendingtask, 'graphdatatotal' => $graphdatatotal, 'graphdatapaid' => $graphdatapaid, 'graphdataremaining' => $graphdataremaining, 'userpicturepath' => $userpicturepath, 'brandlogopath' => $brandlogopath,'message' => 'Admin Dashboard'],200);
	}
	public function adminbranddetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'yearmonth'	=> 'required',
	      'brand_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$branddetails = DB::table('brand')
		->select('*')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->first();
		if($branddetails->brandtype_id == 2){
			$data = $this->patchbranddetails($request->yearmonth, $request->brand_id);
			return response()->json($data);
		}else{
			$branddetails->brand_currency = $branddetails->brand_currency == 1 ? "$" : " £";
			$getyearandmonth = explode('-', $setyearmonth);
			$getfirstdate = $setyearmonth."-01";
			if($yearmonth[1] == "1" || $yearmonth[1] == "3" || $yearmonth[1] == "5" || $yearmonth[1] == "7" || $yearmonth[1] == "8" || $yearmonth[1] == "10" || $yearmonth[1] == "12"){
				$noofdays = 31;
			}elseif($yearmonth[1] == "2"){
				$noofdays = 28;
			}else{
				$noofdays = 30;
			}
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
			->where('assignppc_month','=',$setyearmonth)
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
			$gross = DB::table('orderpayment')
			->select('orderpayment_amount')
			->whereIn('orderpayment_date', $list)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$forwarded = DB::table('orderpayment')
			->select('orderpayment_amount')
			->whereIn('orderpaymentstatus_id',[8,9])
			->whereIn('orderpayment_date', $list)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$invoice = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',3)
			->whereIn('orderpayment_date', $list)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$paid = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',3)
			->whereIn('orderpayment_date', $list)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$cancel = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',4)
			->whereIn('orderpayment_date', $list)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$refund = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',5)
			->whereIn('orderpayment_date', $list)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$chargeback = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',6)
			->whereIn('orderpayment_date', $list)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$recovery = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('orderpaymentstatus_id','=',7)
			->whereIn('orderpayment_date', $list)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$request->brand_id)
			->where('status_id','=',1)
			->sum('orderpayment_amount');
			$totalunpaid = $gross-$forwarded-$invoice-$paid-$cancel-$refund-$chargeback-$recovery;
			$stats = array(
				'ppcassignindollar' 	=> $ppcassignindollar,
				'ppcspendindollar' 		=> $ppcspendindollar,
				'remainingppcindollar' 	=> $remainingppcindollar,
				'gross' 				=> $gross,
				'forwarded' 			=> $forwarded,
				'invoice' 				=> $invoice,
				'paid' 					=> $paid,
				'cancel' 				=> $cancel,
				'refund' 				=> $refund,
				'chargeback' 			=> $chargeback,
				'recovery' 				=> $recovery,
				'totalunpaid'		 	=> $totalunpaid,
			);
			$graphdatatotal = array();
			$totalindex = 0;
			foreach ($list as $lists) {
				$total = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('orderpayment_date','=', $lists)
				->whereIn('created_by',$sortuserbrand)
				->where('brand_id','=',$request->brand_id)
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$graphdatatotal[$totalindex] = $total;
				$totalindex++;
			}
			$graphdatapaid = array();
			$paidindex = 0;
			foreach ($list as $lists) {
				$paid = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('orderpaymentstatus_id','=',3)
				->where('orderpayment_date','=', $lists)
				->whereIn('created_by',$sortuserbrand)
				->where('brand_id','=',$request->brand_id)
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$graphdatapaid[$paidindex] = $paid;
				$paidindex++;
			}
			$graphdataremaining = array();
			$remainingindex = 0;
			foreach ($list as $lists) {
				$remaining = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('orderpaymentstatus_id','!=',3)
				->where('orderpayment_date','=', $lists)
				->whereIn('created_by',$sortuserbrand)
				->where('brand_id','=',$request->brand_id)
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$graphdataremaining[$remainingindex] = $remaining;
				$remainingindex++;
			}
			$getuser = DB::table('user')
			->select('user_id','user_name','user_picture','user_target')
			->whereIn('user_id',$sortuserbrand)
			->whereIn('role_id',[6,7])
			->where('status_id','=',1)
			->get();
			$topagent = array();
			$topindex=0;
			$topthree=0;
			foreach ($getuser as $getusers) {
				$getachieve = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('orderpayment_date','like',$setyearmonth.'%')
				->where('created_by','=',$getusers->user_id)
				->sum('orderpayment_amount');
				if ($getachieve != 0 && $topthree <= 2) {
					$getusers->achieve = $getachieve;
					$topagent[$topindex] = $getusers;	
					$topthree++;
				}
				$topindex++;
			}
			$topagent = array_sort($topagent, 'achieve', SORT_DESC);
			$sorttopagent = array();
			$sortindex=0;
			foreach($topagent as $topagents){
				$sorttopagent[$sortindex] = $topagents;
				$sortindex++;
			}
			$agenttarget = array();
			$target=0;
			foreach ($getuser as $getusers) {
				$getachieve = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('brand_id','=',$request->brand_id)
				->where('orderpayment_date','like',$setyearmonth.'%')
				->where('created_by','=',$getusers->user_id)
				->sum('orderpayment_amount');
				$getpaid = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('brand_id','=',$request->brand_id)
				->where('orderpaymentstatus_id','=',3)
				->where('orderpayment_date','like',$setyearmonth.'%')
				->where('created_by','=',$getusers->user_id)
				->sum('orderpayment_amount');
				$getcancel = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('brand_id','=',$request->brand_id)
				->where('orderpaymentstatus_id','=',4)
				->where('orderpayment_date','like',$setyearmonth.'%')
				->where('created_by','=',$getusers->user_id)
				->sum('orderpayment_amount');
				$getusers->achieve = $getachieve;
				$getusers->paid = $getpaid;
				$getusers->cancel = $getcancel;
				$agenttarget[$target] = $getusers;	
				$target++;
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
			return response()->json(['branddetails' => $branddetails,'stats' => $stats, 'topagent' => $sorttopagent, 'agenttarget' => $agenttarget, 'payments' => $payments, 'graphdatatotal' => $graphdatatotal, 'graphdatapaid' => $graphdatapaid, 'graphdataremaining' => $graphdataremaining, 'userpicturepath' => $userpicturepath, 'brandlogopath' => $brandlogopath,'message' => 'Admin Dashboard'],200);
		}
	}
	public function portaladmindashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'yearmonth'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$getyearandmonth = explode('-', $setyearmonth);
		$getfirstdate = $setyearmonth."-01";
		$getsalary = DB::connection('mysql2')->table('payrollexpense')
		->where('elsemployees_dofjoining','<',$getfirstdate)
		->where('elsemployees_status','=',2)
		->select('Salary')
		->sum('Salary');
		$getincrement = DB::connection('mysql2')->table('increment')
        ->where('increment_year','<=',$getyearandmonth[0])
        ->where('increment_month','<=',$getyearandmonth[1])
        ->where('status_id','=',2)
        ->select('increment_amount')
        ->sum('increment_amount');
        $raferal = DB::connection('mysql2')->table('adjustments')
		->where('AdjMonth','=',$setyearmonth)
		->select('adjustment')
		->sum('adjustment');
		$incentive = DB::connection('mysql2')->table('adjustments')
		->where('AdjMonth','=',$setyearmonth)
		->select('incentiveamount')
		->sum('incentiveamount');
		$spiff = DB::connection('mysql2')->table('adjustments')
		->where('AdjMonth','=',$setyearmonth)
		->select('spiffamount')
		->sum('spiffamount');
		$other = DB::connection('mysql2')->table('adjustments')
		->where('AdjMonth','=',$setyearmonth)
		->select('otheramount')
		->sum('otheramount');
		$last = DB::connection('mysql2')->table('adjustments')
		->where('AdjMonth','=',$setyearmonth)
		->select('lastamount')
		->sum('lastamount');
		$caramount = DB::connection('mysql2')->table('car')
		->where('status_id','=',2)
		->select('car_rent')
		->sum('car_rent');
		$additioncaramount = DB::connection('mysql2')->table('caraddition')
		->where('caraddition_date','>=',$setyearmonth)
		->where('status_id','=',2)
		->select('caraddition_rent')
		->sum('caraddition_rent');
		$sumcarrent = $caramount+$additioncaramount;
        $grosssalary = $getsalary+$getincrement+$raferal+$incentive+$spiff+$other+$last+$sumcarrent;
        $getcorrection = DB::connection('mysql2')->table('attendancecorrection')
        ->where('attendancecorrection_affdate','like',$setyearmonth.'%')
        ->where('attendancecorrection_status','=',"Approved")
        ->where('status_id','=',2)
        ->select('attendancecorrection_amount')
        ->sum('attendancecorrection_amount');
		$salaryexpense = array(
			'grosssalary' 				=> $grosssalary,
			'getcorrection' 			=> $getcorrection,
			'netsalary' 				=> $grosssalary,
		);
		$getcar = DB::connection('mysql2')->table('car')
		->select('car_id','car_name','car_rent')
		// ->where('created_at','like',$setyearmonth.'%')
		->where('status_id','=',2)
		->get();
		$carexpense = array();
		$carindex=0;
		foreach ($getcar as $getcars) {
			$getcarrentaddition = DB::connection('mysql2')->table('caraddition')
			->select('caraddition_rent')
			->where('caraddition_date','>=',$setyearmonth)
			->where('status_id','=',1)
			->where('car_id','=',$getcars->car_id)
			->sum('caraddition_rent');
			$getcars->car_rent = $getcars->car_rent+$getcarrentaddition;
			$getcarassign = DB::connection('mysql2')->table('carassigndetails')
			->select('elsemployees_name')
			->where('carassign_month','=',$setyearmonth)
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
		$sumbasiccarrent = DB::connection('mysql2')->table('car')
		->select('car_rent')
		// ->where('created_at','like',$setyearmonth.'%')
		->where('status_id','=',2)
		->sum('car_rent');
		$sumcarrentaddition = DB::connection('mysql2')->table('caraddition')
		->select('caraddition_rent')
		->where('caraddition_date','>=',$setyearmonth)
		->where('status_id','=',1)
		->sum('caraddition_rent');
		$sumcarrent = $sumbasiccarrent+$sumcarrentaddition;
		$fixexpense = DB::connection('mysql2')->table('expense')
		->select('expense_title','expense_amount')
		->where('expense_yearandmonth','=',$setyearmonth)
		->where('expense_isrecuring','=',1)
		->where('expensetype_id','=',2)
		->where('status_id','=',2)
		->get();
		$vanexpense = DB::connection('mysql2')->table('expense')
		->select('expense_title','expense_amount')
		->where('expense_yearandmonth','=',$setyearmonth)
		->where('expense_isrecuring','=',0)
		->where('expensetype_id','=',4)
		->where('status_id','=',2)
		->get();
		$otherexpense = DB::connection('mysql2')->table('expense')
		->select('expense_title','expense_amount')
		->where('expense_yearandmonth','=',$setyearmonth)
		->where('expense_isrecuring','=',0)
		->where('expensetype_id','=',5)
		->where('status_id','=',2)
		->get();
		$sumfixexpense = DB::connection('mysql2')->table('expense')
		->select('expense_amount')
		->where('expense_yearandmonth','=',$setyearmonth)
		->where('expense_isrecuring','=',1)
		->where('expensetype_id','=',4)
		->where('status_id','=',2)
		->sum('expense_amount');
		$sumvanexpense = DB::connection('mysql2')->table('expense')
		->select('expense_amount')
		->where('expense_yearandmonth','=',$setyearmonth)
		->where('expense_isrecuring','=',0)
		->where('expensetype_id','=',4)
		->where('status_id','=',2)
		->sum('expense_amount');
		$sumotherexpense = DB::connection('mysql2')->table('expense')
		->select('expense_amount')
		->where('expense_yearandmonth','=',$setyearmonth)
		->where('expense_isrecuring','=',0)
		->where('expensetype_id','!=',4)
		->where('status_id','=',2)
		->sum('expense_amount');
		$grandtotal = $sumcarrent+$sumfixexpense+$sumvanexpense+$sumotherexpense+$grosssalary;
		$sumallexpense = array(
			'sumcarrent' 		=> $sumcarrent,
			'sumfixexpense' 	=> $sumfixexpense,
			'sumvanexpense' 	=> $sumvanexpense,
			'sumotherexpense' 	=> $sumotherexpense,
			'netsalary' 		=> $grosssalary,
			'grandtotal' 		=> $grandtotal,
		);
		$userpicturepath = URL::to('/')."/public/user_picture/";
		$brandlogopath = URL::to('/')."/public/brand_logo/";
		return response()->json(['salaryexpense' => $salaryexpense, 'carexpense' => $carexpense, 'vanexpense' => $vanexpense, 'fixexpense' => $fixexpense, 'otherexpense' => $otherexpense, 'sumallexpense' => $sumallexpense, 'message' => 'Admin Dashboard'],200);
	}
	public function upcomingpaymentdashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'yearmonth'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$graphdata = array();
		$yearindex = 0;
		for ($i=1; $i < 32 ; $i++) { 
			if ($i <= 9) {
				$getupcommingpayments = DB::table('orderpaymentdetails')
				->select('order_title','orderpayment_title','orderpayment_amount','user_name','user_picture')
				->where('orderpaymentstatus_id','=',2)
				->where('orderpayment_date','=',$setyearmonth.'-0'.$i)
				->where('status_id','=',1)
				->get();
				$sumpayments = DB::table('orderpaymentdetails')
				->select('orderpayment_amount')
				->where('orderpaymentstatus_id','=',2)
				->where('orderpayment_date','=',$setyearmonth.'-0'.$i)
				->where('status_id','=',1)
				->sum('orderpayment_amount');
			}else{
				$getupcommingpayments = DB::table('orderpaymentdetails')
				->select('order_title','orderpayment_title','orderpayment_amount','user_name','user_picture')
				->where('orderpaymentstatus_id','=',2)
				->where('orderpayment_date','=',$setyearmonth.'-'.$i)
				->where('status_id','=',1)
				->get();
				$sumpayments = DB::table('orderpaymentdetails')
				->select('orderpayment_amount')
				->where('orderpaymentstatus_id','=',2)
				->where('orderpayment_date','=',$setyearmonth.'-'.$i)
				->where('status_id','=',1)
				->sum('orderpayment_amount');
			}
			$graphdata[$yearindex]['date'] = $i;
			$graphdata[$yearindex]['payments'] = $getupcommingpayments;
			$graphdata[$yearindex]['sumpayments'] = $sumpayments;
			$yearindex++;
		}
		return response()->json($graphdata,200);
	}
	public function billingmerchantdashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
			'yearmonth'	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$merchatdetail = DB::table('billingmerchant')
		->select('*')
		->where('status_id','=',1)
		->groupBy('billingmerchant_email')
		->get();
		if(isset($merchatdetail)){
			$sortbillingmerchant = array();
			$merchantnetamount = array();
			// mehroz start
			$firstdatewithzero = $setyearmonth.'-01';
        	$firstdate = $setyearmonth.'-01';
			$stats = array();
			$index=0;
			foreach($merchatdetail as $merchatdetails){
				$merchantids = DB::table('billingmerchant')
				->select('billingmerchant_id')
				->where('billingmerchant_email','=',$merchatdetails->billingmerchant_email)
				->get();
				$sortmerchantids = array();
				foreach($merchantids as $merchantidss){
					$sortmerchantids[] = $merchantidss->billingmerchant_id;
				}
				$firstbalance = $merchatdetails->billingmerchant_openingbalance;
				$previouspaidbalance = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('orderpayment_date','<',$firstdatewithzero)
				->where('orderpaymentstatus_id','=',3)
				->whereIn('merchant_id',$sortmerchantids)
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$previousfeededuction =  $merchatdetails->billingmerchant_fee / 100 * $previouspaidbalance;
				$previoustotalwithdrawl = DB::table('withdrawal')
				->select('withdrawal_amount')
				->where('withdrawal_month','<',$request->yearmonth)
				->whereIn('billingmerchant_id',$sortmerchantids)
				->where('status_id','=',1)
				->sum('withdrawal_amount');
				$previousnetbalance = $previouspaidbalance+$firstbalance-$previoustotalwithdrawl-$previousfeededuction;
				$openingbalance = $previousnetbalance;
				$paidbalance = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('orderpayment_date','like',$setyearmonth.'%')
				->where('orderpaymentstatus_id','=',3)
				->whereIn('merchant_id',$sortmerchantids)
				->where('status_id','=',1)
				->sum('orderpayment_amount');
				$grosstotalbalance = $openingbalance+$paidbalance;
				$totalwithdrawl = DB::table('withdrawal')
				->select('withdrawal_amount')
				->where('withdrawaltype_id','=',$request->withdrawal_month)
				->whereIn('billingmerchant_id',$sortmerchantids)
				->where('status_id','=',1)
				->sum('withdrawal_amount');
				$feededuction =  $merchatdetails->billingmerchant_fee / 100 * $paidbalance;
				$netbalance = $grosstotalbalance-$totalwithdrawl-$feededuction;

				$merchatdetails->billingmerchant_openingbalance 	= $openingbalance;
				$merchatdetails->paidbalance 		= $paidbalance;
				$merchatdetails->feededuction 		= $feededuction;
				$merchatdetails->totalwithdrawl 	= $totalwithdrawl;
				$merchatdetails->grosstotalbalance  = $grosstotalbalance;
				$merchatdetails->netbalance  		= $netbalance;
				$merchantnetamount[$index] 			= $netbalance;
				$stats[$index] 		                = $merchatdetails;
				$index++;
			}
			// mehroz end
			$billingmerchanttitle = DB::table('billingmerchant')
			->select('billingmerchant_title')
			->where('status_id','=',1)
			->groupBy('billingmerchant_email')
			->get();
			$merchanttitle = array();
			foreach($billingmerchanttitle as $billingmerchanttitles){
				$merchanttitle[] =  $billingmerchanttitles->billingmerchant_title;
			}
			$logopath = URL::to('/')."/public/billingmerchantlogo/";
			return response()->json(['sortbillingmerchant' => $stats, 'merchanttitle' => $merchanttitle, 'merchantnetamount' => $merchantnetamount, 'logopath' => $logopath,  'message' => 'Billing Merchant Details'],200);
		}else{
			$emptyarray = array();
			$logopath = "";
			return response()->json(['sortbillingmerchant' => $emptyarray, 'logopath' => $logopath,  'message' => 'Billing Merchant Details'],200);
		}
	}
	public function workerdashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
			'yearmonth'	=> 'required',
			'brand_id'	=> 'required',
			'id'		=> 'required',
		]);
		if ($validate->fails()) {    
		    return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$getyearandmonth = explode('-', $setyearmonth);
		$getuser = DB::table('user')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$list=array();
		if($yearmonth[1] == "1" || $yearmonth[1] == "3" || $yearmonth[1] == "5" || $yearmonth[1] == "7" || $yearmonth[1] == "8" || $yearmonth[1] == "10" || $yearmonth[1] == "12"){
			$noofdays = 31;
		}elseif($yearmonth[1] == "2"){
			$noofdays = 28;
		}else{
			$noofdays = 30;
		}
		for($d=1; $d<=$noofdays; $d++)
		{
			$time=mktime(12, 0, 0, $getyearandmonth[1], $d, $getyearandmonth[0]);          
			if (date('m', $time)==$getyearandmonth[1])       
				$list[]=date('Y-m-d', $time);
		}
		$datewiseordercount = array();
		$index = 0;
		foreach ($list as $lists) {
		$totalorders = DB::table('task')
		->select('task_id')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->where('task_workby','=',$request->id)
		->where('task_date','=',$lists)
		->count('task_id');
		$completeorders = DB::table('task')
		->select('task_id')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->where('taskstatus_id','>=',3)
		->where('task_workby','=',$request->id)
		->where('task_date','=',$lists)
		->count('task_id');
		$datewiseordercount[$index]['totalorders'] = $totalorders;
		$datewiseordercount[$index]['completeorders'] = $completeorders;
		$datewiseordercount[$index]['orderdate'] = $lists;
		$index++;
		}
		$monthlytotalorders = DB::table('task')
		->select('task_id')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->where('task_workby','=',$request->id)
		->where('task_date','like',$setyearmonth.'%')
		->count('task_id');
		$monthlycompleteorders = DB::table('task')
		->select('task_id')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->where('taskstatus_id','>=',3)
		->where('task_workby','=',$request->id)
		->where('task_date','like',$setyearmonth.'%')
		->count('task_id');
		$monthlyremainingorders = $monthlytotalorders-$monthlycompleteorders;
		$ordercounts = array();
		$ordercounts['totalorder'] = $monthlytotalorders;
		$ordercounts['completeorder'] = $monthlycompleteorders;
		$ordercounts['pendingorder'] = $monthlyremainingorders;
		$userpicturepath = URL::to('/')."/public/user_picture/";
		$branddetail = DB::table('branddetail')
		->select('*')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->first();
		$logopath = URL::to('/')."/public/brand_logo/";
		if(isset($getuser)){
		    return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount, 'orderscount' => $ordercounts, 'userpicturepath' => $userpicturepath, 'branddetail' => $branddetail, 'logopath' => $logopath, 'message' => 'Worker Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function salesdashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
			'yearmonth'	=> 'required',
			'brand_id'	=> 'required',
			'id'		=> 'required',
		]);
		if ($validate->fails()) {    
		    return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$getyearandmonth = explode('-', $setyearmonth);
		$getuser = DB::table('user')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$list=array();
		if($yearmonth[1] == "1" || $yearmonth[1] == "3" || $yearmonth[1] == "5" || $yearmonth[1] == "7" || $yearmonth[1] == "8" || $yearmonth[1] == "10" || $yearmonth[1] == "12"){
			$noofdays = 31;
		}elseif($yearmonth[1] == "2"){
			$noofdays = 28;
		}else{
			$noofdays = 30;
		}
		$from = $setyearmonth.'-01';
		$to = $setyearmonth.'-'.$noofdays;
		$year = $getyearandmonth[0];
		$month = $getyearandmonth[1];
		for($d=1; $d<=$noofdays; $d++)
		{
			$time=mktime(12, 0, 0, $month, $d, $year);          
			if (date('m', $time)==$month)       
				$list[]=date('Y-m-d', $time);
		}
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$workingdays = countDays(2013, 1, array(0, 6));
		$datewiseordercount = array();
		$index = 0;
		foreach ($list as $lists) {
		$orderscount = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('order_date','=',$lists)
		->where('brand_id','=',$request->brand_id)
		->count('order_id');
		$orderamount = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('orderpayment_date','=',$lists)
		->where('orderpaymentstatus_id','!=',1)
		->where('brand_id','=',$request->brand_id)
		->sum('orderpayment_amount');
		$datewiseordercount[$index]['orderscount'] = $orderscount;
		$datewiseordercount[$index]['orderamount'] = $orderamount;
		$datewiseordercount[$index]['orderdate'] = $lists;
		$index++;
		}
		$target = array();
		$targetachieved = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('orderpaymentstatus_id','!=',1)
		->whereBetween('orderpayment_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->sum('orderpayment_amount');	
		$targetpaid = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('orderpaymentstatus_id','=',3)
		->whereBetween('orderpayment_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->sum('orderpayment_amount');	
		$recoverypaid = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('orderpaymentstatus_id','=',7)
		->whereBetween('orderpayment_recoverydate', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->sum('orderpayment_amount');
		$targetcancel = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('orderpaymentstatus_id','=',4)
		->whereBetween('orderpayment_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->sum('orderpayment_amount');
		$targetincrement = DB::table('usertarget')
		->select('usertarget_target')
		->where('user_id','=',$request->id)
		->where('usertarget_month','<=',$setyearmonth)
		->where('status_id','=',1)
		->sum('usertarget_target');
		$usertarget = $getuser->user_target+$targetincrement;
		$unpaidamount = $targetachieved-$targetpaid-$targetcancel-$recoverypaid;
		$counttotalorders = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('orderstatus_id','!=',1)
		->whereBetween('order_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->count('order_id');
		$countcompleteorders = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->whereNotIn('orderstatus_id',[5,8,9,10])
		->where('created_by','=',$request->id)
		->whereBetween('order_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->count('order_id');
		$countapproveorders = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->where('orderstatus_id','=',11)
		->where('created_by','=',$request->id)
		->whereBetween('order_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->count('order_id');
		$countcancel = DB::table('order')
		->select('order_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('orderstatus_id','=',6)
		->whereBetween('order_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->count('order_id');
		$countpendingorders = $countcompleteorders-$countapproveorders-$countcancel;
		$target['user_target'] = $usertarget;
		$target['achieved'] = $targetachieved;
		$target['paid'] = $targetpaid;
		$target['recovery'] = $recoverypaid;
		$target['unpaidamount'] = $unpaidamount;
		$target['remaining'] = $usertarget - $targetachieved;
		$target['perday'] = $usertarget / $workingdays;
		$target['cancel'] = $targetcancel;
		$target['counttotalorders'] = $counttotalorders;
		$target['countcompleteorders'] = $countcompleteorders;
		$target['countapproveorders'] = $countapproveorders;
		$target['countcancel'] = $countcancel;
		$userpicturepath = URL::to('/')."/public/user_picture/";
		$branddetail = DB::table('branddetail')
		->select('*')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->first();
		$logopath = URL::to('/')."/public/brand_logo/";
		if(isset($getuser)){
		    return response()->json(['userdata' => $getuser, 'target' => $target, 'daileordercount' => $datewiseordercount, 'userpicturepath' => $userpicturepath, 'branddetail' => $branddetail, 'logopath' => $logopath, 'message' => 'Sales Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function leadashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
			'yearmonth'	=> 'required',
			'brand_id'	=> 'required',
			'id'		=> 'required',
		]);
		if ($validate->fails()) {    
		    return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$getyearandmonth = explode('-', $setyearmonth);
		$getuser = DB::table('user')
		->select('*')
		->where('user_id','=',$request->id)
		->where('status_id','=',1)
		->first();
		$list=array();
		if($yearmonth[1] == "1" || $yearmonth[1] == "3" || $yearmonth[1] == "5" || $yearmonth[1] == "7" || $yearmonth[1] == "8" || $yearmonth[1] == "10" || $yearmonth[1] == "12"){
			$noofdays = 31;
		}elseif($yearmonth[1] == "2"){
			$noofdays = 28;
		}else{
			$noofdays = 30;
		}
		$from = $setyearmonth.'-01';
		$to = $setyearmonth.'-'.$noofdays;
		for($d=1; $d<=$noofdays; $d++)
		{
			$time=mktime(12, 0, 0, $getyearandmonth[1], $d, $getyearandmonth[0]);          
			if (date('m', $time)==$getyearandmonth[1])       
				$list[]=date('Y-m-d', $time);
		}
		function countDays($year, $month, $ignore) {
		    $count = 0;
		    $counter = mktime(0, 0, 0, $month, 1, $year);
		    while (date("n", $counter) == $month) {
		        if (in_array(date("w", $counter), $ignore) == false) {
		            $count++;
		        }
		        $counter = strtotime("+1 day", $counter);
		    }
		    return $count;
		}
		$datewiseordercount = array();
		$index = 0;
		foreach ($list as $lists) {
		$getdailysavelead = DB::table('freshlead')
		->select('freshlead_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('freshlead_date','=',$lists)
		->where('brand_id','=',$request->brand_id)
		->count('freshlead_id');
		$getdailytotal = DB::table('leadgenerate')
		->select('leadgenerate_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('leadgenerate_date','=',$lists)
		->where('brand_id','=',$request->brand_id)
		->count('lead_id');
		$getdailyclient = DB::table('leadgenerate')
		->select('leadgenerate_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('leadstatus_id','=',3)
		->where('leadgenerate_date','=',$lists)
		->where('brand_id','=',$request->brand_id)
		->count('lead_id');
		$getdailycancel = DB::table('leadgenerate')
		->select('leadgenerate_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('leadstatus_id','=',12)
		->where('leadgenerate_date','=',$lists)
		->where('brand_id','=',$request->brand_id)
		->count('lead_id');
		$datewiseordercount[$index]['savelead'] = $getdailysavelead;
		$datewiseordercount[$index]['total'] = $getdailytotal;
		$datewiseordercount[$index]['client'] = $getdailyclient;
		$datewiseordercount[$index]['cancel'] = $getdailycancel;
		$datewiseordercount[$index]['orderdate'] = $lists;
		$index++;
		}
		$getmonthlysavelead = DB::table('freshlead')
		->select('freshlead_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->whereBetween('freshlead_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->count('freshlead_id');
		$getmonthlytotal = DB::table('leadgenerate')
		->select('leadgenerate_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->whereBetween('leadgenerate_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->count('lead_id');
		$getmonthlyclient = DB::table('leadgenerate')
		->select('leadgenerate_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('leadstatus_id','=',3)
		->whereBetween('leadgenerate_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->count('lead_id');
		$getmonthlycancel = DB::table('leadgenerate')
		->select('leadgenerate_id')
		->where('status_id','=',1)
		->where('created_by','=',$request->id)
		->where('leadstatus_id','=',4)
		->whereBetween('leadgenerate_date', [$from, $to])
		->where('brand_id','=',$request->brand_id)
		->count('lead_id');
		$monthlyordercount = array();
		$monthlyordercount['savelead'] = $getmonthlysavelead;
		$monthlyordercount['total'] = $getmonthlytotal;
		$monthlyordercount['client'] = $getmonthlyclient;
		$monthlyordercount['cancel'] = $getmonthlycancel;
		$userpicturepath = URL::to('/')."/public/user_picture/";
		$branddetail = DB::table('branddetail')
		->select('*')
		->where('brand_id','=',$request->brand_id)
		->where('status_id','=',1)
		->first();
		$logopath = URL::to('/')."/public/brand_logo/";
		if(isset($getuser)){
			return response()->json(['userdata' => $getuser, 'daileordercount' => $datewiseordercount,'orderscount' => $monthlyordercount, 'branddetail' => $branddetail, 'userpicturepath' => $userpicturepath, 'logopath' => $logopath, 'message' => 'Lead Dashboard Details'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function adminpatchdashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'yearmonth'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$getyearandmonth = explode('-', $setyearmonth);
		$getfirstdate = $setyearmonth."-01";
		if($yearmonth[1] == "1" || $yearmonth[1] == "3" || $yearmonth[1] == "5" || $yearmonth[1] == "7" || $yearmonth[1] == "8" || $yearmonth[1] == "10" || $yearmonth[1] == "12"){
			$noofdays = 31;
		}elseif($yearmonth[1] == "2"){
			$noofdays = 28;
		}else{
			$noofdays = 30;
		}
		$list=array();
		for($d=1; $d<=$noofdays; $d++)
		{
		    $time=mktime(12, 0, 0, $getyearandmonth[1], $d, $getyearandmonth[0]);          
		    if (date('m', $time)==$getyearandmonth[1])       
		        $list[]=date('Y-m-d', $time);
		}
		$totalbrand = DB::table('brand')
		->select('brand_id')
		->where('brandtype_id','=',2)
		->where('status_id','=',1)
		->get();
		$brands = array();
		foreach($totalbrand as $totalbrands){
			$brands[] =  $totalbrands->brand_id;
		}
		$sumforwardedtoproduction = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchstatus_id','=',1)
		->where('status_id','=',1)
		->sum('patch_amount');
		$sumreturnfromproduction = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchstatus_id','=',2)
		->where('status_id','=',1)
		->sum('patch_amount');
		$sumondelivery = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchstatus_id','=',3)
		->where('status_id','=',1)
		->sum('patch_amount');
		$sumdelivered = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchstatus_id','=',4)
		->where('status_id','=',1)
		->sum('patch_amount');
		$forwardedtoproduction = DB::table('patch')
		->select('patch_is')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchstatus_id','=',1)
		->where('status_id','=',1)
		->count();
		$returnfromproduction = DB::table('patch')
		->select('patch_is')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchstatus_id','=',2)
		->where('status_id','=',1)
		->count();
		$ondelivery = DB::table('patch')
		->select('patch_is')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchstatus_id','=',3)
		->where('status_id','=',1)
		->count();
		$delivered = DB::table('patch')
		->select('patch_is')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchstatus_id','=',4)
		->where('status_id','=',1)
		->count();
		$orderdata = array(
			'forwardedtoproduction' 	=> $forwardedtoproduction,
			'returnfromproduction' 		=> $returnfromproduction,
			'ondelivery' 				=> $ondelivery,
			'delivered' 				=> $delivered,
			'sumforwardedtoproduction' 	=> $sumforwardedtoproduction,
			'sumreturnfromproduction' 	=> $sumreturnfromproduction,
			'sumondelivery' 			=> $sumondelivery,
			'sumdelivered' 				=> $sumdelivered,
		);
		$forwardedtomanager = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',1)
		->where('status_id','=',1)
		->count();
		$pickbymanager = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',9)
		->where('status_id','=',1)
		->count();
		$forwardedtovendor = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',2)
		->where('status_id','=',1)
		->count();
		$returntomanager = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',3)
		->where('status_id','=',1)
		->count();
		$returntoagent = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',4)
		->where('status_id','=',1)
		->count();
		$senttoclient = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',5)
		->where('status_id','=',1)
		->count();
		$approve = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',6)
		->where('status_id','=',1)
		->count();
		$reject = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',7)
		->where('status_id','=',1)
		->count();
		$editbyclient = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',8)
		->where('status_id','=',1)
		->count();
		$sumforwardedtomanager = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',1)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumpickbymanager = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',9)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumforwardedtovendor = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',2)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumreturntomanager = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',3)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumreturntoagent = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',4)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumsenttoclient = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',5)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumapprove = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',6)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumreject = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',7)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumeditbyclient = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->whereIn('brand_id', $brands)
		->where('patchquerystatus_id','=',8)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$querydata = array(
			'forwardedtomanager' 		=> $forwardedtomanager,
			'pickbymanager' 			=> $pickbymanager,
			'forwardedtovendor' 		=> $forwardedtovendor,
			'returntomanager' 			=> $returntomanager,
			'returntoagent' 			=> $returntoagent,
			'senttoclient' 				=> $senttoclient,
			'approve' 					=> $approve,
			'reject' 					=> $reject,
			'editbyclient' 				=> $editbyclient,
			'sumforwardedtomanager' 	=> $sumforwardedtomanager,
			'sumpickbymanager' 			=> $sumpickbymanager,
			'sumforwardedtovendor' 		=> $sumforwardedtovendor,
			'sumreturntomanager' 		=> $sumreturntomanager,
			'sumreturntoagent' 			=> $sumreturntoagent,
			'sumsenttoclient' 			=> $sumsenttoclient,
			'sumapprove' 				=> $sumapprove,
			'sumreject' 				=> $sumreject,
			'sumeditbyclient' 			=> $sumeditbyclient,
		);
		$total = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('status_id','=',1)
		->sum('patch_amount');
		$pending = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patch_biillingstatus','=',"Pending")
		->where('status_id','=',1)
		->sum('patch_amount');
		$paid = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patch_biillingstatus','=',"Paid")
		->where('status_id','=',1)
		->sum('patch_amount');
		$cancel = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->whereIn('brand_id', $brands)
		->where('patch_biillingstatus','=',"Cancel")
		->where('status_id','=',1)
		->sum('patch_amount');
		$billingdata = array(
			'total' 	=> $total,
			'pending' 	=> $pending,
			'paid' 		=> $paid,
			'cancel' 	=> $cancel,
		);
		return response()->json(['orderdata' => $orderdata,'querydata' => $querydata,'billingdata' => $billingdata,'message' => 'Admin Dashboard'],200);
	}
	public function patchbranddetails($yearmonth, $brand_id){
		$yearmonth = explode('-',$yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$branddetails = DB::table('brand')
		->select('*')
		->where('brand_id','=',$brand_id)
		->where('status_id','=',1)
		->first();
		$branddetails->brand_currency = $branddetails->brand_currency == 1 ? "$" : " £";
		$getyearandmonth = explode('-', $setyearmonth);
		$getfirstdate = $setyearmonth."-01";
		if($yearmonth[1] == "1" || $yearmonth[1] == "3" || $yearmonth[1] == "5" || $yearmonth[1] == "7" || $yearmonth[1] == "8" || $yearmonth[1] == "10" || $yearmonth[1] == "12"){
			$noofdays = 31;
		}elseif($yearmonth[1] == "2"){
			$noofdays = 28;
		}else{
			$noofdays = 30;
		}
		$list=array();
		for($d=1; $d<=$noofdays; $d++)
		{
		    $time=mktime(12, 0, 0, $getyearandmonth[1], $d, $getyearandmonth[0]);          
		    if (date('m', $time)==$getyearandmonth[1])       
		        $list[]=date('Y-m-d', $time);
		}
		$getbranduserid = DB::table('userbarnd')
		->select('user_id')
		->where('brand_id','=',$brand_id)
		->where('status_id','=',1)
		->get();
		$sortuserbrand = array();
		foreach ($getbranduserid as $getbranduserids) {
			$sortuserbrand[] = $getbranduserids->user_id;
		}
		$forwardedtoproduction = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchstatus_id','=',1)
		->where('status_id','=',1)
		->sum('patch_amount');
		$returnfromproduction = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchstatus_id','=',2)
		->where('status_id','=',1)
		->sum('patch_amount');
		$ondelivery = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchstatus_id','=',3)
		->where('status_id','=',1)
		->sum('patch_amount');
		$delivered = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchstatus_id','=',4)
		->where('status_id','=',1)
		->sum('patch_amount');
		$orderdata = array(
			'forwardedtoproduction' 	=> $forwardedtoproduction,
			'returnfromproduction' 		=> $returnfromproduction,
			'ondelivery' 				=> $ondelivery,
			'delivered' 				=> $delivered,
		);
		$forwardedtomanager = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchquerystatus_id','=',1)
		->where('status_id','=',1)
		->count();
		$pickbymanager = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchquerystatus_id','=',9)
		->where('status_id','=',1)
		->count();
		$forwardedtovendor = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchquerystatus_id','=',2)
		->where('status_id','=',1)
		->count();
		$returntomanager = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchquerystatus_id','=',3)
		->where('status_id','=',1)
		->count();
		$returntoagent = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchquerystatus_id','=',4)
		->where('status_id','=',1)
		->count();
		$senttoclient = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchquerystatus_id','=',5)
		->where('status_id','=',1)
		->count();
		$approve = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchquerystatus_id','=',6)
		->where('status_id','=',1)
		->count();
		$reject = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchquerystatus_id','=',7)
		->where('status_id','=',1)
		->count();
		$editbyclient = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$brand_id)
		->where('patchquerystatus_id','=',8)
		->where('status_id','=',1)
		->count();
		$querydata = array(
			'forwardedtomanager' 	=> $forwardedtomanager,
			'pickbymanager' 		=> $pickbymanager,
			'forwardedtovendor' 	=> $forwardedtovendor,
			'returntomanager' 		=> $returntomanager,
			'returntoagent' 		=> $returntoagent,
			'senttoclient' 			=> $senttoclient,
			'approve' 				=> $approve,
			'reject' 				=> $reject,
			'editbyclient' 			=> $editbyclient,
		);
		$getuser = DB::table('user')
		->select('user_id','user_name','user_picture','user_target')
		->whereIn('user_id',$sortuserbrand)
		->whereIn('role_id',[6,7])
		->where('status_id','=',1)
		->get();
		$topagent = array();
		$topindex=0;
		$topthree=0;
		foreach ($getuser as $getusers) {
			$getachieve = DB::table('patch')
			->select('patch_amount')
			->where('status_id','=',1)
			->where('patch_date','like',$setyearmonth.'%')
			->where('created_by','=',$getusers->user_id)
			->sum('patch_amount');
			if ($getachieve != 0 && $topthree <= 2) {
				$getusers->achieve = $getachieve;
				$topagent[$topindex] = $getusers;	
				$topthree++;
			}
			$topindex++;
		}
		$topagent = array_sort($topagent, 'achieve', SORT_DESC);
		$sorttopagent = array();
		$sortindex=0;
		foreach($topagent as $topagents){
			$sorttopagent[$sortindex] = $topagents;
			$sortindex++;
		}
		$agenttarget = array();
		$target=0;
		foreach ($getuser as $getusers) {
			$getachieve = DB::table('patch')
			->select('patch_amount')
			->where('status_id','=',1)
			->where('brand_id','=',$brand_id)
			->where('patch_date','like',$setyearmonth.'%')
			->where('created_by','=',$getusers->user_id)
			->sum('patch_amount');
			$getpaid = DB::table('patch')
			->select('patch_amount')
			->where('status_id','=',1)
			->where('brand_id','=',$brand_id)
			->where('patch_biillingstatus','=',"Paid")
			->where('patch_date','like',$setyearmonth.'%')
			->where('created_by','=',$getusers->user_id)
			->sum('patch_amount');
			$getcancel = DB::table('patch')
			->select('patch_amount')
			->where('status_id','=',1)
			->where('brand_id','=',$brand_id)
			->where('patch_biillingstatus','=',"Cancel")
			->where('patch_date','like',$setyearmonth.'%')
			->where('created_by','=',$getusers->user_id)
			->sum('patch_amount');
			$getusers->achieve = $getachieve;
			$getusers->paid = $getpaid;
			$getusers->cancel = $getcancel;
			$agenttarget[$target] = $getusers;	
			$target++;
		}
		$graphdatatotal = array();
		$indextotal = 0;
		foreach ($list as $lists) {
			$total = DB::table('patch')
			->select('patch_amount')
			->where('patch_date','=', $lists)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$brand_id)
			->where('status_id','=',1)
			->sum('patch_amount');
			$graphdatatotal[$indextotal] = $total;
			$indextotal++;
		}
		$graphdatapaid = array();
		$indexpaid = 0;
		foreach ($list as $lists) {
			$paid = DB::table('patch')
			->select('patch_amount')
			->where('patch_biillingstatus','=',"Paid")
			->where('patch_date','=', $lists)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$brand_id)
			->where('status_id','=',1)
			->sum('patch_amount');
			$graphdatapaid[$indexpaid] = $paid;
			$indexpaid++;
		}
		$graphdataramaining = array();
		$indexcancel = 0;
		foreach ($list as $lists) {
			$cancel = DB::table('patch')
			->select('patch_amount')
			->where('patch_biillingstatus','=',"Cancel")
			->where('patch_date','=', $lists)
			->whereIn('created_by',$sortuserbrand)
			->where('brand_id','=',$brand_id)
			->where('status_id','=',1)
			->sum('patch_amount');
			$graphdataramaining[$indexcancel] = $cancel;
			$indexcancel++;
		}
		$orders = DB::table('patch')
		->select('patch_title','patch_quantity','patch_amount','patch_deliverycost','patch_date')
		->whereIn('patch_date',$list)
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserbrand)
		->get();
		$query = DB::table('patchquerylist')
		->select('patchquery_title','patchquery_quantity','patchquery_amount','patchquery_deliverycost','patchquery_date','patchquerystatus_name','user_name')
		->whereIn('patchquery_date',$list)
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserbrand)
		->get();
		$userpicturepath = URL::to('/')."/public/user_picture/";
		$brandlogopath = URL::to('/')."/public/brand_logo/";
		return array('branddetails' => $branddetails,'orderdata' => $orderdata,'querydata' => $querydata,'sorttopagent' =>$sorttopagent,'agenttarget' => $agenttarget,'orders' => $orders,'query' => $query,'graphdatatotal' => $graphdatatotal,'graphdatapaid' => $graphdatapaid,'graphdataramaining' => $graphdataramaining,'userpicturepath' => $userpicturepath,'brandlogopath' => $brandlogopath);
	}
	public function salespatchdashboard(Request $request){
		$validate = Validator::make($request->all(), [ 
			'yearmonth'	=> 'required',
			'brand_id'	=> 'required',
			'id'		=> 'required',
		]);
		if ($validate->fails()) {    
		    return response()->json($validate->errors(), 400);
		}
		$yearmonth = explode('-',$request->yearmonth);
		if($yearmonth[1] <= 9){
			$setyearmonth = $yearmonth[0].'-0'.$yearmonth[1];
		}else{
			$setyearmonth = $yearmonth[0].'-'.$yearmonth[1];
		}
		$getyearandmonth = explode('-', $setyearmonth);
		$getfirstdate = $setyearmonth."-01";
		if($yearmonth[1] == "1" || $yearmonth[1] == "3" || $yearmonth[1] == "5" || $yearmonth[1] == "7" || $yearmonth[1] == "8" || $yearmonth[1] == "10" || $yearmonth[1] == "12"){
			$noofdays = 31;
		}elseif($yearmonth[1] == "2"){
			$noofdays = 28;
		}else{
			$noofdays = 30;
		}
		$list=array();
		for($d=1; $d<=$noofdays; $d++)
		{
		    $time=mktime(12, 0, 0, $getyearandmonth[1], $d, $getyearandmonth[0]);          
		    if (date('m', $time)==$getyearandmonth[1])       
		        $list[]=date('Y-m-d', $time);
		}
		$forwardedtoproduction = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',1)
		->where('status_id','=',1)
		->sum('patch_amount');
		$returnfromproduction = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',2)
		->where('status_id','=',1)
		->sum('patch_amount');
		$ondelivery = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',3)
		->where('status_id','=',1)
		->sum('patch_amount');
		$delivered = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',4)
		->where('status_id','=',1)
		->sum('patch_amount');
		$sumforwardedtoproduction = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',1)
		->where('status_id','=',1)
		->sum('patch_amount');
		$sumreturnfromproduction = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',2)
		->where('status_id','=',1)
		->sum('patch_amount');
		$sumondelivery = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',3)
		->where('status_id','=',1)
		->sum('patch_amount');
		$sumdelivered = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',4)
		->where('status_id','=',1)
		->sum('patch_amount');
		$forwardedtoproduction = DB::table('patch')
		->select('patch_is')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',1)
		->where('status_id','=',1)
		->count();
		$returnfromproduction = DB::table('patch')
		->select('patch_is')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',2)
		->where('status_id','=',1)
		->count();
		$ondelivery = DB::table('patch')
		->select('patch_is')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',3)
		->where('status_id','=',1)
		->count();
		$delivered = DB::table('patch')
		->select('patch_is')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchstatus_id','=',4)
		->where('status_id','=',1)
		->count();
		$orderdata = array(
			'forwardedtoproduction' 	=> $forwardedtoproduction,
			'returnfromproduction' 		=> $returnfromproduction,
			'ondelivery' 				=> $ondelivery,
			'delivered' 				=> $delivered,
			'sumforwardedtoproduction' 	=> $sumforwardedtoproduction,
			'sumreturnfromproduction' 	=> $sumreturnfromproduction,
			'sumondelivery' 			=> $sumondelivery,
			'sumdelivered' 				=> $sumdelivered,
		);
		$forwardedtomanager = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',1)
		->where('status_id','=',1)
		->count();
		$pickbymanager = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',9)
		->where('status_id','=',1)
		->count();
		$forwardedtovendor = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',2)
		->where('status_id','=',1)
		->count();
		$returntomanager = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',3)
		->where('status_id','=',1)
		->count();
		$returntoagent = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',4)
		->where('status_id','=',1)
		->count();
		$senttoclient = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',5)
		->where('status_id','=',1)
		->count();
		$approve = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',6)
		->where('status_id','=',1)
		->count();
		$reject = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',7)
		->where('status_id','=',1)
		->count();
		$editbyclient = DB::table('patchquery')
		->select('patchquery_id')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',8)
		->where('status_id','=',1)
		->count();
		$sumforwardedtomanager = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',1)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumpickbymanager = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',9)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumforwardedtovendor = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',2)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumreturntomanager = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',3)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumreturntoagent = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',4)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumsenttoclient = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',5)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumapprove = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',6)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumreject = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',7)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$sumeditbyclient = DB::table('patchquery')
		->select('patchquery_amount')
		->whereIn('patchquery_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patchquerystatus_id','=',8)
		->where('status_id','=',1)
		->sum('patchquery_amount');
		$querydata = array(
			'forwardedtomanager' 		=> $forwardedtomanager,
			'pickbymanager' 			=> $pickbymanager,
			'forwardedtovendor' 		=> $forwardedtovendor,
			'returntomanager' 			=> $returntomanager,
			'returntoagent' 			=> $returntoagent,
			'senttoclient' 				=> $senttoclient,
			'approve' 					=> $approve,
			'reject' 					=> $reject,
			'editbyclient' 				=> $editbyclient,
			'sumforwardedtomanager' 	=> $sumforwardedtomanager,
			'sumpickbymanager' 			=> $sumpickbymanager,
			'sumforwardedtovendor' 		=> $sumforwardedtovendor,
			'sumreturntomanager' 		=> $sumreturntomanager,
			'sumreturntoagent' 			=> $sumreturntoagent,
			'sumsenttoclient' 			=> $sumsenttoclient,
			'sumapprove' 				=> $sumapprove,
			'sumreject' 				=> $sumreject,
			'sumeditbyclient' 			=> $sumeditbyclient,
		);
		$total = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('status_id','=',1)
		->sum('patch_amount');
		$pending = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patch_biillingstatus','=',"Pending")
		->where('status_id','=',1)
		->sum('patch_amount');
		$paid = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patch_biillingstatus','=',"Paid")
		->where('status_id','=',1)
		->sum('patch_amount');
		$cancel = DB::table('patch')
		->select('patch_amount')
		->whereIn('patch_date', $list)
		->where('brand_id','=',$request->brand_id)
		->where('created_by','=',$request->id)
		->where('patch_biillingstatus','=',"Cancel")
		->where('status_id','=',1)
		->sum('patch_amount');
		$billingdata = array(
			'total' 	=> $total,
			'pending' 	=> $pending,
			'paid' 		=> $paid,
			'cancel' 	=> $cancel,
		);
		return response()->json(['orderdata' => $orderdata,'querydata' => $querydata,'billingdata' => $billingdata,'message' => 'Admin Dashboard'],200);
	}
	// patch admin dashboard end
}