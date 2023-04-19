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
		$userbrand = DB::table('userbarnd')
		->select('brand_id')
		->where('user_id','=',$request->user_id)
		->where('status_id','=',1)
		->get();
		$sortbrand = array();
		foreach($userbrand as $brands){
			$sortbrand[] = $brands->brand_id;
		}
		$userids = DB::table('userbarnd')
		->select('user_id')
		->whereIn('brand_id',$sortbrand)
		->where('status_id','=',1)
		->get();
		$sortuserids = array();
		foreach($userids as $useridss){
			$sortuserids[] = $useridss->user_id;
		}
		$from = $request->from;
		$from = explode('-',$request->from);
		if($from[1] <= 9){
			$setfrom = $from[0].'-0'.$from[1].'-'.$from[2];
		}else{
			$setfrom = $from[0].'-'.$from[1].'-'.$from[2];
		}
		$to = $request->to;
		$to = explode('-',$request->to);
		if($to[1] <= 9){
			$setto = $to[0].'-0'.$to[1].'-'.$to[2];
		}else{
			$setto = $to[0].'-'.$to[1].'-'.$to[2];
		}
		$gettoyearandmonth = explode('-', $setto);
		$toyearandmonth = $gettoyearandmonth[0].'-'.$gettoyearandmonth[1];
		$fromyearandmonth = explode('-', $setfrom);
		$year = $fromyearandmonth[0];
		$month = $fromyearandmonth[1];
		$targetdate = $year.'-'.$month;
		$getmonth = $toyearandmonth;
		$userdetails = array();
		$userlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->whereIn('user_id',$sortuserids)
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
		->whereIn('user_id',$sortuserids)
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
		->whereIn('created_by',$sortuserids)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->sum('orderpayment_amount');
		$sumpaid = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',3)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->sum('orderpayment_amount');
		$sumcancel = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',4)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->sum('orderpayment_amount');
		$sumrefund = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',5)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->sum('orderpayment_amount');
		$sumchargeback = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',6)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->sum('orderpayment_amount');
		$sumrecovery = DB::table('orderpayment')
		->select('orderpayment_amount')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',7)
		->whereBetween('orderpayment_recoverydate', [$setfrom, $setto])
		->sum('orderpayment_amount');
		$sumunpaid = $sumachieved-$sumpaid-$sumcancel-$sumrefund-$sumchargeback;
		$sumcountachieved = DB::table('orderpayment')
		->select('orderpayment_id')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->count();
		$sumcountpaid = DB::table('orderpayment')
		->select('orderpayment_id')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',3)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->count();
		$sumcountcancel = DB::table('orderpayment')
		->select('orderpayment_id')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',4)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->count();
		$sumcountrefund = DB::table('orderpayment')
		->select('orderpayment_id')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',5)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->count();
		$sumcountchargeback = DB::table('orderpayment')
		->select('orderpayment_id')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',6)
		->whereBetween('orderpayment_date', [$setfrom, $setto])
		->count();
		$sumcountrecovery = DB::table('orderpayment')
		->select('orderpayment_id')
		->where('status_id','=',1)
		->whereIn('created_by',$sortuserids)
		->where('orderpaymentstatus_id','=',7)
		->whereBetween('orderpayment_recoverydate', [$setfrom, $setto])
		->count();
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
			if(isset($userlist->user_id)){
				$targetincrement = DB::table('usertarget')
				->select('usertarget_target')
				->where('user_id','=',$userlist->user_id)
				->where('usertarget_month','<=',$targetdate)
				->where('status_id','=',1)
				->sum('usertarget_target');
				$user_target = $userlist->user_target+$targetincrement;
				$achieved = DB::table('orderwithpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->sum('orderpayment_amount');
				$paid = DB::table('orderwithpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',3)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->sum('orderpayment_amount');
				$cancel = DB::table('orderwithpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',4)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->sum('orderpayment_amount');
				$refund = DB::table('orderwithpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',5)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->sum('orderpayment_amount');
				$chargeback = DB::table('orderwithpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',6)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->sum('orderpayment_amount');
				$recovery = DB::table('orderwithpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',7)
				->whereBetween('orderpayment_recoverydate', [$setfrom, $setto])
				->sum('orderpayment_amount');
				$unpaid = $achieved-$paid;
				$countachieved = DB::table('orderwithpayment')
				->select('orderpayment_id')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->count('orderpayment_id');
				$countpaid = DB::table('orderwithpayment')
				->select('orderpayment_id')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',3)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->count('orderpayment_id');
				$countcancel = DB::table('orderwithpayment')
				->select('orderpayment_id')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',4)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->count('orderpayment_id');
				$countrefund = DB::table('orderwithpayment')
				->select('orderpayment_id')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',5)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->count('orderpayment_id');
				$countchargeback = DB::table('orderwithpayment')
				->select('orderpayment_id')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',6)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->count('orderpayment_id');
				$countrecovery = DB::table('orderwithpayment')
				->select('orderpayment_id')
				->where('status_id','=',1)
				->where('created_by','=',$userlist->user_id)
				->where('orderpaymentstatus_id','=',7)
				->whereBetween('orderpayment_recoverydate', [$setfrom, $setto])
				->count('orderpayment_id');
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
			}else{
				$userlist->user_target = $user_target;
				$userlist->achieved = 0;
				$userlist->paid = 0;
				$userlist->cancel = 0;
				$userlist->refund = 0;
				$userlist->chargeback = 0;
				$userlist->recovery = 0;
				$userlist->unpaid = 0;
				$userlist->countachieved = 0;
				$userlist->countpaid = 0;
				$userlist->countunpaid = 0;
				$userlist->countcancel = 0;
				$userlist->countrefund = 0;
				$userlist->countchargeback = 0;
				$userlist->countrecovery = 0;
				$userdetails[$index] = $userlist;
			}
		}
		$designerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->where('role_id','=',15)
		->whereIn('user_id',$sortuserids)
		->where('status_id','=',1)
		->get();
		$designerindex=0;
		$designercommission;
		$designerdetails = array();
		foreach ($designerlist as $designerlist) {
			if(isset($designerlist->user_id)){
				$completeorders = DB::table('task')
				->select('task_id')
				->where('status_id','=',1)
				->where('taskstatus_id','>=',3)
				->where('task_workby','=',$designerlist->user_id)
				->whereBetween('task_date', [$setfrom, $setto])
				->count('task_id');
				$getcommission = DB::table('commission')
				->select('*')
				->where('status_id','=',1)
				->where('role_id','=',15)
				->orderBy('commission_id','DESC')
				->get();
				$commissionindex = 0;
				$designercommission = 0;
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
			}else{
				$designerlist->completeorders = 0;
				$designerlist->commission = 0;
				$designerdetails[$designerindex] = $designerlist;
			}
		}
		$digitizerlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->where('role_id','=',16)
		->whereIn('user_id',$sortuserids)
		->where('status_id','=',1)
		->get();
		$digitizerdetails = array();
		$digitizerindex=0;
		$digitizercommission = 0;
		foreach ($digitizerlist as $digitizerlist) {
			if(isset($digitizerlist->user_id)){
				$completeorders = DB::table('task')
				->select('task_id')
				->where('status_id','=',1)
				->where('taskstatus_id','>=',3)
				->where('task_workby','=',$digitizerlist->user_id)
				->whereBetween('task_date', [$setfrom, $setto])
				->count('task_id');
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
			}else{
				$digitizerlist->completeorder = 0;
				$digitizerlist->commission = 0;
				$digitizerdetails[$digitizerindex] = $digitizerlist;
			}
		}
		$unitheadlist = DB::table('user')
		->select('user_id','role_id','user_name','user_target','user_picture')
		->where('role_id','=',3)
		->whereIn('user_id',$sortuserids)
		->where('status_id','=',1)
		->get();
		$unitheaddetails = array();
		$unitheadindex=0;
		$unitheadcommission = 0;
		foreach ($unitheadlist as $unitheadlist) {
			if(isset($unitheadlist->user_id)){
				$unituserbrand = DB::table('userbarnd')
				->select('brand_id')
				->where('user_id','=',$unitheadlist->user_id)
				->where('status_id','=',1)
				->get();
				$unitsortbrand = array();
				foreach($unituserbrand as $unitbrands){
					$unitsortbrand[] = $unitbrands->brand_id;
				}
				$unituserids = DB::table('userbarnd')
				->select('user_id')
				->whereIn('brand_id',$unitsortbrand)
				->where('status_id','=',1)
				->get();
				$unitsortuserids = array();
				foreach($unituserids as $unituserids){
					$unitsortuserids[] = $unituserids->user_id;
				}
				$unitsumbasictarget = DB::table('user')
				->select('user_target')
				->whereIn('user_id',$unitsortuserids)
				->whereIn('role_id',[6,7])
				->where('status_id','=',1)
				->sum('user_target');
				$unitsumtargetincrement = DB::table('usertarget')
				->select('usertarget_target')
				->whereIn('user_id',$unitsortuserids)
				->where('usertarget_month','<=',$targetdate)
				->where('status_id','=',1)
				->sum('usertarget_target');
				$unitsumtarget = $unitsumbasictarget+$unitsumtargetincrement;
				$unitsummaxpaid = DB::table('orderpayment')
				->select('orderpayment_amount')
				->where('status_id','=',1)
				->where('orderpaymentstatus_id','=',3)
				->whereIn('created_by',$unitsortuserids)
				->whereBetween('orderpayment_date', [$setfrom, $setto])
				->sum('orderpayment_amount');
				$paidpatchquery = DB::table('patchquery')
				->select('patchquery_id')
				->where('status_id','=',1)
				->whereIn('patchquerystatus_id',[10,11,12])
				->whereBetween('patchquery_date', [$setfrom, $setto])
				->get();
				$sortpaidpatchquery = array();
				if(isset($paidpatchquery)){
					foreach($paidpatchquery as $paidpatchquerys){
						$sortpaidpatchquery[] = $paidpatchquerys->patchquery_id;
					}
					$unitsumpatchpaid = DB::table('patchqueryitem')
					->select('patchqueryitem_proposalquote')
					->where('status_id','=',1)
					->whereIn('patchquery_id',$sortpaidpatchquery)
					->sum('patchqueryitem_proposalquote');
					$shippingids = DB::table('patchquery')
					->select('patchqueryshipping_id')
					->where('status_id','=',1)
					->whereIn('patchquery_id',$sortpaidpatchquery)
					->get();
					if(isset($shippingids)){
						$sortpatchshippingids = array();
						foreach($shippingids as $shippingids){
							$sortpatchshippingids[] = $shippingids->patchqueryshipping_id;
						}
						$sumshippingcost = DB::table('patchqueryshipping')
						->select('patchqueryshipping_cost')
						->where('status_id','=',1)
						->whereIn('patchqueryshipping_id',$sortpatchshippingids)
						->sum('patchqueryshipping_cost');
					}else{
						$sumshippingcost = 0;
					}
					$patchvendorids = DB::table('patchqueryitem')
					->select('patchqueryitem_finalvendor')
					->where('status_id','=',1)
					->whereIn('patchquery_id',$sortpaidpatchquery)
					->get();
					if(isset($patchvendorids)){
						$sortpatchvendorids = array();
						foreach($patchvendorids as $patchvendoridss){
							$sortpatchvendorids[] = $patchvendoridss->patchqueryitem_finalvendor;
						}
						$unitsumpatchvendorcost = DB::table('patchqueryvendor')
						->select('patchqueryvendor_cost')
						->where('status_id','=',1)
						->whereIn('patchquery_id',$sortpaidpatchquery)
						->whereIn('vendorproduction_id',$sortpatchvendorids)
						->sum('patchqueryvendor_cost');
					}else{
						$unitsumpatchvendorcost = 0;
					}
				}else{
					$sumshippingcost = 0;
					$unitsumpatchvendorcost = 0;
					$unitsumpatchpaid = 0;
				}
				$unitsumpatchachieved =$unitsumpatchpaid-$sumshippingcost-$unitsumpatchvendorcost;
				$unitsumachieved = $unitsummaxpaid+$unitsumpatchachieved;
				$unitgetcommission = DB::table('commission')
				->select('*')
				->where('status_id','=',1)
				->where('user_id','=',$unitheadlist->user_id)
				->orderBy('commission_id','DESC')
				->get();
				$unitcommissionindex = 0;
				foreach ($unitgetcommission as $unitgetcommissions) {
					if ($unitsumachieved >= $unitgetcommissions->commission_from && $unitsumachieved >= $unitgetcommissions->commission_to && $unitcommissionindex == 0) {
						$unitheadcommission = $unitgetcommissions->commission_rate;
						$unitcommissionindex++;
						break;
					}else{
						$unitheadcommission = 0;
					}
				}
				$unitheadlist->target = $unitsumtarget;
				$unitheadlist->achieved = $unitsumachieved;
				$unitheadlist->commission = $unitheadcommission;
				$unitheaddetails[$unitheadindex] = $unitheadlist;
				$unitheadindex++;
			}else{
				$unitheadlist->target = 0;
				$unitheadlist->achieved = 0;
				$unitheadlist->commission = 0;
				$unitheaddetails[$unitheadindex] = $unitheadlist;
			}
		}
		$profilepath = URL::to('/')."/public/user_picture/";
      	if(isset($userdetails)){
		    return response()->json(['userdetails' => $userdetails, 'summreport' => $summreport, 'profilepath' => $profilepath, 'designerdetails' => $designerdetails, 'digitizerdetails' => $digitizerdetails, 'unitheaddetails' => $unitheaddetails, 'message' => 'Monthly Sales Target Report'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function commissionreport(Request $request){
		$validate = Validator::make($request->all(), [ 
            'from'		=> 'required',
            'to'		=> 'required',
			'id'		=> 'required',
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
		$from = $request->from;
		$from = explode('-',$request->from);
		if($from[1] <= 9){
			$setfrom = $from[0].'-0'.$from[1].'-'.$from[2];
		}else{
			$setfrom = $from[0].'-'.$from[1].'-'.$from[2];
		}
		$date = $request->to;
		$to = explode('-',$request->to);
		if($to[1] <= 9){
			$setto = $to[0].'-0'.$to[1].'-'.$to[2];
		}else{
			$setto = $to[0].'-'.$to[1].'-'.$to[2];
		}
		$getyearandmonth = explode('-', $setfrom);
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
			->whereBetween('orderpayment_date', [$setfrom, $lists])
			->sum('orderpayment_amount');
			$getrecoveryamount = DB::table('orderpayment')
			->select('orderpayment_amount')
			->where('status_id','=',1)
			->where('created_by','=',$request->id)
			->where('orderpaymentstatus_id','=',7)
			->whereBetween('orderpayment_recoverydate', [$setfrom, $lists])
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
					$getpaidorders = DB::table('orderpayment')
					->select('orderpayment_id')
					->where('orderpayment_amount','>=',10)
					->where('status_id','=',1)
					->where('created_by','=',$request->id)
					->where('orderpaymentstatus_id','=',3)
					->where('orderpayment_date','=',$lists)
					->count('orderpayment_id');
					$getrecoveryorders = DB::table('orderpayment')
					->select('orderpayment_id')
					->where('status_id','=',1)
					->where('created_by','=',$request->id)
					->where('orderpaymentstatus_id','=',7)
					->where('orderpayment_recoverydate','=',$lists)
					->count('orderpayment_id');
					if ($commissions['from'] != 1 && $gettargetachieved >= $commissions['from'] && $indexforallpaidorders == 0) {
					$achieveddate = $lists;
					$indexforallpaidorders++;
					}
					if($setfrom >= "2023-01-31"){
						$targetachp = DB::table('orderpayment')
						->select('orderpayment_amount')
						->where('status_id','=',1)
						->where('created_by','=',$request->id)
						->where('orderpaymentstatus_id','=',3)
						->whereIn('orderpayment_date', $list)
						->sum('orderpayment_amount');
						$targetachr = DB::table('orderpayment')
						->select('orderpayment_amount')
						->where('status_id','=',1)
						->where('created_by','=',$request->id)
						->where('orderpaymentstatus_id','=',7)
						->whereIn('orderpayment_recoverydate', $list)
						->sum('orderpayment_amount');
						$targetachieved = $targetachp+$targetachr;
						if($setfrom = "2023-03-1"){
							if($targetachieved >= 4000){
								$finalcommisionamount = $getpaidorders*200;
								$finalrecoveryamount = $getrecoveryorders*200;
								$finalrate = 200;
							}else{
								$finalcommisionamount = $getpaidorders*100;
								$finalrecoveryamount = $getrecoveryorders*100;
								$finalrate = 100;
							}
						}else{
							if($targetachieved >= 4500){
								$finalcommisionamount = $getpaidorders*200;
								$finalrecoveryamount = $getrecoveryorders*200;
								$finalrate = 200;
							}else{
								$finalcommisionamount = $getpaidorders*100;
								$finalrecoveryamount = $getrecoveryorders*100;
								$finalrate = 100;
							}
						}
						
					}else{
						$finalcommisionamount = $commissions['rate']*$getpaidorders;
						$finalrecoveryamount = $commissions['rate']*$getrecoveryorders;
						$finalrate = $commissions['rate'];
					}
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
		return response()->json(['commissiondata' => $commissiondata, 'targetachieveddate' => $achieveddate, 'message' => 'Monthly Employee Commission Report'],200);
	}
}