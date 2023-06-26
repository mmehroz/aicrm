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

class dashboardController extends Controller {
    public function admindashboard( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'yearmonth'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $yearmonth = explode( '-', $request->yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $getyearandmonth = explode( '-', $setyearmonth );
        $getfirstdate = $setyearmonth.'-01';
        if ( $yearmonth[ 1 ] == '1' || $yearmonth[ 1 ] == '3' || $yearmonth[ 1 ] == '5' || $yearmonth[ 1 ] == '7' || $yearmonth[ 1 ] == '8' || $yearmonth[ 1 ] == '10' || $yearmonth[ 1 ] == '12' ) {
            $noofdays = 31;
        } elseif ( $yearmonth[ 1 ] == '2' ) {
            $noofdays = 28;
        } else {
            $noofdays = 30;
        }
        $list = array();
        for ( $d = 1; $d <= $noofdays; $d++ ) {
            $time = mktime( 12, 0, 0, $getyearandmonth[ 1 ], $d, $getyearandmonth[ 0 ] );

            if ( date( 'm', $time ) == $getyearandmonth[ 1 ] )
            $list[] = date( 'Y-m-d', $time );
        }
        $totalbrand = DB::table( 'userbarnd' )
        ->select( 'brand_id' )
        ->where( 'user_id', '=', $request->user_id )
        ->where( 'status_id', '=', 1 )
        ->get();
        $brands = array();
        foreach ( $totalbrand as $totalbrands ) {
            $brands[] = $totalbrands->brand_id;
        }
        $totaluser = DB::table( 'userbarnd' )
        ->select( 'user_id' )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->get();
        $userids = array();
        foreach ( $totaluser as $totalusers ) {
            $userids[] = $totalusers->user_id;
        }
        $digitalbrand = DB::table( 'brand' )
        ->select( 'brand_id', 'brand_name' )
        ->whereIn( 'brand_id', $brands )
        ->where( 'brandtype_id', '=', 1 )
        ->where( 'status_id', '=', 1 )
        ->get();
        $dindex = 0;
        $digitalbrandachieved = array();
        foreach ( $digitalbrand as $digitalbrands ) {
            $brandachieved = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'brand_id', '=', $digitalbrands->brand_id )
            ->where( 'orderpaymentstatus_id', '=', 3 )
            ->whereIn( 'orderpayment_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'orderpayment_amount' );
            $digitalbrands->brandachieved = $brandachieved;
            $digitalbrandachieved[ $dindex ] = $digitalbrands;
            $dindex++;
        }
        $patchbrand = DB::table( 'brand' )
        ->select( 'brand_id', 'brand_name' )
        ->whereIn( 'brand_id', $brands )
        ->where( 'brandtype_id', '=', 2 )
        ->where( 'status_id', '=', 1 )
        ->get();
        $pindex = 0;
        $patchbrandachieved = array();
        foreach ( $patchbrand as $patchbrands ) {
            $brandachieved = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'brand_id', '=', $patchbrands->brand_id )
            ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchbrands->brandachieved = $brandachieved;
            $patchbrandachieved[ $pindex ] = $patchbrands;
            $pindex++;
        }
        $grosssale = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->whereIn( 'orderpayment_date', $list )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $invoicesale = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'orderpaymentstatus_id', '=', 2 )
        ->whereIn( 'orderpayment_date', $list )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $paidsale = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'orderpaymentstatus_id', '=', 3 )
        ->whereIn( 'orderpayment_date', $list )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $cancel = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'orderpaymentstatus_id', '=', 4 )
        ->whereIn( 'orderpayment_date', $list )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $refund = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'orderpaymentstatus_id', '=', 5 )
        ->whereIn( 'orderpayment_date', $list )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $chargeback = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'orderpaymentstatus_id', '=', 6 )
        ->whereIn( 'orderpayment_date', $list )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $recovery = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'orderpaymentstatus_id', '=', 7 )
        ->whereIn( 'orderpayment_recoverydate', $list )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $totalunpaid = $grosssale-$paidsale-$cancel-$refund-$chargeback;
        $totaltarget = DB::table( 'user' )
        ->select( 'user_target' )
        ->whereIn( 'user_id', $userids )
        ->where( 'status_id', '=', 1 )
        ->sum( 'user_target' );
        $targetincrement = DB::table( 'usertarget' )
        ->select( 'usertarget_target' )
        ->whereIn( 'user_id', $userids )
        ->where( 'usertarget_month', '<=', $setyearmonth )
        ->where( 'status_id', '=', 1 )
        ->sum( 'usertarget_target' );
        $gettotaltarget = $totaltarget+$targetincrement;
        $gettotalachieve = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'status_id', '=', 1 )
        ->whereIn( 'orderpayment_date', $list )
        ->whereIn( 'brand_id', $brands )
        ->sum( 'orderpayment_amount' );
        $previousrecover = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'orderpaymentstatus_id', '=', 7 )
        ->where( 'orderpayment_date', '<', $getfirstdate )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $previouscancel = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'orderpaymentstatus_id', '=', 4 )
        ->where( 'orderpayment_date', '<', $getfirstdate )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $previouspaid = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->where( 'orderpaymentstatus_id', '=', 3 )
        ->where( 'orderpayment_date', '<', $getfirstdate )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $previousunpaid = DB::table( 'orderpayment' )
        ->select( 'orderpayment_amount' )
        ->whereNotIn( 'orderpaymentstatus_id', [ 3, 4, 7 ] )
        ->where( 'orderpayment_date', '<', $getfirstdate )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
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
            'previousrecover' 	=> $previousrecover,
            'previouscancel' 	=> $previouscancel,
            'previouspaid' 		=> $previouspaid,
            'previousunpaid' 	=> $previousunpaid,
        );
        $getupcommingpayments = DB::table( 'orderpaymentdetails' )
        ->select( 'order_title', 'orderpayment_id', 'orderpayment_title', 'orderpayment_amount', 'user_name', 'user_picture' )
        ->whereNotIn( 'orderpaymentstatus_id', [ 3, 4 ] )
        ->whereIn( 'brand_id', $brands )
        ->whereIn( 'orderpayment_date', $list )
        ->where( 'status_id', '=', 1 )
        ->get();
        $sumupcommingpayments = DB::table( 'orderpaymentdetails' )
        ->select( 'orderpayment_amount' )
        ->whereNotIn( 'orderpaymentstatus_id', [ 3, 4 ] )
        ->whereIn( 'brand_id', $brands )
        ->whereIn( 'orderpayment_date', $list )
        ->where( 'status_id', '=', 1 )
        ->sum( 'orderpayment_amount' );
        $pendingtask = DB::table( 'tasklist' )
        ->select( 'task_id', 'task_title', 'task_deadlinedate', 'taskstatus_name', 'creator', 'ordercreatorname' )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->orderByDesc( 'task_id' )
        ->limit( 30 )
        ->get();

        $ppcassign = DB::table( 'assignppc' )
        ->select( 'assignppc_amount' )
        ->where( 'assignppc_month', '=', $setyearmonth )
        ->where( 'status_id', '=', 1 )
        ->sum( 'assignppc_amount' );
        $ppcspend = DB::table( 'ppc' )
        ->select( 'ppc_amount' )
        ->whereIn( 'ppc_date', $list )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->sum( 'ppc_amount' );
        $remainingppc = $ppcassign-$ppcspend;
        $ppcverview = array(
            'ppcassign' 		=> $ppcassign,
            'ppcspend' 			=> $ppcspend,
            'remainingppc' 		=> $remainingppc,
        );
        $patchproductionitem = DB::table( 'patchqueryitem' )
        ->select( 'patchqueryitem_id', 'patchqueryitem_finalvendor' )
        ->whereIn( 'patchqueryitem_date', $list )
        ->where( 'status_id', '=', 1 )
        ->get();
        $patchproductionitemid = array();
        $patchproductionitemvendor = array();
        $patchindex = 0;
        foreach ( $patchproductionitem as $patchproductionitem ) {
            $patchproductionitemid[] = $patchproductionitem->patchqueryitem_id;
            $patchproductionitemvendor[] = $patchproductionitem->patchqueryitem_finalvendor;
            $patchindex++;
        }
        $patchproductioncost = DB::table( 'patchqueryvendor' )
        ->select( 'patchqueryvendor_cost' )
        ->whereIn( 'vendorproduction_id', $patchproductionitemvendor )
        ->whereIn( 'patchqueryitem_id', $patchproductionitemid )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryvendor_cost' );
        $patchpaidcost = DB::table( 'patchpayment' )
        ->select( 'patchpayment_amount' )
        ->whereIn( 'patch_createdate', $list )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchpayment_amount' );
        $patchpayablecost = $patchproductioncost-$patchpaidcost;
        $patchshipmentcost = DB::table( 'patchquery' )
        ->select( 'patchquery_shipmentamount' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchquery_shipmentamount' );
        $officeexpense = 0;
        $totalpayable = $patchpayablecost+$patchshipmentcost+$officeexpense;
        $payable = array(
            'patchpayablecost' 		=> $patchpayablecost,
            'patchshipmentcost' 	=> $patchshipmentcost,
            'officeexpense' 		=> $officeexpense,
            'totalpayable' 		    => $totalpayable,
        );
        $userpicturepath = URL::to( '/' ).'/public/user_picture/';
        $brandlogopath = URL::to( '/' ).'/public/brand_logo/';
        return response()->json( [ 'topdata' => $topdata, 'payable' => $payable, 'ppcverview' => $ppcverview, 'digitalbrandachieved' => $digitalbrandachieved, 'patchbrandachieved' => $patchbrandachieved, 'upcommingpayments' => $getupcommingpayments, 'sumpayments' => $sumupcommingpayments, 'pendingtask' => $pendingtask, 'userpicturepath' => $userpicturepath, 'brandlogopath' => $brandlogopath, 'message' => 'Admin Dashboard' ], 200 );
    }

    public function adminbranddetails( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'yearmonth'	=> 'required',
            'brand_id'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $yearmonth = explode( '-', $request->yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $branddetails = DB::table( 'brand' )
        ->select( '*' )
        ->where( 'brand_id', '=', $request->brand_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        if ( $branddetails->brandtype_id == 2 ) {
            $data = $this->patchbranddetails( $request->yearmonth, $request->brand_id );
            return response()->json( $data );
        } else {
            $branddetails->brand_currency = $branddetails->brand_currency == 1 ? "$" : ' £';
            $getyearandmonth = explode( '-', $setyearmonth );
            $getfirstdate = $setyearmonth.'-01';
            if ( $yearmonth[ 1 ] == '1' || $yearmonth[ 1 ] == '3' || $yearmonth[ 1 ] == '5' || $yearmonth[ 1 ] == '7' || $yearmonth[ 1 ] == '8' || $yearmonth[ 1 ] == '10' || $yearmonth[ 1 ] == '12' ) {
                $noofdays = 31;
            } elseif ( $yearmonth[ 1 ] == '2' ) {
                $noofdays = 28;
            } else {
                $noofdays = 30;
            }
            $list = array();
            for ( $d = 1; $d <= $noofdays; $d++ ) {
                $time = mktime( 12, 0, 0, $getyearandmonth[ 1 ], $d, $getyearandmonth[ 0 ] );

                if ( date( 'm', $time ) == $getyearandmonth[ 1 ] )
                $list[] = date( 'Y-m-d', $time );
            }
            $getbranduserid = DB::table( 'userbarnd' )
            ->select( 'user_id' )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->get();
            $sortuserbrand = array();
            foreach ( $getbranduserid as $getbranduserids ) {
                $sortuserbrand[] = $getbranduserids->user_id;
            }
            $basictarget = DB::table( 'user' )
            ->select( 'user_target' )
            ->whereIn( 'user_id', $sortuserbrand )
            ->where( 'status_id', '=', 1 )
            ->sum( 'user_target' );
            $targetincrement = DB::table( 'usertarget' )
            ->select( 'usertarget_target' )
            ->whereIn( 'user_id', $sortuserbrand )
            ->where( 'usertarget_month', '<=', $setyearmonth )
            ->where( 'status_id', '=', 1 )
            ->sum( 'usertarget_target' );
            $brandtarget = $basictarget+$targetincrement;
            $ppcassignindollar = DB::table( 'assignppc' )
            ->select( 'assignppc_amount' )
            ->where( 'assignppc_month', '=', $setyearmonth )
            ->whereIn( 'user_id', $sortuserbrand )
            ->where( 'status_id', '=', 1 )
            ->sum( 'assignppc_amount' );
            $ppcspendindollar = DB::table( 'ppc' )
            ->select( 'ppc_amount' )
            ->whereIn( 'ppc_date', $list )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'ppc_amount' );
            $remainingppcindollar = $ppcassignindollar-$ppcspendindollar;
            $gross = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->whereIn( 'orderpayment_date', $list )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'orderpayment_amount' );
            $forwarded = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->whereIn( 'orderpaymentstatus_id', [ 8, 9 ] )
            ->whereIn( 'orderpayment_date', $list )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'orderpayment_amount' );
            $invoice = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'orderpaymentstatus_id', '=', 2 )
            ->whereIn( 'orderpayment_date', $list )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'orderpayment_amount' );
            $paid = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'orderpaymentstatus_id', '=', 3 )
            ->whereIn( 'orderpayment_date', $list )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'orderpayment_amount' );
            $cancel = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'orderpaymentstatus_id', '=', 4 )
            ->whereIn( 'orderpayment_date', $list )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'orderpayment_amount' );
            $refund = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'orderpaymentstatus_id', '=', 5 )
            ->whereIn( 'orderpayment_date', $list )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'orderpayment_amount' );
            $chargeback = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'orderpaymentstatus_id', '=', 6 )
            ->whereIn( 'orderpayment_date', $list )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'orderpayment_amount' );
            $recovery = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'orderpaymentstatus_id', '=', 7 )
            ->whereIn( 'orderpayment_recoverydate', $list )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $request->brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'orderpayment_amount' );
            $netpaid = $paid+$recovery;
            $totalunpaid = $gross-$paid-$cancel-$refund-$chargeback;
            $stats = array(
                'brandtarget' 			=> $brandtarget,
                'ppcassignindollar' 	=> $ppcassignindollar,
                'ppcspendindollar' 		=> $ppcspendindollar,
                'remainingppcindollar' 	=> $remainingppcindollar,
                'gross' 				=> $gross,
                'forwarded' 			=> $forwarded,
                'invoice' 				=> $invoice,
                'paid' 					=> $paid,
                'recovery' 				=> $recovery,
                'netpaid' 				=> $netpaid,
                'cancel' 				=> $cancel,
                'refund' 				=> $refund,
                'chargeback' 			=> $chargeback,
                'totalunpaid'		 	=> $totalunpaid,
            );
            $getuser = DB::table( 'user' )
            ->select( 'user_id', 'user_name', 'user_picture', 'user_target' )
            ->whereIn( 'user_id', $sortuserbrand )
            ->whereIn( 'role_id', [ 6, 7 ] )
            ->where( 'status_id', '=', 1 )
            ->get();
            $topagent = array();
            $topindex = 0;
            $topthree = 0;
            foreach ( $getuser as $getusers ) {
                $getachieve = DB::table( 'orderpayment' )
                ->select( 'orderpayment_amount' )
                ->where( 'status_id', '=', 1 )
                ->where( 'orderpayment_date', 'like', $setyearmonth.'%' )
                ->where( 'created_by', '=', $getusers->user_id )
                ->sum( 'orderpayment_amount' );
                if ( $getachieve != 0 && $topthree <= 2 ) {
                    $getusers->achieve = $getachieve;
                    $topagent[ $topindex ] = $getusers;

                    $topthree++;
                }
                $topindex++;
            }
            $topagent = array_sort( $topagent, 'achieve', SORT_DESC );
            $sorttopagent = array();
            $sortindex = 0;
            foreach ( $topagent as $topagents ) {
                $sorttopagent[ $sortindex ] = $topagents;
                $sortindex++;
            }
            $agenttarget = array();
            $target = 0;
            foreach ( $getuser as $getusers ) {
                $getachieve = DB::table( 'orderpayment' )
                ->select( 'orderpayment_amount' )
                ->where( 'status_id', '=', 1 )
                ->where( 'brand_id', '=', $request->brand_id )
                ->where( 'orderpayment_date', 'like', $setyearmonth.'%' )
                ->where( 'created_by', '=', $getusers->user_id )
                ->sum( 'orderpayment_amount' );
                $getpaid = DB::table( 'orderpayment' )
                ->select( 'orderpayment_amount' )
                ->where( 'status_id', '=', 1 )
                ->where( 'brand_id', '=', $request->brand_id )
                ->where( 'orderpaymentstatus_id', '=', 3 )
                ->where( 'orderpayment_date', 'like', $setyearmonth.'%' )
                ->where( 'created_by', '=', $getusers->user_id )
                ->sum( 'orderpayment_amount' );
                $getrecovery = DB::table( 'orderpayment' )
                ->select( 'orderpayment_amount' )
                ->where( 'status_id', '=', 1 )
                ->where( 'brand_id', '=', $request->brand_id )
                ->where( 'orderpayment_recoverydate', 'like', $setyearmonth.'%' )
                ->where( 'created_by', '=', $getusers->user_id )
                ->sum( 'orderpayment_amount' );
                $netachieve = $getpaid+$getrecovery;
                $getcancel = DB::table( 'orderpayment' )
                ->select( 'orderpayment_amount' )
                ->where( 'status_id', '=', 1 )
                ->where( 'brand_id', '=', $request->brand_id )
                ->where( 'orderpaymentstatus_id', '=', 4 )
                ->where( 'orderpayment_date', 'like', $setyearmonth.'%' )
                ->where( 'created_by', '=', $getusers->user_id )
                ->sum( 'orderpayment_amount' );
                $getusers->achieve = $getachieve;
                $getusers->paid = $getpaid;
                $getusers->recovery = $getrecovery;
                $getusers->netachieve = $netachieve;
                $getusers->cancel = $getcancel;
                $agenttarget[ $target ] = $getusers;

                $target++;
            }
            $payments = DB::table( 'orderpaymentdetails' )
            ->select( 'order_title', 'orderpayment_title', 'orderpayment_amount', 'user_name', 'user_picture' )
            ->where( 'orderpaymentstatus_id', '!=', 3 )
            ->where( 'status_id', '=', 1 )
            ->whereIn( 'created_by', $sortuserbrand )
            ->whereIn( 'orderpayment_duedate', $list )
            ->get();
            $sumpayments = DB::table( 'orderpaymentdetails' )
            ->select( 'orderpayment_amount' )
            ->where( 'orderpaymentstatus_id', '!=', 3 )
            ->where( 'status_id', '=', 1 )
            ->whereIn( 'created_by', $sortuserbrand )
            ->whereIn( 'orderpayment_duedate', $list )
            ->sum( 'orderpayment_amount' );
            $userpicturepath = URL::to( '/' ).'/public/user_picture/';
            $brandlogopath = URL::to( '/' ).'/public/brand_logo/';
            return response()->json( [ 'branddetails' => $branddetails, 'stats' => $stats, 'topagent' => $sorttopagent, 'agenttarget' => $agenttarget, 'upcommingpayments' => $payments, 'sumpayments' => $sumpayments, 'userpicturepath' => $userpicturepath, 'brandlogopath' => $brandlogopath, 'message' => 'Admin Brand Dashboard' ], 200 );
        }
    }

    public function portaladmindashboard( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'yearmonth'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $yearmonth = explode( '-', $request->yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $getyearandmonth = explode( '-', $setyearmonth );
        $getfirstdate = $setyearmonth.'-01';
        $depart = DB::connection( 'mysql2' )->table( 'hrm_department' )
        ->select( '*' )
        ->where( 'status_id', '=', 2 )
        ->get();
        $departsalary = array();
        $dindex = 0;
        foreach ( $depart as $departs ) {
            $departemployees = DB::connection( 'mysql2' )->table( 'elsemployees' )
            ->where( 'elsemployees_departid', '=', $departs->dept_id )
            ->where( 'elsemployees_dofjoining', '<', $getfirstdate )
            ->where( 'elsemployees_status', '=', 2 )
            ->select( 'elsemployees_batchid' )
            ->get();
            $employeesid = array();
            foreach ( $departemployees as $departemployeess ) {
                $employeesid[] = $departemployeess->elsemployees_batchid;
            }
            $getsalary = DB::connection( 'mysql2' )->table( 'payrollsalaries' )
            ->whereIn( 'EMP_BADGE_ID', $employeesid )
            ->select( 'Salary' )
            ->sum( 'Salary' );
            $getincrement = DB::connection( 'mysql2' )->table( 'increment' )
            ->where( 'increment_year', '<=', $getyearandmonth[ 0 ] )
            // ->where( 'increment_month', '<=', $getyearandmonth[ 1 ] )
            ->whereIn( 'elsemployees_batchid', $employeesid )
            ->where( 'status_id', '=', 2 )
            ->select( 'increment_amount' )
            ->sum( 'increment_amount' );
            $grosssalary = $getsalary+$getincrement;
            $departsalary[ $dindex ][ 'name' ] = $departs->dept_name;
            $departsalary[ $dindex ][ 'grosssalary' ] = $grosssalary;
            $dindex++;
        }

        $grosssalary = DB::connection( 'mysql2' )->table( 'netsalary' )
        ->where( 'netsalary_month', '=', $request->yearmonth )
        ->where( 'status_id', '=', 1 )
        ->select( 'netsalary_amount' )
        ->sum( 'netsalary_amount' );
        $salaryexpense = array(
            'grosssalary' 				=> $grosssalary,
            'netsalary' 				=> $grosssalary,
        );
        $getcar = DB::connection( 'mysql2' )->table( 'car' )
        ->select( 'car_id', 'car_name', 'car_rent' )
        // ->where( 'created_at', 'like', $setyearmonth.'%' )
        // ->where( 'status_id', '=', 2 )
        ->get();
        $carexpense = array();
        $carindex = 0;
        foreach ( $getcar as $getcars ) {
            $getcarrentaddition = DB::connection( 'mysql2' )->table( 'caraddition' )
            ->select( 'caraddition_rent' )
            ->where( 'caraddition_date', '>=', $setyearmonth )
            ->where( 'status_id', '=', 1 )
            ->where( 'car_id', '=', $getcars->car_id )
            ->sum( 'caraddition_rent' );
            $getcars->car_rent = $getcars->car_rent+$getcarrentaddition;
            $getcarassign = DB::connection( 'mysql2' )->table( 'carassigndetails' )
            ->select( 'elsemployees_name' )
            ->where( 'carassign_month', '=', $setyearmonth )
            ->where( 'status_id', '=', 1 )
            ->where( 'car_id', '=', $getcars->car_id )
            ->first();
            if ( isset( $getcarassign->elsemployees_name ) ) {
                $getcars->assignto = $getcarassign->elsemployees_name;

            } else {
                $getcars->assignto = 'Not Assigned';
            }
            $carexpense[ $carindex ] = $getcars;
            $carindex++;
        }
        $sumbasiccarrent = DB::connection( 'mysql2' )->table( 'car' )
        ->select( 'car_rent' )
        // ->where( 'created_at', 'like', $setyearmonth.'%' )
        ->where( 'status_id', '=', 2 )
        ->sum( 'car_rent' );
        $sumcarrentaddition = DB::connection( 'mysql2' )->table( 'caraddition' )
        ->select( 'caraddition_rent' )
        ->where( 'caraddition_date', '>=', $setyearmonth )
        ->where( 'status_id', '=', 1 )
        ->sum( 'caraddition_rent' );
        $sumcarrent = $sumbasiccarrent+$sumcarrentaddition;
        $fixexpense = DB::connection( 'mysql2' )->table( 'expense' )
        ->select( 'expense_id', 'expense_title', 'expense_amount' )
        ->where( 'expense_yearandmonth', '=', $setyearmonth )
        ->where( 'expense_isrecuring', '=', 1 )
        ->where( 'expensetype_id', '=', 2 )
        ->where( 'status_id', '=', 2 )
        ->get();
        $vanexpense = DB::connection( 'mysql2' )->table( 'expense' )
        ->select( 'expense_id', 'expense_title', 'expense_amount' )
        ->where( 'expense_yearandmonth', '=', $setyearmonth )
        ->where( 'expense_isrecuring', '=', 0 )
        ->where( 'expensetype_id', '=', 4 )
        ->where( 'status_id', '=', 2 )
        ->get();
        $otherexpense = DB::connection( 'mysql2' )->table( 'expense' )
        ->select( 'expense_id', 'expense_title', 'expense_amount' )
        ->where( 'expense_yearandmonth', '=', $setyearmonth )
        ->where( 'expense_isrecuring', '=', 0 )
        ->where( 'expensetype_id', '=', 5 )
        ->where( 'status_id', '=', 2 )
        ->get();
        $sumfixexpense = DB::connection( 'mysql2' )->table( 'expense' )
        ->select( 'expense_amount' )
        ->where( 'expense_yearandmonth', '=', $setyearmonth )
        ->where( 'expense_isrecuring', '=', 1 )
        ->where( 'expensetype_id', '=', 4 )
        ->where( 'status_id', '=', 2 )
        ->sum( 'expense_amount' );
        $sumvanexpense = DB::connection( 'mysql2' )->table( 'expense' )
        ->select( 'expense_amount' )
        ->where( 'expense_yearandmonth', '=', $setyearmonth )
        ->where( 'expense_isrecuring', '=', 0 )
        ->where( 'expensetype_id', '=', 4 )
        ->where( 'status_id', '=', 2 )
        ->sum( 'expense_amount' );
        $sumotherexpense = DB::connection( 'mysql2' )->table( 'expense' )
        ->select( 'expense_amount' )
        ->where( 'expense_yearandmonth', '=', $setyearmonth )
        ->where( 'expense_isrecuring', '=', 0 )
        ->where( 'expensetype_id', '!=', 4 )
        ->where( 'status_id', '=', 2 )
        ->sum( 'expense_amount' );
        $grandtotal = $sumcarrent+$sumfixexpense+$sumvanexpense+$sumotherexpense+$grosssalary;
        $sumallexpense = array(
            'sumcarrent' 		=> $sumcarrent,
            'sumfixexpense' 	=> $sumfixexpense,
            'sumvanexpense' 	=> $sumvanexpense,
            'sumotherexpense' 	=> $sumotherexpense,
            'netsalary' 		=> $grosssalary,
            'grandtotal' 		=> $grandtotal,
        );
        $userpicturepath = URL::to( '/' ).'/public/user_picture/';
        $brandlogopath = URL::to( '/' ).'/public/brand_logo/';
        return response()->json( [ 'departsalary' => $departsalary, 'salaryexpense' => $salaryexpense, 'carexpense' => $carexpense, 'vanexpense' => $vanexpense, 'fixexpense' => $fixexpense, 'otherexpense' => $otherexpense, 'sumallexpense' => $sumallexpense, 'message' => 'Admin Dashboard' ], 200 );
    }

    public function billingmerchantdashboard( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'yearmonth'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $yearmonth = explode( '-', $request->yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $merchatdetail = DB::table( 'billingmerchant' )
        ->select( '*' )
        ->where( 'status_id', '=', 1 )
        ->groupBy( 'billingmerchant_email' )
        ->get();
        if ( isset( $merchatdetail ) ) {
            $sortbillingmerchant = array();
            $merchantnetamount = array();
            $firstdatewithzero = $setyearmonth.'-01';
            $firstdate = $setyearmonth.'-01';
            $stats = array();
            $index = 0;
            foreach ( $merchatdetail as $merchatdetails ) {
                $merchantids = DB::table( 'billingmerchant' )
                ->select( 'billingmerchant_id' )
                ->where( 'billingmerchant_email', '=', $merchatdetails->billingmerchant_email )
                ->get();
                $sortmerchantids = array();
                foreach ( $merchantids as $merchantidss ) {
                    $sortmerchantids[] = $merchantidss->billingmerchant_id;
                }
                $firstbalance = $merchatdetails->billingmerchant_openingbalance;
                $previouspaidbalance = DB::table( 'orderpayment' )
                ->select( 'orderpayment_amount' )
                ->where( 'orderpayment_date', '<', $firstdatewithzero )
                ->where( 'orderpaymentstatus_id', '=', 3 )
                ->whereIn( 'merchant_id', $sortmerchantids )
                ->where( 'status_id', '=', 1 )
                ->sum( 'orderpayment_amount' );
                $previousfeededuction =  $merchatdetails->billingmerchant_fee / 100 * $previouspaidbalance;
                $previoustotalwithdrawl = DB::table( 'withdrawal' )
                ->select( 'withdrawal_amount' )
                ->where( 'withdrawal_month', '<', $request->yearmonth )
                ->whereIn( 'billingmerchant_id', $sortmerchantids )
                ->where( 'status_id', '=', 1 )
                ->sum( 'withdrawal_amount' );
                $previousnetbalance = $previouspaidbalance+$firstbalance-$previoustotalwithdrawl-$previousfeededuction;
                $openingbalance = $previousnetbalance;
                $paidbalance = DB::table( 'orderpayment' )
                ->select( 'orderpayment_amount' )
                ->where( 'orderpayment_date', 'like', $setyearmonth.'%' )
                ->where( 'orderpaymentstatus_id', '=', 3 )
                ->whereIn( 'merchant_id', $sortmerchantids )
                ->where( 'status_id', '=', 1 )
                ->sum( 'orderpayment_amount' );
                $grosstotalbalance = $openingbalance+$paidbalance;
                $totalwithdrawl = DB::table( 'withdrawal' )
                ->select( 'withdrawal_amount' )
                ->where( 'withdrawaltype_id', '=', $request->withdrawal_month )
                ->whereIn( 'billingmerchant_id', $sortmerchantids )
                ->where( 'status_id', '=', 1 )
                ->sum( 'withdrawal_amount' );
                $feededuction =  $merchatdetails->billingmerchant_fee / 100 * $paidbalance;
                $netbalance = $grosstotalbalance-$totalwithdrawl-$feededuction;

                $merchatdetails->billingmerchant_openingbalance 	 = $openingbalance;
                $merchatdetails->paidbalance 		 = $paidbalance;
                $merchatdetails->feededuction 		 = $feededuction;
                $merchatdetails->totalwithdrawl 	 = $totalwithdrawl;
                $merchatdetails->grosstotalbalance  = $grosstotalbalance;
                $merchatdetails->netbalance  		 = $netbalance;
                $merchantnetamount[ $index ] 			 = $netbalance;
                $stats[ $index ] 		                = $merchatdetails;
                $index++;
            }
            $billingmerchanttitle = DB::table( 'billingmerchant' )
            ->select( 'billingmerchant_title' )
            ->where( 'status_id', '=', 1 )
            ->groupBy( 'billingmerchant_email' )
            ->get();
            $merchanttitle = array();
            foreach ( $billingmerchanttitle as $billingmerchanttitles ) {
                $merchanttitle[] =  $billingmerchanttitles->billingmerchant_title;
            }
            $logopath = URL::to( '/' ).'/public/billingmerchantlogo/';
            return response()->json( [ 'sortbillingmerchant' => $stats, 'merchanttitle' => $merchanttitle, 'merchantnetamount' => $merchantnetamount, 'logopath' => $logopath,  'message' => 'Billing Merchant Details' ], 200 );
        } else {
            $emptyarray = array();
            $logopath = '';
            return response()->json( [ 'sortbillingmerchant' => $emptyarray, 'logopath' => $logopath,  'message' => 'Billing Merchant Details' ], 200 );
        }
    }

    public function workerdashboard( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'yearmonth'	=> 'required',
            'brand_id'	=> 'required',
            'id'		=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $yearmonth = explode( '-', $request->yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $getyearandmonth = explode( '-', $setyearmonth );
        $commission = 0;
        $getuser = DB::table( 'user' )
        ->select( '*' )
        ->where( 'user_id', '=', $request->id )
        ->where( 'status_id', '=', 1 )
        ->first();
        $list = array();
        if ( $yearmonth[ 1 ] == '1' || $yearmonth[ 1 ] == '3' || $yearmonth[ 1 ] == '5' || $yearmonth[ 1 ] == '7' || $yearmonth[ 1 ] == '8' || $yearmonth[ 1 ] == '10' || $yearmonth[ 1 ] == '12' ) {
            $noofdays = 31;
        } elseif ( $yearmonth[ 1 ] == '2' ) {
            $noofdays = 28;
        } else {
            $noofdays = 30;
        }
        for ( $d = 1; $d <= $noofdays; $d++ ) {
            $time = mktime( 12, 0, 0, $getyearandmonth[ 1 ], $d, $getyearandmonth[ 0 ] );

            if ( date( 'm', $time ) == $getyearandmonth[ 1 ] )
            $list[] = date( 'Y-m-d', $time );
        }
        $taskindex = 0;
        $monthlydata = array();
        foreach ( $list as $lists ) {
            $taskcount = DB::table( 'task' )
            ->select( 'task_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'task_workby', '=', $request->id )
            ->where( 'task_date', '=', $lists )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'task_id' );
            $monthlydata[ $taskindex ] = $taskcount;
            $taskindex++;
        }
        $monthlytotalorders = DB::table( 'task' )
        ->select( 'task_id' )
        ->where( 'brand_id', '=', $request->brand_id )
        ->where( 'status_id', '=', 1 )
        ->where( 'task_workby', '=', $request->id )
        ->where( 'task_date', 'like', $setyearmonth.'%' )
        ->count( 'task_id' );
        $monthlycompleteorders = DB::table( 'task' )
        ->select( 'task_id' )
        ->where( 'brand_id', '=', $request->brand_id )
        ->where( 'status_id', '=', 1 )
        ->where( 'taskstatus_id', '>=', 3 )
        ->where( 'task_workby', '=', $request->id )
        ->where( 'task_date', 'like', $setyearmonth.'%' )
        ->count( 'task_id' );
        $monthlyremainingorders = $monthlytotalorders-$monthlycompleteorders;
        if ( $request->role_id == 15 ) {
            $completeorders = DB::table( 'task' )
            ->select( 'task_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'taskstatus_id', '>=', 3 )
            ->where( 'task_workby', '=', $request->id )
            ->where( 'task_date', 'like', $setyearmonth.'%' )
            ->count( 'task_id' );
            $getcommission = DB::table( 'commission' )
            ->select( '*' )
            ->where( 'brandtype_id', '=', 1 )
            ->where( 'status_id', '=', 1 )
            ->where( 'user_id', '=', $request->id )
            ->orderBy( 'commission_id', 'DESC' )
            ->get();
            $commissionindex = 0;
            foreach ( $getcommission as $getcommissions ) {
                if ( $completeorders >= $getcommissions->commission_from && $completeorders >= $getcommissions->commission_to && $commissionindex == 0 ) {
                    $commission = $getcommissions->commission_rate;
                    $commissionindex++;
                    break;
                } else {
                    $commission = 0;
                }
            }
        } else {
            $completeorders = DB::table( 'task' )
            ->select( 'task_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'taskstatus_id', '>=', 3 )
            ->where( 'task_workby', '=', $request->id )
            ->where( 'task_date', 'like', $setyearmonth.'%' )
            ->count( 'task_id' );
            $getcommission = DB::table( 'commission' )
            ->select( '*' )
            ->where( 'brandtype_id', '=', 1 )
            ->where( 'status_id', '=', 1 )
            ->where( 'user_id', '=', $request->id )
            ->orderBy( 'commission_id', 'DESC' )
            ->get();
            $commissionindex = 0;
            foreach ( $getcommission as $getcommissions ) {
                if ( $completeorders >= $getcommissions->commission_from && $completeorders >= $getcommissions->commission_to && $commissionindex == 0 ) {
                    $commission = $getcommissions->commission_rate;
                    $commissionindex++;
                    break;
                } else {
                    $commission = 0;
                }
            }
        }
        $ordercounts = array();
        $ordercounts[ 'totalorder' ] = $monthlytotalorders;
        $ordercounts[ 'completeorder' ] = $monthlycompleteorders;
        $ordercounts[ 'pendingorder' ] = $monthlyremainingorders;
        $ordercounts[ 'commission' ] = $commission;
        $userpicturepath = URL::to( '/' ).'/public/user_picture/';
        $logopath = URL::to( '/' ).'/public/brand_logo/';
        $branddetail = DB::table( 'branddetail' )
        ->select( '*' )
        ->where( 'brand_id', '=', $request->brand_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        if ( isset( $getuser ) ) {
            return response()->json( [ 'userdata' => $getuser, 'monthlydata' => $monthlydata, 'orderscount' => $ordercounts, 'userpicturepath' => $userpicturepath, 'branddetail' => $branddetail, 'logopath' => $logopath, 'message' => 'Worker Dashboard Details' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function salesdashboard( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'yearmonth'	=> 'required',
            'brand_id'	=> 'required',
            'id'		=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $branddetail = DB::table( 'brand' )
        ->select( '*' )
        ->where( 'brand_id', '=', $request->brand_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        $branddetail->brand_currency = $branddetail->brand_currency == 1 ? "$" : ' £';
        if ( $branddetail->brandtype_id == 2 ) {
            $data = $this->salespatchdashboard( $request->yearmonth, $request->brand_id, $request->id, $request->role_id );
            return response()->json( $data );
        } else {
            $yearmonth = explode( '-', $request->yearmonth );
            if ( $yearmonth[ 1 ] <= 9 ) {
                $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
            } else {
                $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
            }
            $getyearandmonth = explode( '-', $setyearmonth );
            $getuser = DB::table( 'user' )
            ->select( '*' )
            ->where( 'user_id', '=', $request->id )
            ->where( 'status_id', '=', 1 )
            ->first();
            $list = array();
            if ( $yearmonth[ 1 ] == '1' || $yearmonth[ 1 ] == '3' || $yearmonth[ 1 ] == '5' || $yearmonth[ 1 ] == '7' || $yearmonth[ 1 ] == '8' || $yearmonth[ 1 ] == '10' || $yearmonth[ 1 ] == '12' ) {
                $noofdays = 31;
            } elseif ( $yearmonth[ 1 ] == '2' ) {
                $noofdays = 28;
            } else {
                $noofdays = 30;
            }
            $from = $setyearmonth.'-01';
            $to = $setyearmonth.'-'.$noofdays;
            $year = $getyearandmonth[ 0 ];
            $month = $getyearandmonth[ 1 ];
            for ( $d = 1; $d <= $noofdays; $d++ ) {
                $time = mktime( 12, 0, 0, $month, $d, $year );

                if ( date( 'm', $time ) == $month )
                $list[] = date( 'Y-m-d', $time );
            }

            function countDays( $year, $month, $ignore ) {
                $count = 0;
                $counter = mktime( 0, 0, 0, $month, 1, $year );
                while ( date( 'n', $counter ) == $month ) {
                    if ( in_array( date( 'w', $counter ), $ignore ) == false ) {
                        $count++;
                    }
                    $counter = strtotime( '+1 day', $counter );
                }
                return $count;
            }
            $workingdays = countDays( 2013, 1, array( 0, 6 ) );
            $graphdatacount = array();
            $indexcount = 0;
            foreach ( $list as $lists ) {
                $orderscount = DB::table( 'order' )
                ->select( 'order_id' )
                ->where( 'status_id', '=', 1 )
                ->where( 'created_by', '=', $request->id )
                ->where( 'order_date', '=', $lists )
                ->where( 'brand_id', '=', $request->brand_id )
                ->count( 'order_id' );
                $graphdatacount[ $indexcount ] = $orderscount;
                $indexcount++;
            }
            $graphdataamount = array();
            $indexzmount = 0;
            foreach ( $list as $lists ) {
                $orderamount = DB::table( 'orderpayment' )
                ->select( 'orderpayment_amount' )
                ->where( 'status_id', '=', 1 )
                ->where( 'created_by', '=', $request->id )
                ->where( 'orderpayment_date', '=', $lists )
                ->where( 'orderpaymentstatus_id', '!=', 1 )
                ->where( 'brand_id', '=', $request->brand_id )
                ->sum( 'orderpayment_amount' );
                $graphdataamount[ $indexzmount ] = $orderamount;
                $indexzmount++;
            }
            $graphdatadate = array();
            $indexdate = 0;
            foreach ( $list as $lists ) {
                $graphdatadate[ $indexdate ] = $lists;
                $indexdate++;
            }
            $target = array();
            $targetachieved = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'orderpaymentstatus_id', '!=', 1 )
            ->whereBetween( 'orderpayment_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->sum( 'orderpayment_amount' );

            $targetpaid = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'orderpaymentstatus_id', '=', 3 )
            ->whereBetween( 'orderpayment_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->sum( 'orderpayment_amount' );

            $recoverypaid = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'orderpaymentstatus_id', '=', 7 )
            ->whereBetween( 'orderpayment_recoverydate', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->sum( 'orderpayment_amount' );
            $targetcancel = DB::table( 'orderpayment' )
            ->select( 'orderpayment_amount' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'orderpaymentstatus_id', '=', 4 )
            ->whereBetween( 'orderpayment_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->sum( 'orderpayment_amount' );
            $targetincrement = DB::table( 'usertarget' )
            ->select( 'usertarget_target' )
            ->where( 'user_id', '=', $request->id )
            ->where( 'usertarget_month', '<=', $setyearmonth )
            ->where( 'status_id', '=', 1 )
            ->sum( 'usertarget_target' );
            $usertarget = $getuser->user_target+$targetincrement;
            $unpaidamount = $targetachieved-$targetpaid-$targetcancel;
            $counttotalorders = DB::table( 'order' )
            ->select( 'order_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'orderstatus_id', '!=', 1 )
            ->whereBetween( 'order_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'order_id' );
            $countcompleteorders = DB::table( 'order' )
            ->select( 'order_id' )
            ->where( 'status_id', '=', 1 )
            ->whereNotIn( 'orderstatus_id', [ 5, 8, 9, 10 ] )
            ->where( 'created_by', '=', $request->id )
            ->whereBetween( 'order_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'order_id' );
            $countapproveorders = DB::table( 'order' )
            ->select( 'order_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'orderstatus_id', '=', 11 )
            ->where( 'created_by', '=', $request->id )
            ->whereBetween( 'order_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'order_id' );
            $countcancel = DB::table( 'order' )
            ->select( 'order_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'orderstatus_id', '=', 6 )
            ->whereBetween( 'order_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'order_id' );
            $countpaid = DB::table( 'orderpayment' )
            ->select( 'orderpayment_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'orderpaymentstatus_id', '=', 3 )
            ->whereBetween( 'orderpayment_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'orderpayment_id' );

            $countrecovery = DB::table( 'orderpayment' )
            ->select( 'orderpayment_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'orderpaymentstatus_id', '=', 7 )
            ->whereBetween( 'orderpayment_recoverydate', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'orderpayment_id' );
            $countpendingorders = $countcompleteorders-$countapproveorders-$countcancel;
            $target[ 'user_target' ] = $usertarget;
            $target[ 'achieved' ] = $targetachieved;
            $target[ 'paid' ] = $targetpaid;
            $target[ 'recovery' ] = $recoverypaid;
            $target[ 'unpaidamount' ] = $unpaidamount;
            $target[ 'remaining' ] = $usertarget - $targetachieved;
            $target[ 'perday' ] = $usertarget / $workingdays;
            $target[ 'cancel' ] = $targetcancel;
            $target[ 'counttotalorders' ] = $counttotalorders;
            $target[ 'countcompleteorders' ] = $countcompleteorders;
            $target[ 'countapproveorders' ] = $countapproveorders;
            $target[ 'countcancel' ] = $countcancel;
            $target[ 'countpaid' ] = $countpaid;
            $target[ 'countrecovery' ] = $countrecovery;
            $userpicturepath = URL::to( '/' ).'/public/user_picture/';
            $logopath = URL::to( '/' ).'/public/brand_logo/';
            if ( isset( $getuser ) ) {
                return response()->json( [ 'userdata' => $getuser, 'target' => $target, 'userpicturepath' => $userpicturepath, 'branddetail' => $branddetail, 'logopath' => $logopath, 'graphdatacount' => $graphdatacount, 'graphdataamount' => $graphdataamount, 'graphdatadate' => $graphdatadate, 'message' => 'Sales Dashboard Details' ], 200 );
            } else {
                return response()->json( 'Oops! Something Went Wrong', 400 );
            }
        }
    }

    public function leadashboard( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'yearmonth'	=> 'required',
            'brand_id'	=> 'required',
            'id'		=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $branddetail = DB::table( 'branddetail' )
        ->select( '*' )
        ->where( 'brand_id', '=', $request->brand_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        if ( $branddetail->brandtype_id == 2 ) {
            $data = $this->salespatchdashboard( $request->yearmonth, $request->brand_id, $request->id, $request->role_id );
            return response()->json( $data );
        } else {
            $yearmonth = explode( '-', $request->yearmonth );
            if ( $yearmonth[ 1 ] <= 9 ) {
                $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
            } else {
                $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
            }
            $getyearandmonth = explode( '-', $setyearmonth );
            $getuser = DB::table( 'user' )
            ->select( '*' )
            ->where( 'user_id', '=', $request->id )
            ->where( 'status_id', '=', 1 )
            ->first();
            $list = array();
            if ( $yearmonth[ 1 ] == '1' || $yearmonth[ 1 ] == '3' || $yearmonth[ 1 ] == '5' || $yearmonth[ 1 ] == '7' || $yearmonth[ 1 ] == '8' || $yearmonth[ 1 ] == '10' || $yearmonth[ 1 ] == '12' ) {
                $noofdays = 31;
            } elseif ( $yearmonth[ 1 ] == '2' ) {
                $noofdays = 28;
            } else {
                $noofdays = 30;
            }
            $from = $setyearmonth.'-01';
            $to = $setyearmonth.'-'.$noofdays;
            for ( $d = 1; $d <= $noofdays; $d++ ) {
                $time = mktime( 12, 0, 0, $getyearandmonth[ 1 ], $d, $getyearandmonth[ 0 ] );

                if ( date( 'm', $time ) == $getyearandmonth[ 1 ] )
                $list[] = date( 'Y-m-d', $time );
            }

            function countDays( $year, $month, $ignore ) {
                $count = 0;
                $counter = mktime( 0, 0, 0, $month, 1, $year );
                while ( date( 'n', $counter ) == $month ) {
                    if ( in_array( date( 'w', $counter ), $ignore ) == false ) {
                        $count++;
                    }
                    $counter = strtotime( '+1 day', $counter );
                }
                return $count;
            }
            $getmonthlysavelead = DB::table( 'freshlead' )
            ->select( 'freshlead_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->whereBetween( 'freshlead_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'freshlead_id' );
            $getmonthlytotal = DB::table( 'leadgenerate' )
            ->select( 'leadgenerate_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->whereBetween( 'leadgenerate_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'lead_id' );
            $getmonthlyclient = DB::table( 'leadgenerate' )
            ->select( 'leadgenerate_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'leadstatus_id', '=', 3 )
            ->whereBetween( 'leadgenerate_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'lead_id' );
            $getmonthlycancel = DB::table( 'leadgenerate' )
            ->select( 'leadgenerate_id' )
            ->where( 'status_id', '=', 1 )
            ->where( 'created_by', '=', $request->id )
            ->where( 'leadstatus_id', '=', 4 )
            ->whereBetween( 'leadgenerate_date', [ $from, $to ] )
            ->where( 'brand_id', '=', $request->brand_id )
            ->count( 'lead_id' );
            $monthlyordercount = array();
            $monthlyordercount[ 'savelead' ] = $getmonthlysavelead;
            $monthlyordercount[ 'total' ] = $getmonthlytotal;
            $monthlyordercount[ 'client' ] = $getmonthlyclient;
            $monthlyordercount[ 'cancel' ] = $getmonthlycancel;
            $userpicturepath = URL::to( '/' ).'/public/user_picture/';
            $logopath = URL::to( '/' ).'/public/brand_logo/';
        }
        if ( isset( $getuser ) ) {
            return response()->json( [ 'userdata' => $getuser, 'orderscount' => $monthlyordercount, 'branddetail' => $branddetail, 'userpicturepath' => $userpicturepath, 'logopath' => $logopath, 'message' => 'Lead Dashboard Details' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function adminpatchdashboard( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'yearmonth'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $yearmonth = explode( '-', $request->yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $getyearandmonth = explode( '-', $setyearmonth );
        $getfirstdate = $setyearmonth.'-01';
        if ( $yearmonth[ 1 ] == '1' || $yearmonth[ 1 ] == '3' || $yearmonth[ 1 ] == '5' || $yearmonth[ 1 ] == '7' || $yearmonth[ 1 ] == '8' || $yearmonth[ 1 ] == '10' || $yearmonth[ 1 ] == '12' ) {
            $noofdays = 31;
        } elseif ( $yearmonth[ 1 ] == '2' ) {
            $noofdays = 28;
        } else {
            $noofdays = 30;
        }
        $list = array();
        for ( $d = 1; $d <= $noofdays; $d++ ) {
            $time = mktime( 12, 0, 0, $getyearandmonth[ 1 ], $d, $getyearandmonth[ 0 ] );

            if ( date( 'm', $time ) == $getyearandmonth[ 1 ] )
            $list[] = date( 'Y-m-d', $time );
        }
        $totalbrand = DB::table( 'userbarnd' )
        ->select( 'brand_id' )
        ->where( 'user_id', '=', $request->user_id )
        ->where( 'status_id', '=', 1 )
        ->get();
        $brands = array();
        foreach ( $totalbrand as $totalbrands ) {
            $brands[] = $totalbrands->brand_id;
        }
        $totaluser = DB::table( 'userbarnd' )
        ->select( 'user_id' )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->get();
        $userids = array();
        foreach ( $totaluser as $totalusers ) {
            $userids[] = $totalusers->user_id;
        }
        $patchbrand = DB::table( 'brand' )
        ->select( 'brand_id' )
        ->whereIn( 'brand_id', $brands )
        ->where( 'brandtype_id', '=', 2 )
        ->where( 'status_id', '=', 1 )
        ->get();
        $sortpatchbrand = array();
        foreach ( $patchbrand as $patchbrands ) {
            $sortpatchbrand[] = $patchbrands->brand_id;
        }
        $patchuserid = DB::table( 'userbarnd' )
        ->select( 'user_id' )
        ->whereIn( 'brand_id', $sortpatchbrand )
        ->whereIn( 'user_id', $userids )
        ->where( 'status_id', '=', 1 )
        ->get();
        $sortpatchuser = array();
        foreach ( $patchuserid as $patchuserids ) {
            $sortpatchuser[] = $patchuserids->user_id;
        }
        $patchusertarget = DB::table( 'user' )
        ->select( 'user_target' )
        ->whereIn( 'user_id', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'user_target' );
        $targetincrement = DB::table( 'usertarget' )
        ->select( 'usertarget_target' )
        ->whereIn( 'user_id', $sortpatchuser )
        ->where( 'usertarget_month', '<=', $setyearmonth )
        ->where( 'status_id', '=', 1 )
        ->sum( 'usertarget_target' );
        $patchtarget = $patchusertarget+$targetincrement;
        $patchgross = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchpaid = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchcancel = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 7 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchunpaid = $patchgross-$patchpaid-$patchcancel;
        $patchbillingoverview = array(
            'patchtarget' 	=> $patchtarget,
            'patchgross' 	=> $patchgross,
            'patchpaid' 	=> $patchpaid,
            'patchcancel' 	=> $patchcancel,
            'patchunpaid' 	=> $patchunpaid,
        );
        $patchgrossquerycount = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchforwardedcount = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 1 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchforwardedamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 1 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchpaidcount = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchpaidamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchonboardcount = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 11 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchonboardamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 11 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchdeliveredcount = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 12 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchdeliveredamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 12 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchreturenedcount = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 15 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchreturenedamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 15 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchorderoverview = array(
            'patchgrossquerycount' 	=> $patchgrossquerycount,
            'patchgrossqueryamount'	=> $patchgross,
            'patchforwardedcount' 	=> $patchforwardedcount,
            'patchforwardedamount' 	=> $patchforwardedamount,
            'patchpaidcount' 		=> $patchpaidcount,
            'patchpaidamount' 		=> $patchpaidamount,
            'patchonboardcount' 	=> $patchonboardcount,
            'patchonboardamount' 	=> $patchonboardamount,
            'patchdeliveredcount' 	=> $patchdeliveredcount,
            'patchdeliveredamount' 	=> $patchdeliveredamount,
            'patchreturenedcount' 	=> $patchreturenedcount,
            'patchreturenedamount' 	=> $patchreturenedamount,
        );
        $patchpickedquantity = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 9 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchpickedamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 9 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchvendorquantity = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 2 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchvendoramount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 2 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchreturnquantity = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 3 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchreturnamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 3 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchclientquantity = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 5 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchclientamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 5 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchapprovequantity = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 6 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchapproveamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 6 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchrejectquantity = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->where( 'patchquerystatus_id', '=', 7 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchrejectamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquerystatus_id', '=', 7 )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchpaidquantity = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchpaidamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchdeliverquantity = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquerystatus_id', [ 11, 12 ] )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->count();
        $patchdeliveramount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->whereIn( 'patchquerystatus_id', [ 11, 12 ] )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $sortpatchuser )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $patchstatuswisequantity = array( $patchpickedquantity, $patchvendorquantity, $patchreturnquantity, $patchclientquantity, $patchapprovequantity, $patchrejectquantity, $patchpaidquantity, $patchdeliverquantity );
        $patchstatuswiseamount = array( $patchpickedamount, $patchvendoramount, $patchreturnamount, $patchclientamount, $patchapproveamount, $patchrejectamount, $patchpaidamount, $patchdeliveramount );
        return response()->json( [ 'patchbillingoverview' => $patchbillingoverview, 'patchorderoverview' => $patchorderoverview, 'patchstatuswisequantity' => $patchstatuswisequantity, 'patchstatuswiseamount' => $patchstatuswiseamount, 'message' => 'Admin Patch Dashboard' ], 200 );
    }

    public function adminpatchandquerylist( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'yearmonth'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $yearmonth = explode( '-', $request->yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $getyearandmonth = explode( '-', $setyearmonth );
        $getfirstdate = $setyearmonth.'-01';
        if ( $yearmonth[ 1 ] == '1' || $yearmonth[ 1 ] == '3' || $yearmonth[ 1 ] == '5' || $yearmonth[ 1 ] == '7' || $yearmonth[ 1 ] == '8' || $yearmonth[ 1 ] == '10' || $yearmonth[ 1 ] == '12' ) {
            $noofdays = 31;
        } elseif ( $yearmonth[ 1 ] == '2' ) {
            $noofdays = 28;
        } else {
            $noofdays = 30;
        }
        $list = array();
        for ( $d = 1; $d <= $noofdays; $d++ ) {
            $time = mktime( 12, 0, 0, $getyearandmonth[ 1 ], $d, $getyearandmonth[ 0 ] );

            if ( date( 'm', $time ) == $getyearandmonth[ 1 ] )
            $list[] = date( 'Y-m-d', $time );
        }
        $totalbrand = DB::table( 'userbarnd' )
        ->select( 'brand_id' )
        ->where( 'user_id', '=', $request->user_id )
        ->where( 'status_id', '=', 1 )
        ->get();
        $brands = array();
        foreach ( $totalbrand as $totalbrands ) {
            $brands[] = $totalbrands->brand_id;
        }
        $totaluser = DB::table( 'userbarnd' )
        ->select( 'user_id' )
        ->whereIn( 'brand_id', $brands )
        ->where( 'status_id', '=', 1 )
        ->get();
        $userids = array();
        foreach ( $totaluser as $totalusers ) {
            $userids[] = $totalusers->user_id;
        }
        $patchquerylist = DB::table( 'patchquerylist' )
        ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id', 'patchquerystatus_name', 'user_name' )
        ->whereIn( 'patchquery_date', $list )
        ->whereIn( 'created_by', $userids )
        ->where( 'status_id', '=', 1 )
        ->orderBy( 'patchquery_id', 'DESC' )
        ->get();
        return response()->json( [ 'patchquerylist' => $patchquerylist, 'message' => 'Admin Patch Query List' ], 200 );
    }

    public function patchbranddetails( $yearmonth, $brand_id ) {
        $yearmonth = explode( '-', $yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $branddetails = DB::table( 'brand' )
        ->select( '*' )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        $branddetails->brand_currency = $branddetails->brand_currency == 1 ? "$" : ' £';
        $getyearandmonth = explode( '-', $setyearmonth );
        $getfirstdate = $setyearmonth.'-01';
        if ( $yearmonth[ 1 ] == '1' || $yearmonth[ 1 ] == '3' || $yearmonth[ 1 ] == '5' || $yearmonth[ 1 ] == '7' || $yearmonth[ 1 ] == '8' || $yearmonth[ 1 ] == '10' || $yearmonth[ 1 ] == '12' ) {
            $noofdays = 31;
        } elseif ( $yearmonth[ 1 ] == '2' ) {
            $noofdays = 28;
        } else {
            $noofdays = 30;
        }
        $list = array();
        for ( $d = 1; $d <= $noofdays; $d++ ) {
            $time = mktime( 12, 0, 0, $getyearandmonth[ 1 ], $d, $getyearandmonth[ 0 ] );

            if ( date( 'm', $time ) == $getyearandmonth[ 1 ] )
            $list[] = date( 'Y-m-d', $time );
        }
        $getbranduserid = DB::table( 'userbarnd' )
        ->select( 'user_id' )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'status_id', '=', 1 )
        ->get();
        $sortuserbrand = array();
        foreach ( $getbranduserid as $getbranduserids ) {
            $sortuserbrand[] = $getbranduserids->user_id;
        }
        $forwardedtoproduction = DB::table( 'patch' )
        ->select( 'patch_amount' )
        ->whereIn( 'patch_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchstatus_id', '=', 1 )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patch_amount' );
        $returnfromproduction = DB::table( 'patch' )
        ->select( 'patch_amount' )
        ->whereIn( 'patch_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchstatus_id', '=', 2 )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patch_amount' );
        $ondelivery = DB::table( 'patch' )
        ->select( 'patch_amount' )
        ->whereIn( 'patch_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchstatus_id', '=', 3 )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patch_amount' );
        $delivered = DB::table( 'patch' )
        ->select( 'patch_amount' )
        ->whereIn( 'patch_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchstatus_id', '=', 4 )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patch_amount' );
        $orderdata = array(
            'forwardedtoproduction' 	=> $forwardedtoproduction,
            'returnfromproduction' 		=> $returnfromproduction,
            'ondelivery' 				=> $ondelivery,
            'delivered' 				=> $delivered,
        );
        $forwardedtomanager = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchquerystatus_id', '=', 1 )
        ->where( 'status_id', '=', 1 )
        ->count();
        $pickbymanager = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchquerystatus_id', '=', 9 )
        ->where( 'status_id', '=', 1 )
        ->count();
        $forwardedtovendor = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchquerystatus_id', '=', 2 )
        ->where( 'status_id', '=', 1 )
        ->count();
        $returntomanager = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchquerystatus_id', '=', 3 )
        ->where( 'status_id', '=', 1 )
        ->count();
        $returntoagent = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchquerystatus_id', '=', 4 )
        ->where( 'status_id', '=', 1 )
        ->count();
        $senttoclient = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchquerystatus_id', '=', 5 )
        ->where( 'status_id', '=', 1 )
        ->count();
        $approve = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchquerystatus_id', '=', 6 )
        ->where( 'status_id', '=', 1 )
        ->count();
        $reject = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchquerystatus_id', '=', 7 )
        ->where( 'status_id', '=', 1 )
        ->count();
        $editbyclient = DB::table( 'patchquery' )
        ->select( 'patchquery_id' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'patchquerystatus_id', '=', 8 )
        ->where( 'status_id', '=', 1 )
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
        $getuser = DB::table( 'user' )
        ->select( 'user_id', 'user_name', 'user_picture', 'user_target' )
        ->whereIn( 'user_id', $sortuserbrand )
        ->whereIn( 'role_id', [ 6, 7 ] )
        ->where( 'status_id', '=', 1 )
        ->get();
        $topagent = array();
        $topindex = 0;
        $topthree = 0;
        foreach ( $getuser as $getusers ) {
            $getachieve = DB::table( 'patch' )
            ->select( 'patch_amount' )
            ->where( 'status_id', '=', 1 )
            ->where( 'patch_date', 'like', $setyearmonth.'%' )
            ->where( 'created_by', '=', $getusers->user_id )
            ->sum( 'patch_amount' );
            if ( $getachieve != 0 && $topthree <= 2 ) {
                $getusers->achieve = $getachieve;
                $topagent[ $topindex ] = $getusers;

                $topthree++;
            }
            $topindex++;
        }
        $topagent = array_sort( $topagent, 'achieve', SORT_DESC );
        $sorttopagent = array();
        $sortindex = 0;
        foreach ( $topagent as $topagents ) {
            $sorttopagent[ $sortindex ] = $topagents;
            $sortindex++;
        }
        $agenttarget = array();
        $target = 0;
        foreach ( $getuser as $getusers ) {
            $getachieve = DB::table( 'patch' )
            ->select( 'patch_amount' )
            ->where( 'status_id', '=', 1 )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'patch_date', 'like', $setyearmonth.'%' )
            ->where( 'created_by', '=', $getusers->user_id )
            ->sum( 'patch_amount' );
            $getpaid = DB::table( 'patch' )
            ->select( 'patch_amount' )
            ->where( 'status_id', '=', 1 )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'patch_biillingstatus', '=', 'Paid' )
            ->where( 'patch_date', 'like', $setyearmonth.'%' )
            ->where( 'created_by', '=', $getusers->user_id )
            ->sum( 'patch_amount' );
            $getcancel = DB::table( 'patch' )
            ->select( 'patch_amount' )
            ->where( 'status_id', '=', 1 )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'patch_biillingstatus', '=', 'Cancel' )
            ->where( 'patch_date', 'like', $setyearmonth.'%' )
            ->where( 'created_by', '=', $getusers->user_id )
            ->sum( 'patch_amount' );
            $getusers->achieve = $getachieve;
            $getusers->paid = $getpaid;
            $getusers->cancel = $getcancel;
            $agenttarget[ $target ] = $getusers;

            $target++;
        }
        $graphdatatotal = array();
        $indextotal = 0;
        foreach ( $list as $lists ) {
            $total = DB::table( 'patch' )
            ->select( 'patch_amount' )
            ->where( 'patch_date', '=', $lists )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patch_amount' );
            $graphdatatotal[ $indextotal ] = $total;
            $indextotal++;
        }
        $graphdatapaid = array();
        $indexpaid = 0;
        foreach ( $list as $lists ) {
            $paid = DB::table( 'patch' )
            ->select( 'patch_amount' )
            ->where( 'patch_biillingstatus', '=', 'Paid' )
            ->where( 'patch_date', '=', $lists )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patch_amount' );
            $graphdatapaid[ $indexpaid ] = $paid;
            $indexpaid++;
        }
        $graphdataramaining = array();
        $indexcancel = 0;
        foreach ( $list as $lists ) {
            $cancel = DB::table( 'patch' )
            ->select( 'patch_amount' )
            ->where( 'patch_biillingstatus', '=', 'Cancel' )
            ->where( 'patch_date', '=', $lists )
            ->whereIn( 'created_by', $sortuserbrand )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patch_amount' );
            $graphdataramaining[ $indexcancel ] = $cancel;
            $indexcancel++;
        }
        $orders = DB::table( 'patchorderlist' )
        ->select( 'patch_title', 'patch_quantity', 'patch_amount', 'patch_deliverycost', 'patch_date', 'patchstatus_name' )
        ->whereIn( 'patch_date', $list )
        ->where( 'status_id', '=', 1 )
        ->whereIn( 'created_by', $sortuserbrand )
        ->get();
        $query = DB::table( 'patchquerylist' )
        ->select( 'patchquery_title', 'patchquery_quantity', 'patchquery_amount', 'patchquery_deliverycost', 'patchquery_date', 'patchquerystatus_name', 'user_name' )
        ->whereIn( 'patchquery_date', $list )
        ->where( 'status_id', '=', 1 )
        ->whereIn( 'created_by', $sortuserbrand )
        ->get();
        $userpicturepath = URL::to( '/' ).'/public/user_picture/';
        $brandlogopath = URL::to( '/' ).'/public/brand_logo/';
        return array( 'branddetails' => $branddetails, 'orderdata' => $orderdata, 'querydata' => $querydata, 'sorttopagent' =>$sorttopagent, 'agenttarget' => $agenttarget, 'orders' => $orders, 'query' => $query, 'graphdatatotal' => $graphdatatotal, 'graphdatapaid' => $graphdatapaid, 'graphdataramaining' => $graphdataramaining, 'userpicturepath' => $userpicturepath, 'brandlogopath' => $brandlogopath );
    }

    public function salespatchdashboard( $yearmonth, $brand_id, $id, $roleid ) {
        $yearmonth = explode( '-', $yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $getyearandmonth = explode( '-', $setyearmonth );
        $getfirstdate = $setyearmonth.'-01';
        if ( $yearmonth[ 1 ] == '1' || $yearmonth[ 1 ] == '3' || $yearmonth[ 1 ] == '5' || $yearmonth[ 1 ] == '7' || $yearmonth[ 1 ] == '8' || $yearmonth[ 1 ] == '10' || $yearmonth[ 1 ] == '12' ) {
            $noofdays = 31;
        } elseif ( $yearmonth[ 1 ] == '2' ) {
            $noofdays = 28;
        } else {
            $noofdays = 30;
        }
        $list = array();
        for ( $d = 1; $d <= $noofdays; $d++ ) {
            $time = mktime( 12, 0, 0, $getyearandmonth[ 1 ], $d, $getyearandmonth[ 0 ] );

            if ( date( 'm', $time ) == $getyearandmonth[ 1 ] )
            $list[] = date( 'Y-m-d', $time );
        }
        $branddetail = DB::table( 'brand' )
        ->select( '*' )
        ->where( 'brand_id', '=', $brand_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        $getuser = DB::table( 'user' )
        ->select( '*' )
        ->where( 'user_id', '=', $id )
        ->where( 'status_id', '=', 1 )
        ->first();
        if ( $roleid == 6 ) {
            $userinbrand = DB::table( 'userbarnd' )
            ->select( 'user_id' )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'status_id', '=', 1 )
            ->get();
            $sortuserid = array();
            foreach ( $userinbrand as $userinbrands ) {
                $sortuserid[] = $userinbrands->user_id;
            }
            $userlistids = DB::table( 'user' )
            ->select( 'user_id' )
            ->whereIn( 'role_id', [ 5, 7 ] )
            ->whereIn( 'user_id', $sortuserid )
            ->where( 'status_id', '=', 1 )
            ->get();
            $branduser = array();
            foreach ( $userlistids as $userlistidss ) {
                $branduser[] = $userlistidss->user_id;
            }
            $total = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'status_id', '=', 1 )
            ->count();
            $paid = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'status_id', '=', 1 )
            ->count();

            $convertionoverview = array(
                'patchgrossquerycount' => $total,
                'patchpaidcount' => $paid,
            );
            $basictarget = DB::table( 'user' )
            ->select( 'user_target' )
            ->whereIn( 'user_id', $branduser )
            ->where( 'status_id', '=', 1 )
            ->sum( 'user_target' );
            $targetincrement = DB::table( 'usertarget' )
            ->select( 'usertarget_target' )
            ->whereIn( 'user_id', $branduser )
            ->where( 'usertarget_month', '<=', $setyearmonth )
            ->where( 'status_id', '=', 1 )
            ->sum( 'usertarget_target' );
            $target = $basictarget+$targetincrement;
            $agents = DB::table( 'user' )
            ->select( 'user_id' )
            ->whereIn( 'role_id', [ 5, 7 ] )
            ->whereIn( 'user_id', $branduser )
            ->where( 'status_id', '=', 1 )
            ->count();
            $managertarget = $agents*1000;
            $managercommission;
            $paidamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'status_id', '=', 1 )
            ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->sum( 'patchqueryitem_proposalquote' );
            $productioncost = 30/100*$paidamount;
            $amountforcommission = $paidamount-$productioncost;
            if ( $amountforcommission >= $managertarget ) {
                $managercommission = $agents*10000;
            } else {
                $managercommission = 0;
            }
            $commissionoverview = array(
                'totaltarget' 	=> $managertarget,
                'grosssale' 	=> $amountforcommission,
                'paidsale' 		=> $managercommission,
            );
            $patchpickedquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 9 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchpickedamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 9 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchvendorquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 2 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchvendoramount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 2 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchreturnquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 3 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchreturnamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 3 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchclientquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 5 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchclientamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 5 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchapprovequantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 6 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchapproveamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 6 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchrejectquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 7 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchrejectamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 7 )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchpaidquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchpaidamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchdeliverquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->whereIn( 'patchquerystatus_id', [ 11, 12 ] )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchdeliveramount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->whereIn( 'patchquerystatus_id', [ 11, 12 ] )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchquerylist = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id', 'patchquerystatus_name', 'user_name' )
            ->where( 'brand_id', '=', $brand_id )
            ->whereIn( 'created_by', $branduser )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->get();
        } else {
            $total = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'status_id', '=', 1 )
            ->count();
            $paid = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'status_id', '=', 1 )
            ->count();

            $convertionoverview = array(
                'patchgrossquerycount' => $total,
                'patchpaidcount' => $paid,
            );
            $basictarget = DB::table( 'user' )
            ->select( 'user_target' )
            ->where( 'user_id', '=', $id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'user_target' );
            $targetincrement = DB::table( 'usertarget' )
            ->select( 'usertarget_target' )
            ->where( 'user_id', '=', $id )
            ->where( 'usertarget_month', '<=', $setyearmonth )
            ->where( 'status_id', '=', 1 )
            ->sum( 'usertarget_target' );
            $target = $basictarget+$targetincrement;
            $paidamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'status_id', '=', 1 )
            ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->sum( 'patchqueryitem_proposalquote' );
            $productioncost = 30/100*$paidamount;
            $amountforcommission = $paidamount-$productioncost;
            $getcommission = DB::table( 'commission' )
            ->select( '*' )
            ->where( 'brandtype_id', '=', 2 )
            ->where( 'status_id', '=', 1 )
            ->where( 'user_id', '=', $id )
            ->orderBy( 'commission_id', 'DESC' )
            ->get();
            $commissionindex = 0;
            $usercommission = 0;
            foreach ( $getcommission as $getcommissions ) {
                if ( $amountforcommission >= $getcommissions->commission_from && $amountforcommission >= $getcommissions->commission_to && $commissionindex == 0 ) {
                    $usercommission = $getcommissions->commission_rate;
                    $commissionindex++;
                    break;
                } else {
                    $usercommission = 0;
                }
            }
            $commissionoverview = array(
                'totaltarget' 	=> $target,
                'grosssale' 	=> $amountforcommission,
                'paidsale' 		=> $usercommission,
            );
            $patchpickedquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 9 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchpickedamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 9 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchvendorquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 2 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchvendoramount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 2 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchreturnquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 3 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchreturnamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 3 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchclientquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 5 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchclientamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 5 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchapprovequantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 6 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchapproveamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 6 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchrejectquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->where( 'patchquerystatus_id', '=', 7 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchrejectamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->where( 'patchquerystatus_id', '=', 7 )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchpaidquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchpaidamount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->whereIn( 'patchquerystatus_id', [ 10, 11, 12 ] )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchdeliverquantity = DB::table( 'patchquery' )
            ->select( 'patchquery_id' )
            ->whereIn( 'patchquerystatus_id', [ 11, 12 ] )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->count();
            $patchdeliveramount = DB::table( 'patchqueryanditem' )
            ->select( 'patchqueryitem_proposalquote' )
            ->whereIn( 'patchquerystatus_id', [ 11, 12 ] )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryitem_proposalquote' );
            $patchquerylist = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id', 'patchquerystatus_name', 'user_name' )
            ->where( 'brand_id', '=', $brand_id )
            ->where( 'created_by', '=', $id )
            ->whereIn( 'patchquery_date', $list )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->get();
        }
        $patchstatuswisequantity = array( $patchpickedquantity, $patchvendorquantity, $patchreturnquantity, $patchclientquantity, $patchapprovequantity, $patchrejectquantity, $patchpaidquantity, $patchdeliverquantity );
        $patchstatuswiseamount = array( $patchpickedamount, $patchvendoramount, $patchreturnamount, $patchclientamount, $patchapproveamount, $patchrejectamount, $patchpaidamount, $patchdeliveramount );
        $userpicturepath = URL::to( '/' ).'/public/user_picture/';
        $logopath = URL::to( '/' ).'/public/brand_logo/';
        return array( 'branddetail' => $branddetail, 'userdata' => $getuser, 'patchorderoverview' => $convertionoverview, 'commissionoverview' => $commissionoverview, 'patchquerylist' => $patchquerylist, 'patchstatuswisequantity' => $patchstatuswisequantity, 'patchstatuswiseamount' => $patchstatuswiseamount, 'userpicturepath' => $userpicturepath, 'logopath' => $logopath );
    }

    public function patchqueryprofitlossstatement( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_id'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( 'Patch Query Id Required', 400 );
        }
        $data = DB::table( 'patchquerydetails' )
        ->select( '*' )
        ->where( 'status_id', '=', 1 )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->first();
        $dollarquoteamount = DB::table( 'patchqueryanditem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchqueryitem_proposalquote' );
        $dollarshipmentquoteamount = DB::table( 'patchquery' )
        ->select( 'patchquery_shipmentamount' )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchquery_shipmentamount' );
        $dollarnetamount = $dollarquoteamount+$dollarshipmentquoteamount;
        $pkrnetamount = $dollarnetamount*270;
        $pkrproductioncost = DB::table( 'patchpayment' )
        ->select( 'patchpayment_amount' )
        ->where( 'patch_id', '=', $request->patchquery_id )
        ->where( 'patchpaymenttype_id', '=', 1 )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchpayment_amount' );
        $pkrshipmentcost = $dollarshipmentquoteamount*270;
        $pkrnetcost = $pkrshipmentcost+$pkrproductioncost;
        $netprofitloss = $pkrnetamount-$pkrnetcost;
        $finalvendor = DB::table( 'patchqueryitem' )
        ->select( 'patchqueryitem_finalvendor', 'patchqueryitem_quantity', 'patchqueryitem_id' )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->where( 'status_id', '=', 1 )
        ->get();
        $vendorpaymetdetail = array();
        $fvindex = 0;
        foreach ( $finalvendor as $finalvendors ) {
            if ( isset( $finalvendors->patchqueryitem_finalvendor ) ) {
                $patchcategory = DB::table( 'patchqueryitemdetails' )
                ->select( 'patchquerycategory_name' )
                ->where( 'patchqueryitem_id', '=', $finalvendors->patchqueryitem_id )
                ->where( 'status_id', '=', 1 )
                ->first();
                $vendornameandcost = DB::table( 'patchqueryitemvendordetails' )
                ->select( 'patchqueryvendor_cost', 'vendor_name', 'patchqueryvendor_productiondays' )
                ->where( 'patchqueryitem_id', '=', $finalvendors->patchqueryitem_id )
                ->where( 'vendorproduction_id', '=', $finalvendors->patchqueryitem_finalvendor )
                ->where( 'status_id', '=', 1 )
                ->first();
                $paidcost = DB::table( 'patchpayment' )
                ->select( 'patchpayment_amount' )
                ->where( 'patch_id', '=', $finalvendors->patchqueryitem_id )
                ->where( 'patchpaymenttype_id', '=', 1 )
                ->where( 'status_id', '=', 1 )
                ->sum( 'patchpayment_amount' );
                $costperpiece = $vendornameandcost->patchqueryvendor_cost/$finalvendors->patchqueryitem_quantity;
                $vendorremainingamount = $vendornameandcost->patchqueryvendor_cost-$paidcost;

                $vendorpaymetdetail[ $fvindex ][ 'patchqueryitem_id' ] = $finalvendors->patchqueryitem_id;
                $vendorpaymetdetail[ $fvindex ][ 'productiondays' ] = $vendornameandcost->patchqueryvendor_productiondays;
                $vendorpaymetdetail[ $fvindex ][ 'customername' ] = $data->patchquery_clientname;
                $vendorpaymetdetail[ $fvindex ][ 'categoryname' ] = $patchcategory->patchquerycategory_name;
                $vendorpaymetdetail[ $fvindex ][ 'name' ] = $vendornameandcost->vendor_name;
                $vendorpaymetdetail[ $fvindex ][ 'quantity' ] = $finalvendors->patchqueryitem_quantity;
                $vendorpaymetdetail[ $fvindex ][ 'vendorcosttotal' ] = $vendornameandcost->patchqueryvendor_cost;
                $vendorpaymetdetail[ $fvindex ][ 'paidcost' ] = $paidcost;
                $vendorpaymetdetail[ $fvindex ][ 'costperpiece' ] = $costperpiece;
                $vendorpaymetdetail[ $fvindex ][ 'remainingamount' ] = $vendorremainingamount;
            } else {
                $vendorpaymetdetail[ $fvindex ][ 'customername' ] = '-';
                $vendorpaymetdetail[ $fvindex ][ 'categoryname' ] = '-';
                $vendorpaymetdetail[ $fvindex ][ 'name' ] = '-';
                $vendorpaymetdetail[ $fvindex ][ 'quantity' ] = '-';
                $vendorpaymetdetail[ $fvindex ][ 'vendorcosttotal' ] = 0;
                $vendorpaymetdetail[ $fvindex ][ 'paidcost' ] = 0;
                $vendorpaymetdetail[ $fvindex ][ 'costperpiece' ] = 0;
                $vendorpaymetdetail[ $fvindex ][ 'remainingamount' ] = 0;
            }
            $fvindex++;
        }
        $pkrshipmentquoteamount = $dollarshipmentquoteamount/270;
        $shippingid = DB::table( 'patchquery' )
        ->select( 'patchqueryshipping_id' )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        if ( isset( $shippingid->patchqueryshipping_id ) ) {
            $shippingcost = DB::table( 'patchqueryshipping' )
            ->select( 'patchqueryshipping_cost' )
            ->where( 'patchqueryshipping_id', '=', $request->patchqueryshipping_id )
            ->where( 'status_id', '=', 1 )
            ->sum( 'patchqueryshipping_cost' );
        } else {
            $shippingcost = 0;
        }
        $pkrshipmentpaidamount = DB::table( 'patchpayment' )
        ->select( 'patchpayment_amount' )
        ->where( 'patch_id', '=', $request->patchquery_id )
        ->where( 'patchpaymenttype_id', '=', 2 )
        ->where( 'status_id', '=', 1 )
        ->sum( 'patchpayment_amount' );
        $pkrshipmentremainingamount = $shippingcost-$pkrshipmentpaidamount;
        $pkrshipmentprofitloss = $pkrshipmentquoteamount-$shippingcost;
        $stats = array();
        $stats[ 'dollarquoteamount' ] = $dollarquoteamount;
        $stats[ 'dollarshipmentquoteamount' ] = $dollarshipmentquoteamount;
        $stats[ 'dollarnetamount' ] = $dollarnetamount;
        $stats[ 'pkrnetamount' ] = $pkrnetamount;
        $stats[ 'pkrnetcost' ] = $pkrnetcost;
        $stats[ 'netprofitloss' ] = $netprofitloss;
        $stats[ 'isprofitorloss' ] = $netprofitloss < 0 ? '-' : '+';
        $stats[ 'pkrshipmentquoteamount' ] = $pkrshipmentquoteamount;
        $stats[ 'pkrshipmenttotalamount' ] = $shippingcost;
        $stats[ 'pkrshipmentpaidamount' ] = $pkrshipmentpaidamount;
        $stats[ 'pkrshipmentremainingamount' ] = $pkrshipmentremainingamount;
        $stats[ 'pkrshipmentprofitloss' ] = $pkrshipmentprofitloss;
        if ( $data ) {
            return response()->json( [ 'data' => $data, 'stats' => $stats, 'vendorpaymetdetail' => $vendorpaymetdetail, 'message' => 'Patch Query Profit Loss Statement' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }
}