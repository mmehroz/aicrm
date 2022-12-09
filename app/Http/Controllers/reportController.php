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

class reportController extends Controller
{
	public function salestargetreport(Request $request){
        $validate = Validator::make($request->all(), [ 
            'from'		=> 'required',
            'to'		=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
		$from = $request->from;
		$to = $request->to;
		$gettoyearandmonth = explode('-', $to);
		$toyearandmonth = $gettoyearandmonth[0].'-'.$gettoyearandmonth[1];
		$fromyearandmonth = explode('-', $from);
		$year = $fromyearandmonth[0];
		$month = $fromyearandmonth[1];
		$targetdate = $year.'-'.$month;
		$getmonth = $toyearandmonth;
		$userdetails = array();
		$userlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->whereIn('role_id',[6,7])
		->where('status_id','=',1)
		->get();
		$sortuser = array();
		foreach($userlist as $userlists){
			$sortuser[] = $userlists->user_id;
		}
		$sumbasictarget = DB::table('user')
		->select('user_target')
		->whereIn('role_id',[6,7])
		->where('status_id','=',1)
		->sum('user_target');
		$sumtargetincrement = DB::table('usertarget')
		->select('usertarget_target')
		->whereIn('user_id',$sortuser)
		->where('usertarget_month','<=',$targetdate)
		->where('status_id','=',1)
		->sum('usertarget_target');
		$sumtarget = $sumbasictarget+$sumtargetincrement;
		$sumachieved = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumpaid = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',3)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumcancel = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',4)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumrefund = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',5)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumchargeback = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',6)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumrecovery = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',7)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumunpaid = $sumachieved-$sumpaid;
		$sumcountachieved = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumcountpaid = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',3)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumcountcancel = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',4)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumcountrefund = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',5)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumcountchargeback = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',6)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$sumcountrecovery = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->where('orderpaymentstatus_id','=',7)
		->where('ordertype_id','=',$request->ordertype_id)
		->whereBetween('orderpayment_date', [$from, $to])
		->sum('orderpayment_amount');
		$summreport = array(
            'sumtarget' 			=> $sumtarget,
			'sumachieved' 			=> $sumachieved,
			'sumpaid' 				=> $sumpaid,
			'sumcancel' 			=> $sumcancel,
			'sumrefund' 			=> $sumrefund,
			'sumchargeback' 		=> $sumchargeback,
			'sumrecovery' 			=> $sumrecovery,
			'sumunpaid' 			=> $sumunpaid,
			'sumcountachieved' 		=> $sumcountachieved,
			'sumcountpaid' 			=> $sumcountpaid,
			'sumcountcancel' 		=> $sumcountcancel,
			'sumcountrefund' 		=> $sumcountrefund,
			'sumcountchargeback' 	=> $sumcountchargeback,
			'sumcountrecovery' 		=> $sumcountrecovery,
		);
		$index=0;
		foreach ($userlist as $userlist) {
			$targetincrement = DB::table('usertarget')
			->select('usertarget_target')
			->where('user_id','=',$userlist->user_id)
			->where('usertarget_month','<=',$targetdate)
			->where('status_id','=',1)
			->sum('usertarget_target');
			$user_target = $userlist->user_target+$targetincrement;
			$achieved = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$paid = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',3)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$cancel = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',4)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$refund = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',5)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$chargeback = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',6)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$recovery = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',7)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$unpaid = $achieved-$paid;
			$countachieved = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$countpaid = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',3)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$countcancel = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',4)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$countrefund = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',5)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$countchargeback = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',6)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$countrecovery = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$userlist->user_id)
			->where('orderpaymentstatus_id','=',7)
			->where('ordertype_id','=',$request->ordertype_id)
			->whereBetween('orderpayment_date', [$from, $to])
			->sum('orderpayment_amount');
			$countunpaid = $countachieved-$countpaid;
			$userlist->user_target = $user_target;
			$userlist->achieved = $achieved;
			$userlist->paid = $paid;
			$userlist->cancel = $cancel;
			$userlist->refund = $refund;
			$userlist->chargeback = $chargeback;
			$userlist->recovery = $recovery;
			$userlist->unpaid = $unpaid;
			$userlist->countachieved = $countachieved;
			$userlist->countpaid = $countpaid;
			$userlist->countunpaid = $countunpaid;
			$userlist->countcancel = $countcancel;
			$userlist->countrefund = $countrefund;
			$userlist->countchargeback = $countchargeback;
			$userlist->countrecovery = $countrecovery;
			$userdetails[$index] = $userlist;
			$index++;
		}
		$designerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->where('role_id','=',15)
		->where('status_id','=',1)
		->get();
		$designerindex=0;
		$designercommission;
		$designerdetails = array();
		foreach ($designerlist as $designerlist) {
			$completeorders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>',4)
			->where('ordertype_id','=',$request->ordertype_id)
			->where('order_pickby','=',$designerlist->user_id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');
			$getcommission = DB::table('commission')
			->select('*')
			->where('status_id','=',1)
			->where('role_id','=',15)
			->orderBy('commission_id','DESC')
			->get();
			$commissionindex = 0;
			foreach ($getcommission as $getcommissions) {
				if ($completeorders >= $getcommissions->commission_from && $completeorders >= $getcommissions->commission_to && $commissionindex == 0) {
					$designercommission = $getcommissions->commission_rate;
					$commissionindex++;
					break;
				}else{
					$designercommission = 0;
				}
			}
			$designerlist->completeorders = $completeorders;
			$designerlist->commission = $designercommission;
			$designerdetails[$designerindex] = $designerlist;
			$designerindex++;
		}
		$digitizerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->where('role_id','=',16)
		->where('status_id','=',1)
		->get();
		$digitizerdetails = array();
		$digitizerindex=0;
		$digitizercommission = 0;
		foreach ($digitizerlist as $digitizerlist) {
			$completeorders = DB::table('order')
			->select('order_id')
			->where('status_id','=',1)
			->where('orderstatus_id','>',4)
			->where('ordertype_id','=',$request->ordertype_id)
			->where('order_pickby','=',$designerlist->user_id)
			->whereBetween('order_date', [$from, $to])
			->count('order_id');
			$getcommission = DB::table('commission')
			->select('*')
			->where('status_id','=',1)
			->where('user_id','=',$digitizerlist->user_id)
			->orderBy('commission_id','DESC')
			->get();
			$commissionindex = 0;
			foreach ($getcommission as $getcommissions) {
				if ($completeorders >= $getcommissions->commission_from && $completeorders >= $getcommissions->commission_to && $commissionindex == 0) {
					$digitizercommission = $getcommissions->commission_rate;
					$commissionindex++;
					break;
				}else{
					$digitizercommission = 0;
				}
			}
			$digitizerlist->completeorder = $completeorders;
			$digitizerlist->commission = $digitizercommission;
			$digitizerdetails[$digitizerindex] = $digitizerlist;
			$digitizerindex++;
		}
		$profilepath = URL::to('/')."/public/user_picture/";
      	if(isset($userdetails)){
		    return response()->json(['userdetails' => $userdetails, 'summreport' => $summreport, 'profilepath' => $profilepath, 'designerdetails' => $designerdetails, 'digitizerdetails' => $digitizerdetails, 'message' => 'Monthly Sales Target Report'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function commissionreport(Request $request){
		$validate = Validator::make($request->all(), [ 
            'from'		=> 'required',
            'to'		=> 'required',
			'date'		=> 'required',
			'id'		=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
		$date = $request->date;
		$getyearandmonth = explode('-', $date);
		$list=array();
		$year = $getyearandmonth[0];
		$month = $getyearandmonth[1];
		for($d=1; $d<=31; $d++)
		{
		    $time=mktime(12, 0, 0, $month, $d, $year);          
		    if (date('m', $time)==$month)       
		    $list[]=date('Y-m-d', $time);
		}
		$commissiondata =  array();
		$achieveddate = '-';
		$finalcommisionamount=0;
		$finalrecoveryamount=0;
		$finalrate=0;
		$commissionindex=0;
		$indexforallpaidorders = 0;
		$finalpaidorders = 0;
		$finalrecoveryorders = 0;
		foreach ($list as $lists) {
			$getpaidamount = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderpaymentstatus_id','=',3)
			->whereBetween('orderpayment_date', [$request->from, $lists])
			->sum('orderpayment_amount');
			$getrecoveryamount = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderpaymentstatus_id','=',7)
			->whereBetween('orderpayment_date', [$request->from, $lists])
			->sum('orderpayment_amount');
			$gettargetachieved = $getpaidamount + $getrecoveryamount;
			$getcommission = DB::table('commission')
			->select('commission_rate', 'commission_from' , 'commission_to')
			->where('status_id','=',1)
			->where('user_id','=',$request->id)
			->get();
			$commission = array();
			$index = 0;
			foreach ($getcommission as $getcommissions) {
				$commission[$index]['rate'] = $getcommissions->commission_rate;
				$commission[$index]['from'] = $getcommissions->commission_from;
				$commission[$index]['to'] = $getcommissions->commission_to;
				$index++;
			}
			foreach ($commission as $commissions) {
			if ($gettargetachieved >= $commissions['from'] && $gettargetachieved <= $commissions['to']) {
					$getpaidorders = DB::table('order')
					->select('order_id')
					->where('status_id','=',1)
					->where('created_by','=',$request->id)
					->where('order_id','=',11)
					->where('order_date','=',$lists)
					->count('order_id');
					$getrecoveryorders = DB::table('orderpayment')
					->select('order_amountquoted')
					->where('status_id','=',1)
					->where('created_by','=',$request->id)
					->where('orderpaymentstatus_id','=',18)
					->where('order_recoverydate','=',$lists)
					->count('order_id');
					if ($commissions['from'] != 1 && $gettargetachieved >= $commissions['from'] && $indexforallpaidorders == 0) {
					$achieveddate = $lists;
					$indexforallpaidorders++;
					}
				$finalcommisionamount = $commissions['rate']*$getpaidorders;
				$finalrecoveryamount = $commissions['rate']*$getrecoveryorders;
				$finalrate = $commissions['rate'];
				$finalpaidorders = $getpaidorders;
				$finalrecoveryorders = $getrecoveryorders;
				break;
			}else{
				$finalcommisionamount = 0;
				$finalrecoveryamount = 0;
				$finalrate = 0;
				$finalpaidorders = 0;
				$finalrecoveryorders = 0;
			}			
			}
			$commissiondata[$commissionindex]['finalcommisionamount'] = $finalcommisionamount;
			$commissiondata[$commissionindex]['finalrecoveryamount'] = $finalrecoveryamount;
			$commissiondata[$commissionindex]['finalrate'] = $finalrate;
			$commissiondata[$commissionindex]['finalpaidorders'] = $finalpaidorders;
			$commissiondata[$commissionindex]['finalrecoveryorders'] = $finalrecoveryorders;
			$commissiondata[$commissionindex]['date'] = $lists;
			$commissionindex++;
		}
		return response()->json(['commissiondata' => $commissiondata, 'targetachieveddate' => '2022-12-09', 'message' => 'Monthly Employee Commission Report'],200);
	}
}