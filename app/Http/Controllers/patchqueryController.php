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

class patchqueryController extends Controller
 {
    public $emptyarray = array();

    public function patchquerycategories( Request $request ) {
        $data = DB::table( 'patchquerycategory' )
        ->select( 'patchquerycategory_id', 'patchquerycategory_name' )
        ->where( 'status_id', '=', 1 )
        ->get();
        if ( $data ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patch Query Categories' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function patchqueryshippingweight( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'vendordelivery_id'	    => 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $data = DB::table( 'patchqueryshipping' )
        ->select( 'patchqueryshipping_id', 'patchqueryshipping_weight' )
        ->where( 'vendordelivery_id', '=', $request->vendordelivery_id )
        ->where( 'status_id', '=', 1 )
        ->get();
        if ( $data ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patch Query Shipping Weight' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function patchqueryshippingcost( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchqueryshipping_id'	    => 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $data = DB::table( 'patchqueryshipping' )
        ->select( 'patchqueryshipping_cost' )
        ->where( 'patchqueryshipping_id', '=', $request->patchqueryshipping_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        if ( $data ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patch Query Shipping Cost' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function createpatchquery( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_clientname' 		    	=> 'required',
            'patchquery_clientemail'	   			=> 'required',
            'patchquery_clientphone'		    	=> 'required',
            'patchquery_clientzip'		    		=> 'required',
            'country_id'		    				=> 'required',
            'state_id'								=> 'required',
            'patchquery_clientaddress'		    	=> 'required',
            'patchquery_clientbussinessname'		=> 'required',
            'patchquery_clientbussinessemail'		=> 'required',
            'patchquery_clientbussinesswebsite'		=> 'required',
            'patchquery_clientbussinessphone'		=> 'required',
            'patchquery_title'						=> 'required',
            'patchquery_medium'						=> 'required',
            'patchquery_clientbudget'				=> 'required',
            'patchquery_shippingaddress'			=> 'required',
            'patchquery_otherdetails'				=> 'required',
            'brand_id'								=> 'required',
            'patchqueryitem'	    				=> 'required',
            'patchquery_shipmentinvoiceamount'	    => 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $patchquery_islead = DB::table( 'lead' )
        ->select( 'lead_id' )
        ->where( 'lead_email', '=', $request->patchquery_clientemail )
        ->where( 'brand_id', '=', $request->brand_id )
        ->where( 'status_id', '=', 1 )
        ->count();
        $adds[] = array(
            'patchquery_clientname' 			=> $request->patchquery_clientname,
            'patchquery_clientemail' 			=> $request->patchquery_clientemail,
            'patchquery_clientphone' 			=> $request->patchquery_clientphone,
            'patchquery_clientzip' 				=> $request->patchquery_clientzip,
            'country_id' 						=> $request->country_id,
            'state_id' 							=> $request->state_id,
            'patchquery_clientaddress' 			=> $request->patchquery_clientaddress,
            'patchquery_clientbussinessname' 	=> $request->patchquery_clientbussinessname,
            'patchquery_clientbussinessemail' 	=> $request->patchquery_clientbussinessemail,
            'patchquery_clientbussinesswebsite' => $request->patchquery_clientbussinesswebsite,
            'patchquery_clientbussinessphone'	=> $request->patchquery_clientbussinessphone,
            'patchquery_title' 					=> $request->patchquery_title,
            'patchquery_shippingaddress'		=> $request->patchquery_shippingaddress,
            'patchquery_clientbudget' 			=> $request->patchquery_clientbudget,
            'patchquery_medium' 				=> $request->patchquery_medium,
            'patchquery_otherdetails'			=> $request->patchquery_otherdetails,
            'patchquery_shipmentinvoiceamount'	=> $request->patchquery_shipmentinvoiceamount,
            'patchquery_islead'					=> $request->patchquery_islead,
            'patchquery_date'					=> $request->patchquery_date,
            'patchquerystatus_id'				=> 2,
            'brand_id'				    		=> $request->brand_id,
            'status_id'		 		    		=> 1,
            'created_by'	 		    		=> $request->user_id,
            'created_at'	 		    		=> date( 'Y-m-d h:i:s' ),
        );
        $save = DB::table( 'patchquery' )->insert( $adds );
        $patchquery_id = DB::getPdo()->lastInsertId();
        $patchqueryitem = $request->patchqueryitem;
        foreach ( $patchqueryitem as $patchqueryitems ) {
            $basic = array(
                'patchquerycategory_id' 		=> $patchqueryitems[ 'patchquerycategory_id' ],
                'patchqueryitem_quantity' 		=> $patchqueryitems[ 'patchqueryitem_quantity' ],
                'patchqueryitem_height' 		=> $patchqueryitems[ 'patchqueryitem_height' ],
                'patchqueryitem_width'			=> $patchqueryitems[ 'patchqueryitem_width' ],
                'patchtype_id'					=> $patchqueryitems[ 'patchtype_id' ],
                'patchback_id'					=> $patchqueryitems[ 'patchback_id' ],
                'patchqueryitem_otherdetails'	=> $patchqueryitems[ 'patchqueryitem_otherdetails' ],
                'patchqueryitem_istask'	        => 1,
                'patchquery_id' 				=> $patchquery_id,
                'patchqueryitem_date' 			=> $request->patchquery_date,
                'status_id'						=> 1,
                'created_by'					=> $request->user_id,
                'created_at'					=> date( 'Y-m-d h:i:s' ),
            );
            DB::table( 'patchqueryitem' )->insert( $basic );
            $patchqueryitem_id = DB::getPdo()->lastInsertId();
            $task_token = openssl_random_pseudo_bytes(7);
    	    $task_token = bin2hex($task_token);
            $task = array(
                'task_title' 		=> $request->patchquery_title,
                'task_description' 	=> $request->patchquery_otherdetails,
                'task_deadlinedate' => date( 'Y-m-d' ),
                'task_manager' 		=> $request->user_id,
                'task_token' 		=> $task_token,
                'task_date' 		=> date('Y-m-d'),
                'taskstatus_id'		=> 1,
                'order_id'			=> $patchqueryitem_id,
                'order_token'		=> $patchqueryitem_id,
                'brand_id'		    => $request->brand_id,
                'status_id'			=> 3,
                'created_by'		=> $request->user_id,
                'created_at'		=> date('Y-m-d h:i:s'),
            );
            DB::table('task')->insert($task);
            if ( isset( $patchqueryitems[ 'attachment' ] ) ) {
                $attachment = $patchqueryitems[ 'attachment' ];
                $index = 0 ;
                $filename = array();
                foreach ( $attachment as $attachments ) {
                    $saveattachment = array();
                    if ( $attachments->isValid() ) {
                        $number = rand( 1, 999 );
                        $numb = $number / 7 ;
                        $foldername = $patchquery_id;
                        $extension = $attachments->getClientOriginalExtension();
                        $filename = $attachments->getClientOriginalName();
                        $filename = $attachments->move( public_path( 'patchqueryitem/'.$foldername ), $filename );
                        $filename = $attachments->getClientOriginalName();
                        $saveattachment = array(
                            'patchqueryitemattachment_name'	=> $filename,
                            'patchqueryitem_id'				=> $patchqueryitem_id,
							'patchquery_id'					=> $patchquery_id,
                            'status_id' 					=> 1,
                            'created_by'					=> $request->user_id,
                            'created_at'					=> date( 'Y-m-d h:i:s' ),
                        );
                    } else {
                        return response()->json( 'Invalid File', 400 );
                    }
                    DB::table( 'patchqueryitemattachment' )->insert( $saveattachment );
                }
            }
        }
        $proposal[] = array(
            'patchproposal_stiches' 		=> '',
            'patchproposal_colors'			=> '',
            'patchproposal_colorchanges' 	=> '',
            'patchproposal_stops'			=> '',
            'patchproposal_machine'			=> '',
            'patchproposal_trims' 			=> '',
            'patchquery_id'			    	=> $patchquery_id,
            'status_id'		 		    	=> 1,
            'created_by'	 		    	=> $request->user_id,
            'created_at'	 		    	=> date( 'Y-m-d h:i:s' ),
        );
        $save = DB::table( 'patchproposal' )->insert( $proposal );
        if ( isset( $request->patchqueryattachment ) ) {
            $patchqueryattachment = $request->patchqueryattachment;
            $index = 0 ;
            $filename = array();
            foreach ( $patchqueryattachment as $patchqueryattachments ) {
                $saveattachment = array();
                if ( $patchqueryattachments->isValid() ) {
                    $number = rand( 1, 999 );
                    $numb = $number / 7 ;
                    $foldername = $patchquery_id;
                    $extension = $patchqueryattachments->getClientOriginalExtension();
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $filename = $patchqueryattachments->move( public_path( 'patchquery/'.$foldername ), $filename );
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $saveattachment = array(
                        'patchqueryattachment_name'	=> $filename,
                        'patchquery_id'				=> $patchquery_id,
                        'status_id' 			    => 1,
                        'created_by'			    => $request->user_id,
                        'created_at'			    => date( 'Y-m-d h:i:s' ),
                    );
                } else {
                    return response()->json( 'Invalid File', 400 );
                }
                DB::table( 'patchqueryattachment' )->insert( $saveattachment );
            }
        }
        if ( $save ) {
            return response()->json( [ 'message' => 'Patch Query Created Successfully' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function patchquerylist( Request $request ) {
        if ( $request->role_id <= 2 ) {
            if ( $request->patchquerystatus_id == 1 ) {
                $data = DB::table( 'patchquerylist' )
                ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquery_discount', 'patchquerystatus_id','patchquerystatus_name','user_name' )
                ->where( 'patchquerystatus_id', '=', $request->patchquerystatus_id )
                ->where( 'patchquery_manager', '=', null )
                ->where( 'status_id', '=', 1 )
                ->orderBy( 'patchquery_id', 'DESC' )
                ->paginate( 30 );
            } else {
                $data = DB::table( 'patchquerylist' )
                ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquery_discount', 'patchquerystatus_id','patchquerystatus_name','user_name' )
                ->where( 'status_id', '=', 1 )
                ->where( 'patchquery_manager', '!=', null )
                ->whereNotIn( 'patchquerystatus_id', [ 6, 7,10,11,12 ] )
                ->orderBy( 'patchquery_id', 'DESC' )
                ->paginate( 30 );
            }
        } elseif ( $request->role_id == 3 ) {
            $brand = DB::table( 'userbarnd' )
            ->select( 'brand_id' )
            ->where( 'user_id', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->get();
            $sortbrand = array();
            foreach ( $brand as $brands ) {
                $sortbrand[] = $brands->brand_id;
            }
            if ( $request->patchquerystatus_id == 1 ) {
                $data = DB::table( 'patchquerylist' )
                ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquery_discount', 'patchquerystatus_id','patchquerystatus_name','user_name' )
                ->where( 'patchquerystatus_id', '=', $request->patchquerystatus_id )
                ->whereIn( 'brand_id', $sortbrand )
                ->where( 'status_id', '=', 1 )
                ->orderBy( 'patchquery_id', 'DESC' )
                ->paginate( 30 );
            } else {
                $data = DB::table( 'patchquerylist' )
                ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquery_discount', 'patchquerystatus_id','patchquerystatus_name','user_name' )
                ->whereIn( 'brand_id', $sortbrand )
                ->whereNotIn( 'patchquerystatus_id', [ 6, 7,10,11,12 ] )
                ->where( 'status_id', '=', 1 )
                ->orderBy( 'patchquery_id', 'DESC' )
                ->paginate( 30 );
            }
        } elseif ( $request->role_id == 20 ) {
            $brand = DB::table( 'userbarnd' )
            ->select( 'brand_id' )
            ->where( 'user_id', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->get();
            $sortbrand = array();
            foreach ( $brand as $brands ) {
                $sortbrand[] = $brands->brand_id;
            }
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquery_discount', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->whereIn( 'brand_id', $sortbrand )
            ->whereIn( 'patchquerystatus_id', [2,3] )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );
        } elseif ( $request->role_id == 6 ) {
            if ( $request->patchquerystatus_id == 1 ) {
                $data = DB::table( 'patchquerylist' )
                ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquery_discount', 'patchquerystatus_id','patchquerystatus_name','user_name' )
                ->where( 'patchquerystatus_id', '=', $request->patchquerystatus_id )
                ->where( 'patchquery_manager', '=', null )
                ->where( 'status_id', '=', 1 )
                ->orderBy( 'patchquery_id', 'DESC' )
                ->paginate( 30 );

            } else {
                $data = DB::table( 'patchquerylist' )
                ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquery_discount', 'patchquerystatus_id','patchquerystatus_name','user_name' )
                ->where( 'patchquery_manager', '=', $request->user_id )
                ->whereNotIn( 'patchquerystatus_id', [ 6, 7,10,11,12 ] )
                ->where( 'status_id', '=', 1 )
                ->orderBy( 'patchquery_id', 'DESC' )
                ->paginate( 30 );

            }
        } else {
            if ( $request->patchquerystatus_id == 1 ) {
                $data = DB::table( 'patchquerylist' )
                ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquery_discount', 'patchquerystatus_id','patchquerystatus_name','user_name' )
                ->where( 'created_by', '=', $request->user_id )
                ->where( 'patchquerystatus_id', '=', $request->patchquerystatus_id )
                ->where( 'status_id', '=', 1 )
                ->orderBy( 'patchquery_id', 'DESC' )
                ->paginate( 30 );
            } else {
                $data = DB::table( 'patchquerylist' )
                ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquery_discount', 'patchquerystatus_id','patchquerystatus_name','user_name' )
                ->where( 'created_by', '=', $request->user_id )
                ->whereNotIn( 'patchquerystatus_id', [ 6, 7 ] )
                ->where( 'status_id', '=', 1 )
                ->orderBy( 'patchquery_id', 'DESC' )
                ->paginate( 30 );
            }
        }
        if ( isset( $data ) ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patch Query List' ], 200 );
        } else {
            return response()->json( [ 'data' => $emptyarray, 'message' => 'Patch Query List' ], 200 );
        }
    }

    public function statuswisepatchquerylist( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_isorderorsample'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        if ( $request->role_id <= 2 ) {
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->whereIn( 'patchquerystatus_id', [5,6,10,11] )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );
        } elseif ( $request->role_id == 3 ) {
            $brand = DB::table( 'userbarnd' )
            ->select( 'brand_id' )
            ->where( 'user_id', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->get();
            $sortbrand = array();
            foreach ( $brand as $brands ) {
                $sortbrand[] = $brands->brand_id;
            }
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->whereIn( 'patchquerystatus_id', [6,10,11] )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->whereIn( 'brand_id', $sortbrand )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );
        } elseif ( $request->role_id == 20 ) {
            $brand = DB::table( 'userbarnd' )
            ->select( 'brand_id' )
            ->where( 'user_id', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->get();
            $sortbrand = array();
            foreach ( $brand as $brands ) {
                $sortbrand[] = $brands->brand_id;
            }
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->whereIn( 'patchquerystatus_id', [6,10,11] )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->whereIn( 'brand_id', $sortbrand )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );
        } elseif ( $request->role_id == 6 ) {
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->whereIn( 'patchquerystatus_id', [6,10,11] )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->where( 'patchquery_manager', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );

        } else {
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_clientname', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->where( 'created_by', '=', $request->user_id )
            ->whereIn( 'patchquerystatus_id', [6,10,11] )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );
        }
        if ( isset( $data ) ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patch Query List' ], 200 );
        } else {
            return response()->json( [ 'data' => $emptyarray, 'message' => 'Patch Query List' ], 200 );
        }
    }

    public function deliveredpatchquerylist( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_isorderorsample'	=> 'required',
            'patchquerystatus_id'	        => 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        if ( $request->role_id <= 2 ) {
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->where( 'patchquerystatus_id', '=', $request->patchquerystatus_id )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );
        } elseif ( $request->role_id == 3 ) {
            $brand = DB::table( 'userbarnd' )
            ->select( 'brand_id' )
            ->where( 'user_id', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->get();
            $sortbrand = array();
            foreach ( $brand as $brands ) {
                $sortbrand[] = $brands->brand_id;
            }
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->where( 'patchquerystatus_id', '=', $request->patchquerystatus_id )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->whereIn( 'brand_id', $sortbrand )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );
        } elseif ( $request->role_id == 20 ) {
            $brand = DB::table( 'userbarnd' )
            ->select( 'brand_id' )
            ->where( 'user_id', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->get();
            $sortbrand = array();
            foreach ( $brand as $brands ) {
                $sortbrand[] = $brands->brand_id;
            }
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->where( 'patchquerystatus_id', '=', $request->patchquerystatus_id )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->whereIn( 'brand_id', $sortbrand )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );
        } elseif ( $request->role_id == 6 ) {
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->where( 'patchquerystatus_id', '=', $request->patchquerystatus_id )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->where( 'patchquery_manager', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );

        } else {
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
            ->where( 'created_by', '=', $request->user_id )
            ->where( 'patchquerystatus_id', '=', $request->patchquerystatus_id )
            ->whereIn( 'patchquery_isorderorsample', $request->patchquery_isorderorsample )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->paginate( 30 );
        }
        if ( isset( $data ) ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patch Query List' ], 200 );
        } else {
            return response()->json( [ 'data' => $emptyarray, 'message' => 'Patch Query List' ], 200 );
        }
    }

    public function patchquerydetails( Request $request ) {
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
        $items = DB::table( 'patchqueryitemdetails' )
        ->select( '*' )
        ->where( 'status_id', '=', 1 )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->get();
        $itemdetails = array();
        $index=0;
        foreach($items as $itemss){
            $vendor = DB::table( 'patchqueryitemvendordetails' )
            ->select( 'patchqueryvendor_id','vendorproduction_id','vendor_name','patchqueryvendor_cost','patchqueryvendor_productiondays' )
            ->where( 'status_id', '=', 1 )
            ->where( 'patchqueryitem_id', '=', $itemss->patchqueryitem_id )
            ->get();
            $finalvendor = DB::table( 'patchqueryitemvendordetails' )
            ->select( 'patchqueryvendor_id','vendorproduction_id','vendor_name','patchqueryvendor_cost','patchqueryvendor_productiondays' )
            ->where( 'status_id', '=', 1 )
            ->where( 'vendorproduction_id', '=', $itemss->patchqueryitem_finalvendor )
            ->where( 'patchqueryitem_id', '=', $itemss->patchqueryitem_id )
            ->first();
            $itemattachments = DB::table( 'patchqueryitemattachment' )
            ->select( 'patchqueryitemattachment_id', 'patchqueryitemattachment_name' )
            ->where( 'status_id', '=', 1 )
            ->where( 'patchquery_id', '=', $request->patchquery_id )
            ->get();
            $additionalcost = 30/100*$itemss->patchqueryitem_proposalquote;
            $updatedcost = $additionalcost+$itemss->patchqueryitem_proposalquote;
            $itemdetails[$index]['itemattachments'] = $itemattachments;
            $itemdetails[$index]['vendor'] = $vendor;
            $itemdetails[$index]['finalvendor'] = $finalvendor;
            $itemss->patchqueryitem_proposalquote = $itemss->patchqueryitem_discount == 2 ? $itemss->patchqueryitem_proposalquote : $updatedcost;
            $itemdetails[$index]['items'] = $itemss;
            $index++;
        }
        $clientattachments = DB::table( 'patchqueryattachment' )
        ->select( 'patchqueryattachment_id', 'patchqueryattachment_name' )
        ->where( 'patchqueryattachmenttype_id', '=', 1 )
        ->where( 'status_id', '=', 1 )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->get();
		$vendorattachments = DB::table( 'patchqueryattachment' )
        ->select( 'patchqueryattachment_id', 'patchqueryattachment_name' )
        ->where( 'patchqueryattachmenttype_id', '=', 2 )
        ->where( 'status_id', '=', 1 )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->get();
        $onactive = 0;
        if($data->patchquerystatus_id == 9){
            $onactive = 1;
        }elseif($data->patchquerystatus_id == 13){
            $onactive = 2;
        }elseif($data->patchquerystatus_id == 14){
            $onactive = 3;
        }elseif($data->patchquerystatus_id == 2){
            $onactive = 4;
        }elseif($data->patchquerystatus_id == 3){
            $onactive = 5;
        }elseif($data->patchquerystatus_id == 5){
            $onactive = 6;
        }elseif($data->patchquerystatus_id == 6 || $data->patchquerystatus_id == 7){
            $onactive = 7;
        }elseif($data->patchquerystatus_id == 10){
            $onactive = 8;
        }else{
            $onactive = 0;
        }
        $data->ispicked = $data->patchquerystatus_id >= 2 ? 1 : 0;
        $data->iscallback = $data->patchquerystatus_id == 1 || $data->patchquerystatus_id == 9 ? 0 : 1;
        $data->iscalldone = $data->patchquerystatus_id == 1 || $data->patchquerystatus_id == 9 || $data->patchquerystatus_id == 13 ? 0 : 1;
        $data->isfwdvendor = $data->patchquerystatus_id >= 2 && $data->patchquerystatus_id != 9 ? 1 : 0;
        $data->isretmanager = $data->patchquerystatus_id >= 3 && $data->patchquerystatus_id != 9 ? 1 : 0;
        $data->issenttoclient = $data->patchquerystatus_id >= 5 && $data->patchquerystatus_id != 9 ? 1 : 0;
        $data->isapprove = $data->patchquerystatus_id == 6 || $data->patchquerystatus_id == 10 || $data->patchquerystatus_id == 11 || $data->patchquerystatus_id == 12 ? 1 : 0;
        $data->isreject = $data->patchquerystatus_id == 7 ? 1 : 0;
        $data->ispaid = $data->patchquerystatus_id == 10 || $data->patchquerystatus_id == 11 || $data->patchquerystatus_id == 12 ? 1 : 0;
        $data->onactive = $onactive;
        $patchquerypath = URL::to( '/' ).'/public/patchquery/'.$request->patchquery_id.'/';
        $patchqueryitempath = URL::to( '/' ).'/public/patchqueryitem/'.$request->patchquery_id.'/';
        $patchquerycostpath = URL::to( '/' ).'/public/patchquerycostattachment/'.$request->patchquery_id.'/';
        if ( $data ) {
            return response()->json( [ 'data' => $data, 'itemdetails' => $itemdetails, 'patchquerypath' => $patchquerypath , 'patchqueryitempath' => $patchqueryitempath, 'clientattachments' => $clientattachments, 'vendorattachments' => $vendorattachments, 'patchquerycostpath' => $patchquerycostpath, 'message' => 'Patch Query Details' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function generatepatchqueryproposal( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchqueryitem_id'	            => 'required',
            'patchqueryitem_finalvendor'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $proposaltachment = DB::table( 'patchqueryvendor' )
        ->select( 'patchqueryvendor_id', 'patchqueryvendor_proposal','patchquery_id' )
        ->where( 'status_id', '=', 1 )
        ->where( 'patchqueryitem_id', '=', $request->patchqueryitem_id )
        ->where( 'vendorproduction_id', '=', $request->patchqueryitem_finalvendor )
        ->first();
        $item = DB::table( 'patchqueryitemdetails' )
        ->select( '*' )
        ->where( 'status_id', '=', 1 )
        ->where( 'patchqueryitem_id', '=', $request->patchqueryitem_id )
        ->first();
        $patchlogopath = URL::to( '/' ).'/public/patchlogo/logo.png';
        $patchcallpath = URL::to( '/' ).'/public/patchlogo/call.png';
        $patchwebpath = URL::to( '/' ).'/public/patchlogo/web.png';
        $patchlocationpath = URL::to( '/' ).'/public/patchlogo/location.png';
        $item->call = $patchcallpath;
        $item->web = $patchwebpath;
        $item->location = $patchlocationpath;
        if(isset($proposaltachment)){
            $patchqueryproposalpath = URL::to( '/' ).'/public/patchqueryproposal/'.$proposaltachment->patchquery_id.'/';
            return response()->json( [ 'item' => $item, 'proposaltachment' => $proposaltachment, 'patchqueryproposalpath' => $patchqueryproposalpath , 'patchlogopath' => $patchlogopath, 'message' => 'Patch Query Proposal Details' ], 200 );
        }else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function movepatchquery( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_id'			=> 'required',
            'patchquerystatus_id'	=> 'required',
        ] );
        if ( $validate->fails() ) {
            return response()->json( $validate->errors(), 400 );
        }
        if($request->patchquerystatus_id == 10 || $request->patchquerystatus_id == 11){
            if(isset($request->patchquery_isorderorsample)){
                $update  = DB::table( 'patchquery' )
                ->where( 'patchquery_id', '=', $request->patchquery_id )
                ->update( [
                    'patchquery_isorderorsample' 	=> $request->patchquery_isorderorsample,
                    'patchquery_modeofpayments' 	=> $request->patchquery_modeofpayments,
                    'patchquerystatus_id' 	        => $request->patchquerystatus_id,
                    'updated_by'		            => $request->user_id,
                    'updated_by'		            => date( 'Y-m-d h:i:s' ),
                ] );
            }else{
                $update  = DB::table( 'patchquery' )
                ->where( 'patchquery_id', '=', $request->patchquery_id )
                ->update( [
                    'patchquerystatus_id' 	        => $request->patchquerystatus_id,
                    'patchquery_shipmentamount' 	=> $request->patchquery_shipmentamount,
                    'updated_by'		            => $request->user_id,
                    'updated_by'		            => date( 'Y-m-d h:i:s' ),
                ] );
            }
        }else{
            $update  = DB::table( 'patchquery' )
            ->where( 'patchquery_id', '=', $request->patchquery_id )
            ->update( [
                'patchquerystatus_id' 	=> $request->patchquerystatus_id,
                'updated_by'		    => $request->user_id,
                'updated_by'		    => date( 'Y-m-d h:i:s' ),
            ] );
        }
        
        if ( $update ) {
            return response()->json( [ 'message' => 'Moved Successfully' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function updatepatchquery( Request $request ) {
        if($request->role_id == 6){
            $validate = Validator::make( $request->all(), [
                'patchquery_clientname' 		    	=> 'required',
                'patchquery_clientemail'	   			=> 'required',
                'patchquery_clientphone'		    	=> 'required',
                'patchquery_clientzip'		    		=> 'required',
                'country_id'		    				=> 'required',
                'state_id'								=> 'required',
                'patchquery_clientaddress'		    	=> 'required',
                'patchquery_clientbussinessname'		=> 'required',
                'patchquery_clientbussinessemail'		=> 'required',
                'patchquery_clientbussinesswebsite'		=> 'required',
                'patchquery_clientbussinessphone'		=> 'required',
                'patchquery_title'						=> 'required',
                'patchquery_shippingaddress'			=> 'required',
                'patchquery_otherdetails'				=> 'required',
            ] );
            if ( $validate->fails() ) {
                return response()->json( $validate->errors(), 400 );
            }
            $updatequery  = DB::table( 'patchquery' )
            ->where( 'patchquery_id', '=', $request->patchquery_id )
            ->update( [
                'patchquery_clientname' 			=> $request->patchquery_clientname,
                'patchquery_clientemail' 			=> $request->patchquery_clientemail,
                'patchquery_clientphone' 			=> $request->patchquery_clientphone,
                'patchquery_clientzip' 				=> $request->patchquery_clientzip,
                'country_id' 						=> $request->country_id,
                'state_id' 							=> $request->state_id,
                'patchquery_clientaddress' 			=> $request->patchquery_clientaddress,
                'patchquery_clientbussinessname' 	=> $request->patchquery_clientbussinessname,
                'patchquery_clientbussinessemail' 	=> $request->patchquery_clientbussinessemail,
                'patchquery_clientbussinesswebsite' => $request->patchquery_clientbussinesswebsite,
                'patchquery_clientbussinessphone'	=> $request->patchquery_clientbussinessphone,
                'patchquery_title' 					=> $request->patchquery_title,
                'patchquery_shippingaddress'		=> $request->patchquery_shippingaddress,
                'patchquery_otherdetails'			=> $request->patchquery_otherdetails,
                'patchqueryshipping_id'			    => $request->patchqueryshipping_id,
                'vendordelivery_id' 	            => $request->vendordelivery_id,
                'patchquerystatus_id'				=> $request->patchquerystatus_id,
                'updated_by'	 		    		=> $request->user_id,
                'updated_at'	 		    		=> date( 'Y-m-d h:i:s' ),
            ]);
        }
        $validate = Validator::make( $request->all(), [
            'patchqueryitem'	=> 'required',
        ] );
        if ( $validate->fails() ) {
            return response()->json( $validate->errors(), 400 );
        }
        $patchqueryitem = $request->patchqueryitem;
        foreach ( $patchqueryitem as $patchqueryitems ) {
            if($request->role_id == 6){
                DB::table( 'patchqueryitem' )
                ->where( 'patchqueryitem_id', '=', $patchqueryitems[ 'patchqueryitem_id' ] )
                ->update( [
                    'patchquerycategory_id' 		=> $patchqueryitems[ 'patchquerycategory_id' ],
                    'patchqueryitem_quantity' 		=> $patchqueryitems[ 'patchqueryitem_quantity' ],
                    'patchqueryitem_height' 		=> $patchqueryitems[ 'patchqueryitem_height' ],
                    'patchqueryitem_width'			=> $patchqueryitems[ 'patchqueryitem_width' ],
                    'patchtype_id'					=> $patchqueryitems[ 'patchtype_id' ],
                    'patchback_id'					=> $patchqueryitems[ 'patchback_id' ],
                    'patchqueryitem_marketcost'		=> $patchqueryitems[ 'patchqueryitem_marketcost' ],
                    'patchqueryitem_otherdetails'	=> $patchqueryitems[ 'patchqueryitem_otherdetails' ],
                    'updated_by'					=> $request->user_id,
                    'updated_at'					=> date( 'Y-m-d h:i:s' ),
                ]);
                if ( isset( $patchqueryitems[ 'patchqueryitem_costattachment' ] ) ) {
                    $costattachment = $patchqueryitems[ 'patchqueryitem_costattachment' ];
                    if ( $costattachment->isValid() ) {
                        $number = rand( 1, 999 );
                        $numb = $number / 7 ;
                        $foldername = $request->patchquery_id;
                        $extension = $costattachment->getClientOriginalExtension();
                        $costattachmentname = $numb.$costattachment->getClientOriginalName();
                        $costattachmentname = $costattachment->move( public_path( 'patchquerycostattachment/'.$foldername ), $costattachmentname );
                        $costattachmentname = $numb.$costattachment->getClientOriginalName();
                        DB::table( 'patchqueryitem' )
                        ->where( 'patchqueryitem_id', '=', $patchqueryitems[ 'patchqueryitem_id' ] )
                        ->update( [
                            'patchqueryitem_costattachment'	=> $costattachmentname,
                            'updated_by'					=> $request->user_id,
                            'updated_at'					=> date( 'Y-m-d h:i:s' ),
                        ]);
                    } else {
                        return response()->json( 'Invalid File', 400 );
                    }
                }
                if($request->patchquerystatus_id == 2){
                    if ( isset( $patchqueryitems[ 'vendorproduction_id' ] ) ) {
                        $vendor = $patchqueryitems[ 'vendorproduction_id' ];
                        foreach ( $vendor as $vendors ) {
                            $savevandor = array(
                                'vendorproduction_id'	=> $vendors,
                                'patchqueryitem_id'		=> $patchqueryitems[ 'patchqueryitem_id' ],
                                'patchquery_id'			=> $request->patchquery_id,
                                'status_id' 			=> 1,
                            );
                            DB::table( 'patchqueryvendor' )->insert( $savevandor );
                        }
                    }
                    DB::table( 'patchquery' )
                    ->where( 'patchquery_id', '=', $request->patchquery_id )
                    ->update( [
                        'patchquery_dollarrate'		        => $request->patchquery_dollarrate,
                    ]);
                }
            }
            if($request->patchquerystatus_id == 3){
                if ( isset( $patchqueryitems[ 'patchqueryitemvendor'] ) ) {
                    $itemvendor = $patchqueryitems[ 'patchqueryitemvendor'];
                    foreach ( $itemvendor as $itemvendors ) {
                        $proposalattachment = $itemvendors['proposalattachment'];
                        $proposalname;
                        if ( $proposalattachment->isValid() ) {
                            $number = rand( 1, 999 );
                            $numb = $number / 7 ;
                            $foldername = $request->patchquery_id;
                            $extension = $proposalattachment->getClientOriginalExtension();
                            $proposalname = $proposalattachment->getClientOriginalName();
                            $proposalname = $proposalattachment->move( public_path( 'patchqueryproposal/'.$foldername ), $proposalname );
                            $proposalname = $proposalattachment->getClientOriginalName();
                        } else {
                            return response()->json( 'Invalid File', 400 );
                        }
                        DB::table( 'patchqueryvendor' )
                        ->where( 'patchqueryvendor_id', '=', $itemvendors['patchqueryvendor_id'] )
                        ->update( [
                            'patchqueryvendor_cost'		        => $itemvendors[ 'patchqueryvendor_cost' ],
                            'patchqueryvendor_productiondays'	=> $itemvendors[ 'patchqueryvendor_productiondays' ],
                            'patchqueryvendor_proposal'	        => $proposalname,
                        ]);
                        $updatequery  = DB::table( 'patchquery' )
                        ->where( 'patchquery_id', '=', $request->patchquery_id )
                        ->update( [
                            'patchquery_shipmentamount'	=> $request->patchquery_shipmentamount,
                            'patchquery_shipmentinvoiceamount'	=> $request->patchquery_shipmentinvoiceamount,
                            'patchquerystatus_id'	=> $request->patchquerystatus_id,
                        ]);
                    }
                }
            }
            if($request->patchquerystatus_id == 5){
                if ( isset( $request->patchqueryitem_discount ) ) {
                    foreach($request->patchqueryitem_discount as $discounts){
                        $updatequery  = DB::table( 'patchqueryitem' )
                        ->where( 'patchqueryitem_id', '=', $discounts )
                        ->update( [
                            'patchqueryitem_discount'	=> 1,
                        ]);
                    }
                    if ( isset( $patchqueryitems[ 'patchqueryitem_finalvendor' ] ) ) {
                        $updatequery  = DB::table( 'patchqueryitem' )
                        ->where( 'patchqueryitem_id', '=', $patchqueryitems[ 'patchqueryitem_id' ] )
                        ->update( [
                            'patchqueryitem_proposalquote'	=> $patchqueryitems[ 'patchqueryitem_proposalquote' ],
                            'patchqueryitem_finalvendor'	=> $patchqueryitems[ 'patchqueryitem_finalvendor' ],
                        ]);
                        $updatequery  = DB::table( 'patchquery' )
                        ->where( 'patchquery_id', '=', $request->patchquery_id )
                        ->update( [
                            'patchquerystatus_id'	=> 3,
                            'patchquery_discount'	=> $request->patchquery_discount,
                            'patchquery_shipmentamount'	=> $request->patchquery_shipmentamount,
                            'patchquery_shipmentinvoiceamount'	=> $request->patchquery_shipmentinvoiceamount,
                        ]);
                    }
                }else{
                    if ( isset( $patchqueryitems[ 'patchqueryitem_finalvendor' ] ) ) {
                        $updatequery  = DB::table( 'patchqueryitem' )
                        ->where( 'patchqueryitem_id', '=', $patchqueryitems[ 'patchqueryitem_id' ] )
                        ->update( [
                            'patchqueryitem_proposalquote'	=> $patchqueryitems[ 'patchqueryitem_proposalquote' ],
                            'patchqueryitem_finalvendor'	=> $patchqueryitems[ 'patchqueryitem_finalvendor' ],
                        ]);
                        $updatequery  = DB::table( 'patchquery' )
                        ->where( 'patchquery_id', '=', $request->patchquery_id )
                        ->update( [
                            'patchquery_shipmentamount'	=> $request->patchquery_shipmentamount,
                            'patchquery_shipmentinvoiceamount'	=> $request->patchquery_shipmentinvoiceamount,
                        ]);
                    }
                }
            }
            if ( isset( $patchqueryitems[ 'attachment' ] ) ) {
                $attachment = $patchqueryitems[ 'attachment' ];
                $index = 0 ;
                $filename = array();
                foreach ( $attachment as $attachments ) {
                    $saveattachment = array();
                    if ( $attachments->isValid() ) {
                        $number = rand( 1, 999 );
                        $numb = $number / 7 ;
                        $foldername = $patchquery_id;
                        $extension = $attachments->getClientOriginalExtension();
                        $filename = $attachments->getClientOriginalName();
                        $filename = $attachments->move( public_path( 'patchqueryitem/'.$foldername ), $filename );
                        $filename = $attachments->getClientOriginalName();
                        $saveattachment = array(
                            'patchqueryitemattachment_name'	=> $filename,
                            'patchqueryitem_id'				=> $patchqueryitem_id,
							'patchquery_id'					=> $patchquery_id,
                            'status_id' 					=> 1,
                            'created_by'					=> $request->user_id,
                            'created_at'					=> date( 'Y-m-d h:i:s' ),
                        );
                    } else {
                        return response()->json( 'Invalid File', 400 );
                    }
                    DB::table( 'patchqueryitemattachment' )->insert( $saveattachment );
                }
            }
        }
        if( isset( $request->patchproposal_stiches ) ) {
            DB::table( 'patchproposal' )
            ->where( 'patchquery_id', '=', $request->patchquery_id )
            ->update( [
                'patchproposal_stiches' 		=> $request->patchproposal_stiches,
                'patchproposal_colors'			=> $request->patchproposal_colors,
                'patchproposal_colorchanges' 	=> $request->patchproposal_colorchanges,
                'patchproposal_stops'			=> $request->patchproposal_stops,
                'patchproposal_machine'			=> $request->patchproposal_machine,
                'patchproposal_trims'	        => $request->patchproposal_trims,
                'created_by'			    	=> $request->user_id,
                'created_at'			    	=> date( 'Y-m-d h:i:s' ),
            ] );
        }
        if ( isset( $request->patchqueryattachment ) ) {
            $patchqueryattachment = $request->patchqueryattachment;
            $index = 0 ;
            $filename = array();
            foreach ( $patchqueryattachment as $patchqueryattachments ) {
                $saveattachment = array();
                if ( $patchqueryattachments->isValid() ) {
                    $number = rand( 1, 999 );
                    $numb = $number / 7 ;
                    $foldername = $request->patchquery_id;
                    $extension = $patchqueryattachments->getClientOriginalExtension();
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $filename = $patchqueryattachments->move( public_path( 'patchquery/'.$foldername ), $filename );
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $saveattachment = array(
                        'patchqueryattachment_name'		=> $filename,
                        'patchquery_id'					=> $request->patchquery_id,
                        'patchqueryattachmenttype_id' 	=> 2,
                        'status_id' 			   		=> 1,
                        'created_by'			    	=> $request->user_id,
                        'created_at'			    	=> date( 'Y-m-d h:i:s' ),
                    );
                } else {
                    return response()->json( 'Invalid File', 400 );
                }
                DB::table( 'patchqueryattachment' )->insert( $saveattachment );
            }
        }
        return response()->json( [ 'message' => 'Updated Successfully' ], 200 );
    }

    public function deletepatchquery( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_id'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( 'Patch Query Id Required', 400 );
        }
        $updateuserstatus  = DB::table( 'patchquery' )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->update( [
            'status_id' 	=> 2,
            'deleted_by'	=> $request->user_id,
            'deleted_at'	=> date( 'Y-m-d h:i:s' ),
        ] );

        if ( $updateuserstatus ) {
            return response()->json( [ 'message' => 'Patch Query Deleted Successfully' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function deletepatchqueryitem( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchqueryitem_id'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( 'Patch Query Item Id Required', 400 );
        }
        $updateuserstatus  = DB::table( 'patchqueryitem' )
        ->where( 'patchqueryitem_id', '=', $request->patchqueryitem_id )
        ->update( [
            'status_id' 	=> 2,
            'deleted_by'	=> $request->user_id,
            'deleted_at'	=> date( 'Y-m-d h:i:s' ),
        ] );

        if ( $updateuserstatus ) {
            return response()->json( [ 'message' => 'Patch Query Item Deleted Successfully' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function savepatchqueryfollowup( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchqueryfollowup_comment'	=> 'required',
            'patchquery_id'				    => 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $adds = array(
            'patchqueryfollowup_comment' 	=> $request->patchqueryfollowup_comment,
            'patchquery_id' 				=> $request->patchquery_id,
            'status_id'		 				=> 1,
            'created_by'	 				=> $request->user_id,
            'created_at'	 				=> date( 'Y-m-d h:i:s' ),
        );
        $save = DB::table( 'patchqueryfollowup' )->insert( $adds );
        if ( $save ) {
            return response()->json( [ 'message' => 'Followup Saved Successfully' ], 200 );
        } else {
            return response()->json( 'Oops! Something went wrong', 400 );
        }
    }

    public function patchqueryfollowuplist( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_id'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $followups = DB::table( 'patchqueryfollowupdetail' )
        ->select( '*' )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->where( 'status_id', '=', 1 )
        ->get();
        if ( $followups ) {
            return response()->json( [ 'data' => $followups, 'message' => 'Followup List' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function patchqueryandleaddetails( Request $request ) {
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
        $lead = DB::table( 'leadcompletedetails' )
        ->select( '*' )
        ->where( 'lead_email', '=', $data->patchquery_clientemail )
        ->where( 'brand_id', '=', $data->brand_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        if ( $data ) {
            return response()->json( [ 'data' => $data, 'lead' => $lead, 'message' => 'Patch Query And Lead Details' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function pickpatchquery( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_id'			=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( 'Patch Query Id Required', 400 );
        }
        $update  = DB::table( 'patchquery' )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->update( [
            'patchquery_manager'	=> $request->user_id,
            'patchquerystatus_id'	=> 9,
        ] );

        if ( $update ) {
            return response()->json( [ 'message' => 'Query Pick Successfully' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function unpickpatchquery( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_id'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( 'Patch Query Id Required', 400 );
        }
        $update  = DB::table( 'patchquery' )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->update( [
            'patchquery_manager'	=> null,
            'patchquerystatus_id'	=> 1,
        ] );

        if ( $update ) {
            return response()->json( [ 'message' => 'Query Un-Pick Successfully' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }

    public function patchqueryinvoicedetails( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_id'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $brand = DB::table('patchquery')
		->where( 'patchquery_id', '=', $request->patchquery_id )
		->select('brand_id')
		->first();
        $getbranddetail = DB::table('brand')
		->where('brand_id','=',$brand->brand_id)
		->select('brand_cover','brand_email','brand_website','brand_invoicename','brand_currency')
		->first();
        $sumquoteamount = DB::table( 'patchqueryitem' )
        ->select( 'patchqueryitem_proposalquote' )
        ->where( 'status_id', '=', 1 )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->sum('patchqueryitem_proposalquote');
        
		$coverpath = URL::to('/')."/public/brand_cover/";
		$invoiceinfo = array(
			'brand_email' 			=> $getbranddetail->brand_email,
			'brand_website' 		=> $getbranddetail->brand_website,
			'brand_invoicename' 	=> $getbranddetail->brand_invoicename,
            'sumquoteamount' 	=> $sumquoteamount,
			'brand_currency' 		=> $getbranddetail->brand_currency == 1 ? "$" : " £",
			'brand_cover' 			=> $getbranddetail->brand_cover,
			'brand_coverpath' 		=> $coverpath,
		);
        $data = DB::table( 'patchquerydetails' )
        ->select( '*' )
        ->where( 'status_id', '=', 1 )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->first();
        $itemdetails = DB::table( 'patchqueryitemdetails' )
        ->select( '*' )
        ->where( 'status_id', '=', 1 )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->get();
        $patchcallpath = URL::to( '/' ).'/public/patchlogo/call.png';
        $patchwebpath = URL::to( '/' ).'/public/patchlogo/web.png';
        $patchlocationpath = URL::to( '/' ).'/public/patchlogo/location.png';
        $data->call = $patchcallpath;
        $data->web = $patchwebpath;
        $data->location = $patchlocationpath;
        if ( $data ) {
            return response()->json( [ 'data' => $data, 'itemdetails' => $itemdetails, 'invoiceinfo' => $invoiceinfo , 'message' => 'Patch Query Invoice Details' ], 200 );
        } else {
            return response()->json( 'Oops! Something Went Wrong', 400 );
        }
    }
    public function validatepatchquery( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_clientname' 		    	=> 'required',
            'patchquery_clientemail'	   			=> 'required',
            'patchquery_clientphone'		    	=> 'required',
            'patchquery_clientzip'		    		=> 'required',
            'country_id'		    				=> 'required',
            'state_id'								=> 'required',
            'patchquery_clientaddress'		    	=> 'required',
            'patchquery_clientbussinessname'		=> 'required',
            'patchquery_clientbussinessemail'		=> 'required',
            'patchquery_clientbussinesswebsite'		=> 'required',
            'patchquery_clientbussinessphone'		=> 'required',
            'patchquery_title'						=> 'required',
            'patchquery_medium'						=> 'required',
            'patchquery_clientbudget'				=> 'required',
            'patchquery_shippingaddress'			=> 'required',
            'patchquery_otherdetails'				=> 'required',
            'brand_id'								=> 'required',
            'patchqueryitem'	    				=> 'required',
        ] );
        if ( $validate->fails() ) {
            return response()->json( $validate->errors(), 400 );
        }
        try {
            $patchqueryitem = $request->patchqueryitem;
            foreach ( $patchqueryitem as $patchqueryitems ) {
                $basic = array(
                    'patchquerycategory_id' 		=> $patchqueryitems[ 'patchquerycategory_id' ],
                    'patchqueryitem_quantity' 		=> $patchqueryitems[ 'patchqueryitem_quantity' ],
                    'patchqueryitem_height' 		=> $patchqueryitems[ 'patchqueryitem_height' ],
                    'patchqueryitem_width'			=> $patchqueryitems[ 'patchqueryitem_width' ],
                    'patchtype_id'					=> $patchqueryitems[ 'patchtype_id' ],
                    'patchback_id'					=> $patchqueryitems[ 'patchback_id' ],
                    'patchqueryitem_otherdetails'	=> $patchqueryitems[ 'patchqueryitem_otherdetails' ],
                );
            }
            return response()->json( [ 'message' => 'Validate Successfully' ], 200 );
        }catch (\Exception $e) {
            return response()->json( 'Fill All Fields To Submit ', 400 );
		}
    }
    public function patchtype( Request $request ) {
        $data = DB::table( 'patchtype' )
        ->select( '*' )
        ->where( 'status_id', '=', 1 )
        ->get();
        if ( isset( $data ) ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patches Type' ], 200 );
        } else {
            return response()->json( [ 'data' => $emptyarray, 'message' => 'Patches Type' ], 200 );
        }
    }

    public function patchback( Request $request ) {
        $data = DB::table( 'patchback' )
        ->select( '*' )
        ->where( 'status_id', '=', 1 )
        ->get();
        if ( isset( $data ) ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patches Back' ], 200 );
        } else {
            return response()->json( [ 'data' => $emptyarray, 'message' => 'Patches Back' ], 200 );
        }
    }

    public function patchclientlist( Request $request ) {
        if ( $request->role_id <= 2 ) {
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientname', 'patchquery_clientemail', 'patchquery_clientphone', 'patchquery_clientbussinessname', 'patchquery_clientbussinessemail', 'patchquery_clientbussinesswebsite','patchquery_clientbussinessphone','user_name' )
            ->whereIn( 'patchquerystatus_id', [10,11,12] )
            ->where( 'patchquery_isorderorsample','=', "order" )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->groupBy( 'patchquery_clientemail' )
            ->paginate( 30 );
        } elseif ( $request->role_id == 3 ) {
            $brand = DB::table( 'userbarnd' )
            ->select( 'brand_id' )
            ->where( 'user_id', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->get();
            $sortbrand = array();
            foreach ( $brand as $brands ) {
                $sortbrand[] = $brands->brand_id;
            }
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientname', 'patchquery_clientemail', 'patchquery_clientphone', 'patchquery_clientbussinessname', 'patchquery_clientbussinessemail', 'patchquery_clientbussinesswebsite','patchquery_clientbussinessphone','user_name' )
            ->whereIn( 'patchquerystatus_id', [10,11,12] )
            ->where( 'patchquery_isorderorsample','=', "order" )
            ->whereIn( 'brand_id', $sortbrand )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->groupBy( 'patchquery_clientemail' )
            ->paginate( 30 );
        } elseif ( $request->role_id == 6 ) {
            $data = DB::table( 'patchquerylist' )
            ->select( 'patchquery_id', 'patchquery_clientname', 'patchquery_clientemail', 'patchquery_clientphone', 'patchquery_clientbussinessname', 'patchquery_clientbussinessemail', 'patchquery_clientbussinesswebsite','patchquery_clientbussinessphone','user_name' )
            ->whereIn( 'patchquerystatus_id', [10,11,12] )
            ->where( 'patchquery_isorderorsample','=', "order" )
            ->where( 'patchquery_manager', '=', $request->user_id )
            ->where( 'status_id', '=', 1 )
            ->orderBy( 'patchquery_id', 'DESC' )
            ->groupBy( 'patchquery_clientemail' )
            ->paginate( 30 );
        }
        if ( isset( $data ) ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patch Client List' ], 200 );
        } else {
            return response()->json( [ 'data' => $emptyarray, 'message' => 'Patch Client List' ], 200 );
        }
    }

    public function clientwisequerylist( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_id'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $personalinfo = DB::table( 'patchquerylist' )
        ->select( 'patchquery_id', 'patchquery_clientname', 'patchquery_clientemail', 'patchquery_clientphone', 'patchquery_clientbussinessname', 'patchquery_clientbussinessemail', 'patchquery_clientbussinesswebsite','patchquery_clientbussinessphone','user_name' )
        ->where( 'patchquery_id','=', $request->patchquery_id )
        ->where( 'status_id', '=', 1 )
        ->first();
        $data = DB::table( 'patchquerylist' )
        ->select( 'patchquery_id', 'patchquery_clientemail', 'patchquery_title', 'patchquery_date', 'patchquery_clientbudget', 'patchquery_islead', 'patchquerystatus_id','patchquerystatus_name','user_name' )
        ->where( 'patchquery_clientemail','=', $personalinfo->patchquery_clientemail )
        ->where( 'status_id', '=', 1 )
        ->orderBy( 'patchquery_id', 'DESC' )
        ->paginate( 30 );
        $personalinfo = DB::table( 'patchquerydetails' )
        ->select( 'patchquery_id', 'patchquery_clientname', 'patchquery_clientemail', 'patchquery_clientphone', 'patchquery_clientbussinessname', 'patchquery_clientbussinessemail', 'patchquery_clientbussinesswebsite','patchquery_clientbussinessphone','patchquery_clientzip', 'state_name', 'country_name', 'patchquery_clientaddress' )
        ->where( 'patchquery_clientemail','=', $personalinfo->patchquery_clientemail )
        ->where( 'status_id', '=', 1 )
        ->first();
        $totalquerycount = DB::table( 'patchquerylist' )
        ->select( 'patchquery_id')
        ->where( 'patchquery_clientemail','=', $personalinfo->patchquery_clientemail )
        ->where( 'status_id', '=', 1 )
        ->count();
        $paidquerycount = DB::table( 'patchquerylist' )
        ->select( 'patchquery_id')
        ->whereIn( 'patchquerystatus_id', [10,11,12] )
        ->where( 'patchquery_clientemail','=', $personalinfo->patchquery_clientemail )
        ->where( 'status_id', '=', 1 )
        ->count();
        $gettotalqueryid = DB::table( 'patchquerylist' )
        ->select( 'patchquery_id')
        ->where( 'patchquery_clientemail','=', $personalinfo->patchquery_clientemail )
        ->where( 'status_id', '=', 1 )
        ->get();
        $totalqueryid = array();
        foreach($gettotalqueryid as $gettotalqueryids){
            $totalqueryid[] = $gettotalqueryids->patchquery_id;
        }
        $getpaidqueryid = DB::table( 'patchquerylist' )
        ->select( 'patchquery_id')
        ->where( 'patchquery_clientemail','=', $personalinfo->patchquery_clientemail )
        ->whereIn( 'patchquerystatus_id', [10,11,12] )
        ->where( 'status_id', '=', 1 )
        ->get();
        $paidqueryid = array();
        foreach($gettotalqueryid as $gettotalqueryids){
            $paidqueryid[] = $gettotalqueryids->patchquery_id;
        }
        $sumtotalquery = DB::table( 'patchqueryitem' )
        ->select( 'patchqueryitem_proposalquote')
        ->whereIn( 'patchquery_id', $totalqueryid )
        ->where( 'status_id', '=', 1 )
        ->sum('patchqueryitem_proposalquote');
        $sumpaidquery = DB::table( 'patchqueryitem' )
        ->select( 'patchqueryitem_proposalquote')
        ->whereIn( 'patchquery_id', $paidqueryid )
        ->where( 'status_id', '=', 1 )
        ->sum('patchqueryitem_proposalquote');
        $stats['totalcount'] = $totalquerycount;
        $stats['paidcount'] = $paidquerycount;
        $stats['sumtotal'] = $sumtotalquery;
        $stats['sumpaid'] = $sumpaidquery;
     
        if ( isset( $data ) ) {
            return response()->json( [ 'data' => $data, 'personalinfo' => $personalinfo, 'stats' => $stats, 'message' => 'Patch Client Query List' ], 200 );
        } else {
            return response()->json( [ 'data' => $emptyarray, 'message' => 'Patch Client Query List' ], 200 );
        }
    }
    public function productionvendorquerydetails(Request $request){
        $validate = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'yearmonth'	=> 'required',
        ]); 
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
        $yearmonth = explode( '-', $request->yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $index = 0;
        $data = DB::table('patchqueryanditem')
        ->select('*')
        ->where('patchqueryitem_finalvendor','=',$request->vendor_id)
        ->where('patchquery_date','like', $setyearmonth.'%')
        ->where('status_id','=',1)
        ->get();
        foreach($data as $datas){
            $cost = DB::table('patchqueryvendor')
            ->select('patchqueryvendor_cost')
            ->where('vendorproduction_id','=',$request->vendor_id)
            ->where('patchqueryitem_id','=',$datas->patchqueryitem_id)
            ->where('status_id','=',1)
            ->sum('patchqueryvendor_cost');
            $paid = DB::table('patchpayment')
            ->select('patchpayment_amount')
            ->where('patchpaymenttype_id','=',1)
            ->where('patch_id','=',$datas->patchqueryitem_id)
            ->where('status_id','=',1)
            ->sum('patchpayment_amount');
            $remaining = $cost-$paid;
            $datas->cost = $cost;
            $datas->paid = $paid;
            $datas->remaining = $remaining;
            $data[$index] = $datas;
            $index++;
        }
		if($data){
			return response()->json(['data' => $data, 'message' => 'Production Vendor Patch Query Details'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Production Vendor Patch Query Details'],200);
		}
	}
    public function shippingvendorquerydetails(Request $request){
        $validate = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'yearmonth'	=> 'required',
        ]); 
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
        $yearmonth = explode( '-', $request->yearmonth );
        if ( $yearmonth[ 1 ] <= 9 ) {
            $setyearmonth = $yearmonth[ 0 ].'-0'.$yearmonth[ 1 ];
        } else {
            $setyearmonth = $yearmonth[ 0 ].'-'.$yearmonth[ 1 ];
        }
        $index = 0;
        $data = DB::table('patchquery')
        ->select('*')
        ->where('vendordelivery_id','=',$request->vendor_id)
        ->where('patchquery_date','like', $setyearmonth.'%')
        ->where('status_id','=',1)
        ->get();
        foreach($data as $datas){
            $cost = DB::table('patchquery')
            ->select('patchquery_shipmentamount')
            ->where('vendordelivery_id','=',$request->vendor_id)
            ->where('patchquery_id','=',$datas->patchquery_id)
            ->where('status_id','=',1)
            ->sum('patchquery_shipmentamount');
            $paid = DB::table('patchpayment')
            ->select('patchpayment_amount')
            ->where('patchpaymenttype_id','=',2)
            ->where('patch_id','=',$datas->patchquery_id)
            ->where('status_id','=',1)
            ->sum('patchpayment_amount');
            $remaining = $cost-$paid;
            $datas->cost = $cost;
            $datas->paid = $paid;
            $datas->remaining = $remaining;
            $data[$index] = $datas;
            $index++;
        }
		if($data){
			return response()->json(['data' => $data, 'message' => 'Shipping Vendor Patch Query Details'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Shipping Vendor Patch Query Details'],200);
		}
	}
    public function patchquerydiscount( Request $request ) {
        $validate = Validator::make($request->all(), [
            'patchqueryitem_id'                => 'required',
            'patchquery_id'                    => 'required',
            'patchqueryitem_discount'          => 'required',
        ]); 
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
        if($request->patchqueryitem_discount == 2){
            $validate = Validator::make($request->all(), [
                'patchqueryitem_proposalquote'     => 'required',
            ]); 
            if ($validate->fails()) {
                return response()->json($validate->errors(), 400);
            }
            $save = DB::table( 'patchqueryitem' )
            ->where( 'patchqueryitem_id', '=', $request->patchqueryitem_id )
            ->update( [
                'patchqueryitem_proposalquote'	=> $request->patchqueryitem_proposalquote,
                'patchqueryitem_discount'	    => 2,
            ]);
        }else{
            $save = DB::table( 'patchqueryitem' )
            ->where( 'patchqueryitem_id', '=', $request->patchqueryitem_id )
            ->update( [
               'patchqueryitem_discount'	    => 3,
            ]);
        }
        $discountcount = DB::table('patchqueryitem')
        ->select('patchqueryitem_id')
        ->where('patchqueryitem_discount','=',1)
        ->where('patchquery_id','=',$request->patchquery_id)
        ->where('status_id','=',1)
        ->count();
        if($discountcount == 0){
            DB::table( 'patchquery' )
            ->where( 'patchquery_id', '=', $request->patchquery_id )
            ->update( [
                'patchquery_discount'	=> 2,
            ]);
        }
        if ( isset( $save ) ) {
            return response()->json( ['message' => 'Patch Query Discount Updated' ], 200 );
        } else {
            return response()->json( [ 'message' => 'Patch Query Discount Updated' ], 200 );
        }
    }
    public function patchquerydiscountlist( Request $request ) {
        $data = DB::table('patchquerylist')
        ->select('*')
        ->where('patchquery_discount','=',1)
        ->where('status_id','=',1)
        ->get();
        if ( isset( $data ) ) {
            return response()->json( [ 'data' => $data, 'message' => 'Patch Discount Query List' ], 200 );
        } else {
            return response()->json( [ 'data' => $emptyarray, 'message' => 'Patch Discount Query List' ], 200 );
        }
    }
    public function patchquotationhistory(Request $request){
        $validate = Validator::make($request->all(), [
            'patchquerycategory_id'    => 'required',
        ]); 
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }
        $data = DB::table('patchqueryanditem')
        ->select('patchquery_title','patchqueryitem_quantity','patchqueryitem_proposalquote','patchquery_date','patchqueryitem_finalvendor')
        ->where('patchquerycategory_id','=',$request->patchquerycategory_id)
        ->where('patchqueryitem_proposalquote','!=', NULL)
        ->orderBy( 'patchqueryitem_id', 'DESC' )
        ->limit(5)
        ->get();
        $sortdata = array();
        $index=0;
        foreach($data as $datas){
            $vendordetail = DB::table('patchqueryitemvendordetails')
            ->select('patchqueryvendor_cost','vendor_name')
            ->where('vendorproduction_id','=',$datas->patchqueryitem_finalvendor)
            ->first();
            $datas->cost = $vendordetail->patchqueryvendor_cost;
            $datas->vendorname = $vendordetail->vendor_name;
            $sortdata[$index] = $datas;
            $index++;
		}
		if($sortdata){
			return response()->json(['data' => $sortdata, 'message' => 'Patch Quotaion History'],200);
		}else{
			$emptyarray = array();
			return response()->json(['data' => $emptyarray,'message' => 'Expense Report'],200);
		}
    }
    public function savepatchcategory( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquerycategory_name'	=> 'required',
        ] );
        if ( $validate->fails() ) {

            return response()->json( $validate->errors(), 400 );
        }
        $adds = array(
            'patchquerycategory_name' 	=> $request->patchquerycategory_name,
            'status_id'		 			=> 1,
        );
        $save = DB::table( 'patchquerycategory' )->insert( $adds );
        if ( $save ) {
            return response()->json( [ 'message' => 'Saved Successfully' ], 200 );
        } else {
            return response()->json( 'Oops! Something went wrong', 400 );
        }
    }
    public function testupdatepatchquery( Request $request ) {
        if($request->role_id == 6){
            $validate = Validator::make( $request->all(), [
                'patchquery_clientname' 		    	=> 'required',
                'patchquery_clientemail'	   			=> 'required',
                'patchquery_clientphone'		    	=> 'required',
                'patchquery_clientzip'		    		=> 'required',
                'country_id'		    				=> 'required',
                'state_id'								=> 'required',
                'patchquery_clientaddress'		    	=> 'required',
                'patchquery_clientbussinessname'		=> 'required',
                'patchquery_clientbussinessemail'		=> 'required',
                'patchquery_clientbussinesswebsite'		=> 'required',
                'patchquery_clientbussinessphone'		=> 'required',
                'patchquery_title'						=> 'required',
                'patchquery_shippingaddress'			=> 'required',
                'patchquery_otherdetails'				=> 'required',
            ] );
            if ( $validate->fails() ) {
                return response()->json( $validate->errors(), 400 );
            }
            $updatequery  = DB::table( 'patchquery' )
            ->where( 'patchquery_id', '=', $request->patchquery_id )
            ->update( [
                'patchquery_clientname' 			=> $request->patchquery_clientname,
                'patchquery_clientemail' 			=> $request->patchquery_clientemail,
                'patchquery_clientphone' 			=> $request->patchquery_clientphone,
                'patchquery_clientzip' 				=> $request->patchquery_clientzip,
                'country_id' 						=> $request->country_id,
                'state_id' 							=> $request->state_id,
                'patchquery_clientaddress' 			=> $request->patchquery_clientaddress,
                'patchquery_clientbussinessname' 	=> $request->patchquery_clientbussinessname,
                'patchquery_clientbussinessemail' 	=> $request->patchquery_clientbussinessemail,
                'patchquery_clientbussinesswebsite' => $request->patchquery_clientbussinesswebsite,
                'patchquery_clientbussinessphone'	=> $request->patchquery_clientbussinessphone,
                'patchquery_title' 					=> $request->patchquery_title,
                'patchquery_shippingaddress'		=> $request->patchquery_shippingaddress,
                'patchquery_otherdetails'			=> $request->patchquery_otherdetails,
                'patchqueryshipping_id'			    => $request->patchqueryshipping_id,
                'vendordelivery_id' 	            => $request->vendordelivery_id,
                'patchquerystatus_id'				=> $request->patchquerystatus_id,
                'updated_by'	 		    		=> $request->user_id,
                'updated_at'	 		    		=> date( 'Y-m-d h:i:s' ),
            ]);
        }
        $validate = Validator::make( $request->all(), [
            'patchqueryitem'	=> 'required',
        ] );
        if ( $validate->fails() ) {
            return response()->json( $validate->errors(), 400 );
        }
        $patchqueryitem = $request->patchqueryitem;
        $iindex=0;
        foreach ( $patchqueryitem as $patchqueryitems ) {
            if($request->role_id == 6 || $request->role_id ==20){
                if ( isset( $patchqueryitems[ 'patchqueryitem_costattachment' ] ) ) {
                    $costattachment = $patchqueryitems[ 'patchqueryitem_costattachment' ];
                    if ( $costattachment->isValid() ) {
                        $number = rand( 1, 999 );
                        $numb = $number / 7 ;
                        $foldername = $request->patchquery_id;
                        $extension = $costattachment->getClientOriginalExtension();
                        $costattachmentname = $numb.$costattachment->getClientOriginalName();
                        $costattachmentname = $costattachment->move( public_path( 'patchquerycostattachment/'.$foldername ), $costattachmentname );
                        $costattachmentname = $numb.$costattachment->getClientOriginalName();
                        DB::table( 'patchqueryitem' )
                        ->where( 'patchqueryitem_id', '=', $patchqueryitems[ 'patchqueryitem_id' ] )
                        ->update( [
                            'patchqueryitem_costattachment'	=> $costattachmentname,
                            'updated_by'					=> $request->user_id,
                            'updated_at'					=> date( 'Y-m-d h:i:s' ),
                        ]);
                    } else {
                        return response()->json( 'Invalid File', 400 );
                    }
                }
                if($request->patchquerystatus_id == 4){
                   $savevandor = array(
                        'vendorproduction_id'	=> $patchqueryitems[ 'vendorproduction_id' ],
                        'patchqueryitem_id'		=> $patchqueryitems[ 'patchqueryitem_id' ],
                        'patchquery_id'			=> $request->patchquery_id,
                        'status_id' 			=> 1,
                    );
                    DB::table( 'patchqueryvendor' )->insert( $savevandor );
                    $patchqueryvendor_id = DB::getPdo()->lastInsertId();
                    $proposalattachment = $patchqueryitems['proposalattachment'];
                    $proposalname;
                    if ( $proposalattachment->isValid() ) {
                        $number = rand( 1, 999 );
                        $numb = $number / 7 ;
                        $foldername = $request->patchquery_id;
                        $extension = $proposalattachment->getClientOriginalExtension();
                        $proposalname = $proposalattachment->getClientOriginalName();
                        $proposalname = $proposalattachment->move( public_path( 'patchqueryproposal/'.$foldername ), $proposalname );
                        $proposalname = $proposalattachment->getClientOriginalName();
                    } else {
                        return response()->json( 'Invalid File', 400 );
                    }
                    DB::table( 'patchqueryvendor' )
                    ->where( 'patchqueryvendor_id', '=', $patchqueryvendor_id )
                    ->update( [
                        'patchqueryvendor_cost'		        => $patchqueryitems[ 'patchqueryvendor_cost' ],
                        'patchqueryvendor_productiondays'	=> $patchqueryitems[ 'patchqueryvendor_productiondays' ],
                        'patchqueryvendor_proposal'	        => $proposalname,
                    ]);
                    $updatequery  = DB::table( 'patchquery' )
                    ->where( 'patchquery_id', '=', $request->patchquery_id )
                    ->update( [
                        'vendordelivery_id' 	            => $request->vendordelivery_id,
                        'patchquery_dollarrate'		        => $request->patchquery_dollarrate,
                        'patchquery_shipmentamount'	        => $request->patchquery_shipmentamount,
                        'patchquery_shipmentinvoiceamount'	=> $request->patchquery_shipmentinvoiceamount,
                        'patchquerystatus_id'	            => $request->patchquerystatus_id,
                    ]);
                    $a = "proposalcost";
                    $b = $iindex;
                    $proposalcost = $a.$b;
                    $proposalcost = $patchqueryitems[ 'patchqueryvendor_cost' ];
                    $dollarrate = $request->patchquery_dollarrate;
                    $e = "itemqty";
                    $f = $iindex;
                    $itemqty = $e.$f;
                    $itemqty = DB::table('patchqueryitem')
                    ->select('patchqueryitem_quantity')
                    ->where('patchqueryitem_id','=',$patchqueryitems[ 'patchqueryitem_id' ])
                    ->sum('patchqueryitem_quantity');
                    $c = "finalproposalcost";
                    $d = $iindex;
                    $finalproposalcost = $c.$d;
                    $finalproposalcost = $proposalcost*$itemqty/$dollarrate;
                    DB::table( 'patchqueryitem' )
                    ->where( 'patchqueryitem_id', '=', $patchqueryitems[ 'patchqueryitem_id' ] )
                    ->update( [
                        'patchqueryitem_finalvendor'	=> $patchqueryitems[ 'vendorproduction_id' ],
                        'patchqueryitem_proposalquote'	=> $finalproposalcost,
                    ]);
                    $iindex++;
                }
            }
            if($request->patchquerystatus_id == 5){
                if ( isset( $request->patchqueryitem_discount ) ) {
                    foreach($request->patchqueryitem_discount as $discounts){
                        $updatequery  = DB::table( 'patchqueryitem' )
                        ->where( 'patchqueryitem_id', '=', $discounts )
                        ->update( [
                            'patchqueryitem_discount'	=> 1,
                        ]);
                    }
                    $updatequery  = DB::table( 'patchquery' )
                    ->where( 'patchquery_id', '=', $request->patchquery_id )
                    ->update( [
                        'patchquerystatus_id'	=> 4,
                        'patchquery_discount'	=> $request->patchquery_discount,
                        'patchquery_discountcomment'	=> $request->patchquery_discountcomment,
                     ]);
                }else{
                    $updatequery  = DB::table( 'patchquery' )
                    ->where( 'patchquery_id', '=', $request->patchquery_id )
                    ->update( [
                        'patchquerystatus_id'	=> $request->patchquerystatus_id,
                    ]);
                }
            }
            if ( isset( $patchqueryitems[ 'attachment' ] ) ) {
                $attachment = $patchqueryitems[ 'attachment' ];
                $index = 0 ;
                $filename = array();
                foreach ( $attachment as $attachments ) {
                    $saveattachment = array();
                    if ( $attachments->isValid() ) {
                        $number = rand( 1, 999 );
                        $numb = $number / 7 ;
                        $foldername = $request->patchquery_id;
                        $extension = $attachments->getClientOriginalExtension();
                        $filename = $attachments->getClientOriginalName();
                        $filename = $attachments->move( public_path( 'patchqueryitem/'.$foldername ), $filename );
                        $filename = $attachments->getClientOriginalName();
                        $saveattachment = array(
                            'patchqueryitemattachment_name'	=> $filename,
                            'patchqueryitem_id'				=> $patchqueryitem_id,
							'patchquery_id'					=> $request->patchquery_id,
                            'status_id' 					=> 1,
                            'created_by'					=> $request->user_id,
                            'created_at'					=> date( 'Y-m-d h:i:s' ),
                        );
                    } else {
                        return response()->json( 'Invalid File', 400 );
                    }
                    DB::table( 'patchqueryitemattachment' )->insert( $saveattachment );
                }
            }
        }
        if( isset( $request->patchproposal_stiches ) ) {
            DB::table( 'patchproposal' )
            ->where( 'patchquery_id', '=', $request->patchquery_id )
            ->update( [
                'patchproposal_stiches' 		=> $request->patchproposal_stiches,
                'patchproposal_colors'			=> $request->patchproposal_colors,
                'patchproposal_colorchanges' 	=> $request->patchproposal_colorchanges,
                'patchproposal_stops'			=> $request->patchproposal_stops,
                'patchproposal_machine'			=> $request->patchproposal_machine,
                'patchproposal_trims'	        => $request->patchproposal_trims,
                'created_by'			    	=> $request->user_id,
                'created_at'			    	=> date( 'Y-m-d h:i:s' ),
            ] );
        }
        if ( isset( $request->patchqueryattachment ) ) {
            $patchqueryattachment = $request->patchqueryattachment;
            $index = 0 ;
            $filename = array();
            foreach ( $patchqueryattachment as $patchqueryattachments ) {
                $saveattachment = array();
                if ( $patchqueryattachments->isValid() ) {
                    $number = rand( 1, 999 );
                    $numb = $number / 7 ;
                    $foldername = $request->patchquery_id;
                    $extension = $patchqueryattachments->getClientOriginalExtension();
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $filename = $patchqueryattachments->move( public_path( 'patchquery/'.$foldername ), $filename );
                    $filename = $patchqueryattachments->getClientOriginalName();
                    $saveattachment = array(
                        'patchqueryattachment_name'		=> $filename,
                        'patchquery_id'					=> $request->patchquery_id,
                        'patchqueryattachmenttype_id' 	=> 2,
                        'status_id' 			   		=> 1,
                        'created_by'			    	=> $request->user_id,
                        'created_at'			    	=> date( 'Y-m-d h:i:s' ),
                    );
                } else {
                    return response()->json( 'Invalid File', 400 );
                }
                DB::table( 'patchqueryattachment' )->insert( $saveattachment );
            }
        }
        return response()->json( [ 'message' => 'Updated Successfully' ], 200 );
    }
    public function patchfilterlist( Request $request ) {
        $data = DB::table('patchfilter')
        ->select('*')
        ->where('status_id','=',1)
        ->get();
        if ( $data ) {
            return response()->json( ['data' => $data, 'message' => 'Patch Filter List' ], 200 );
        } else {
            return response()->json( 'Oops! Something went wrong', 400 );
        }
    }
    public function patchquotationlist( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchfilter_id'	    => 'required',
            'patchquotation_size'	=> 'required',
            'patchquotation_qty'	=> 'required',
        ] );
        if ( $validate->fails() ) {
            return response()->json( $validate->errors(), 400 );
        }
        if($request->patchfilter_id == "-"){
            $data = DB::table('patchquotation')
            ->select('*')
            ->where('status_id','=',1)
            ->get();
        }else{
            $data = DB::table('patchquotation')
            ->select('*')
            ->where('patchfilter_id','=',$request->patchfilter_id)
            ->where('patchquotation_size','=',$request->patchquotation_size)
            ->where('patchquotation_qty','=',$request->patchquotation_qty)
            ->where('status_id','=',1)
            ->get();
        }
        if ( $data ) {
            return response()->json( ['data' => $data, 'message' => 'Patch Quotation List' ], 200 );
        } else {
            return response()->json( 'Oops! Something went wrong', 400 );
        }
    }
    public function searcpatchclient(Request $request){
		$validate = Validator::make($request->all(), [ 
			'search_type'		=> 'required',
			'search_name'		=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$data = DB::table('patchquerylist')
        ->select('patchquery_clientname','patchquery_clientemail','patchquery_clientphone','patchquery_title','patchquery_date','user_name')
        ->where($request->search_type,'like','%'.$request->search_name.'%')
        ->where('status_id','=',1)
        ->first();
		if(isset($data)){
			return response()->json(['data' => $data,'message' => 'Search Patch Client'],200);
		}else{
			return response()->json(['message' => 'Search Patch Client'],200);
		}
	}
    public function adminupdatepatchquery( Request $request ) {
        $validate = Validator::make( $request->all(), [
            'patchquery_clientname' 		    	=> 'required',
            'patchquery_clientemail'	   			=> 'required',
            'patchquery_clientphone'		    	=> 'required',
            'patchquery_clientzip'		    		=> 'required',
            'country_id'		    				=> 'required',
            'state_id'								=> 'required',
            'patchquery_clientaddress'		    	=> 'required',
            'patchquery_clientbussinessname'		=> 'required',
            'patchquery_clientbussinessemail'		=> 'required',
            'patchquery_clientbussinesswebsite'		=> 'required',
            'patchquery_clientbussinessphone'		=> 'required',
            'patchquery_shippingaddress'			=> 'required',
            'patchquery_dollarrate'				    => 'required',
            'patchquery_shipmentamount'				=> 'required',
            'patchquery_shipmentinvoiceamount'		=> 'required',
            'patchqueryshipping_id'		            => 'required',
            'patchqueryitem'	                    => 'required',
        ] );
        if ( $validate->fails() ) {
            return response()->json( $validate->errors(), 400 );
        }
        $updatequery  = DB::table( 'patchquery' )
        ->where( 'patchquery_id', '=', $request->patchquery_id )
        ->update( [
            'patchquery_clientname' 			=> $request->patchquery_clientname,
            'patchquery_clientemail' 			=> $request->patchquery_clientemail,
            'patchquery_clientphone' 			=> $request->patchquery_clientphone,
            'patchquery_clientzip' 				=> $request->patchquery_clientzip,
            'country_id' 						=> $request->country_id,
            'state_id' 							=> $request->state_id,
            'patchquery_clientaddress' 			=> $request->patchquery_clientaddress,
            'patchquery_clientbussinessname' 	=> $request->patchquery_clientbussinessname,
            'patchquery_clientbussinessemail' 	=> $request->patchquery_clientbussinessemail,
            'patchquery_clientbussinesswebsite' => $request->patchquery_clientbussinesswebsite,
            'patchquery_clientbussinessphone'	=> $request->patchquery_clientbussinessphone,
            'patchquery_shippingaddress'		=> $request->patchquery_shippingaddress,
            'patchquery_dollarrate'		        => $request->patchquery_dollarrate,
            'patchquery_shipmentamount'	        => $request->patchquery_shipmentamount,
            'patchqueryshipping_id'	            => $request->patchqueryshipping_id,
            'patchquery_shipmentinvoiceamount'	=> $request->patchquery_shipmentinvoiceamount,
            'updated_by'	 		    		=> $request->user_id,
            'updated_at'	 		    		=> date( 'Y-m-d h:i:s' ),
        ]);
        $patchqueryitem = $request->patchqueryitem;
        foreach ( $patchqueryitem as $patchqueryitems ) {
            DB::table( 'patchqueryitem' )
            ->where( 'patchqueryitem_id', '=', $patchqueryitems[ 'patchqueryitem_id' ] )
            ->update( [
                'patchquerycategory_id' 		=> $patchqueryitems[ 'patchquerycategory_id' ],
                'patchqueryitem_quantity' 		=> $patchqueryitems[ 'patchqueryitem_quantity' ],
                'patchqueryitem_height' 		=> $patchqueryitems[ 'patchqueryitem_height' ],
                'patchqueryitem_width'			=> $patchqueryitems[ 'patchqueryitem_width' ],
                'patchtype_id'					=> $patchqueryitems[ 'patchtype_id' ],
                'patchback_id'					=> $patchqueryitems[ 'patchback_id' ],
                'patchqueryitem_marketcost'		=> $patchqueryitems[ 'patchqueryitem_marketcost' ],
                'patchqueryitem_proposalquote'	=> $patchqueryitems[ 'patchqueryitem_proposalquote' ],
                'patchqueryitem_finalvendor'	=> $patchqueryitems[ 'patchqueryitem_finalvendor' ],
                'updated_by'					=> $request->user_id,
                'updated_at'					=> date( 'Y-m-d h:i:s' ),
            ]);
        }
        return response()->json( [ 'message' => 'Updated Successfully' ], 200 );
    }
    public function patchtaskdetail(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'patchqueryitem_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {    
			return response()->json("Task Id Required", 400);
		}
		$basicdetail = DB::table('tasklist')
		->select('*')
		->where('order_id','=',$request->patchqueryitem_id)
		->where('status_id','=',3)
		->first();
        if(isset($basicdetail->task_id)){
            $attachmentdetail = DB::table('taskattachment')
            ->select('*')
            ->where('task_id','=',$basicdetail->task_id)
            ->where('attachmenttype','=',1)
            ->where('status_id','=',1)
            ->get();
            $forwardedattachmentdetail = DB::table('taskattachment')
            ->select('*')
            ->where('task_id','=',$basicdetail->task_id)
            ->where('attachmenttype','=',3)
            ->where('status_id','=',1)
            ->get();
            $workattachmentdetail = DB::table('taskattachmentdetails')
            ->select('*')
            ->where('task_id','=',$basicdetail->task_id)
            ->where('attachmenttype','=',2)
            ->where('status_id','=',1)
            ->get();
            $taskpath = URL::to('/')."/public/task/".$basicdetail->task_token."/";
            $forwardedtaskpath = URL::to('/')."/public/order/".$basicdetail->order_token."/";
        }else{
            $attachmentdetail = array();
            $forwardedattachmentdetail = array();
            $workattachmentdetail = array();
            $taskpath = "";
            $forwardedtaskpath = "";
        }
		$taskworkpath = URL::to('/')."/public/taskwork/";
		$memberpath = URL::to('/')."/public/user_picture/";
		if($basicdetail){
			return response()->json(['basicdetail' => $basicdetail, 'attachmentdetail' => $attachmentdetail, 'forwardedattachmentdetail' => $forwardedattachmentdetail, 'workattachmentdetail' => $workattachmentdetail, 'taskworkpath' => $taskworkpath, 'taskpath' => $taskpath, 'forwardedtaskpath' => $forwardedtaskpath, 'memberpath' => $memberpath,'message' => 'Task Detail'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
}