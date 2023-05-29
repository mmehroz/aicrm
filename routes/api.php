<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\settingsController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\brandController;
use App\Http\Controllers\userController;
use App\Http\Controllers\leadController;
use App\Http\Controllers\orderController;
use App\Http\Controllers\taskController;
use App\Http\Controllers\ppcController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\billingController;
use App\Http\Controllers\billingmerchantController;
use App\Http\Controllers\freshleadController;
use App\Http\Controllers\reportController;
use App\Http\Controllers\targetController;
use App\Http\Controllers\commissionController;
use App\Http\Controllers\requestquoteController;
use App\Http\Controllers\leadgenerateController;
use App\Http\Controllers\uiorderController;
use App\Http\Controllers\vendorController;
use App\Http\Controllers\patchController;
use App\Http\Controllers\searchleadController;
use App\Http\Controllers\patchqueryController;
use App\Http\Controllers\rawdataController;
use App\Http\Controllers\twilioController;
use App\Http\Controllers\notesController;

Route::any('/downloadclientattachment', [taskController::class, 'downloadclientattachment']);
Route::any('/downloadworkattachment', [taskController::class, 'downloadworkattachment']);

Route::middleware('cors')->group(function(){
Route::any('/login', [loginController::class, 'login']);
Route::middleware('login.check')->group(function(){	
Route::any('/logout', [loginController::class, 'logout']);

Route::any('/role', [settingsController::class, 'role']);
Route::any('/countrylist', [settingsController::class, 'countrylist']);
Route::any('/stateslist', [settingsController::class, 'stateslist']);
Route::any('/citieslist', [settingsController::class, 'citieslist']);
Route::any('/ordertype', [settingsController::class, 'ordertype']);
Route::any('/brandtype', [settingsController::class, 'brandtype']);
Route::any('/leadstatus', [settingsController::class, 'leadstatus']);
Route::any('/orderstatus', [settingsController::class, 'orderstatus']);
Route::any('/taskstatus', [settingsController::class, 'taskstatus']);
Route::any('/orderquestion', [settingsController::class, 'orderquestion']);
Route::any('/orderpaymentstatus', [settingsController::class, 'orderpaymentstatus']);
Route::any('/patchquerystatus', [settingsController::class, 'patchquerystatus']);
Route::any('/patchpaymenttype', [settingsController::class, 'patchpaymenttype']);

Route::any('/createbrand', [brandController::class, 'createbrand']);
Route::any('/updatebrand', [brandController::class, 'updatebrand']);
Route::any('/brandlist', [brandController::class, 'brandlist']);
Route::any('/branddetail', [brandController::class, 'branddetail']);
Route::any('/deletebrand', [brandController::class, 'deletebrand']);
Route::any('/userbrandlist', [brandController::class, 'userbrandlist']);
Route::any('/remainingbrandlist', [brandController::class, 'remainingbrandlist']);

Route::any('/adduser', [userController::class, 'adduser']);
Route::any('/updateuser', [userController::class, 'updateuser']);
Route::any('/userlist', [userController::class, 'userlist']);
Route::any('/alluserlist', [userController::class, 'alluserlist']);
Route::any('/userdetails', [userController::class, 'userdetails']);
Route::any('/deleteuser', [userController::class, 'deleteuser']);
Route::any('/updateusercoverpicture', [userController::class, 'updateusercoverpicture']);
Route::any('/removeuserfrombrand', [userController::class, 'removeuserfrombrand']);
Route::any('/rolewiseuserlist', [userController::class, 'rolewiseuserlist']);
Route::any('/rolesuserlist', [userController::class, 'rolesuserlist']);
Route::any('/workeruserlist', [userController::class, 'workeruserlist']);

Route::any('/createlead', [leadController::class, 'createlead']);
Route::any('/updatelead', [leadController::class, 'updatelead']);
Route::any('/leadlist', [leadController::class, 'leadlist']);
Route::any('/leaddetails', [leadController::class, 'leaddetails']);
Route::any('/deletelead', [leadController::class, 'deletelead']);
Route::any('/forwardedleadlist', [leadController::class, 'forwardedleadlist']);
Route::any('/pickedleadlist', [leadController::class, 'pickedleadlist']);
Route::any('/automanualleadlist', [leadController::class, 'automanualleadlist']);
Route::any('/picklead', [leadController::class, 'picklead']);
Route::any('/unpicklead', [leadController::class, 'unpicklead']);
Route::any('/cancellead', [leadController::class, 'cancellead']);
Route::any('/makelead', [leadController::class, 'makelead']);
Route::any('/searchlead', [leadController::class, 'searchlead']);
Route::any('/clientrevenuereport', [leadController::class, 'clientrevenuereport']);
Route::any('/transferclient', [leadController::class, 'transferclient']);
Route::any('/allclientlist', [leadController::class, 'allclientlist']);
Route::any('/lockorunlocklead', [leadController::class, 'lockorunlocklead']);
Route::any('/clientwisepaymentlist', [leadController::class, 'clientwisepaymentlist']);

Route::any('/createorder', [orderController::class, 'createorder']);
Route::any('/updateorder', [orderController::class, 'updateorder']);
Route::any('/orderlist', [orderController::class, 'orderlist']);
Route::any('/orderdetail', [orderController::class, 'orderdetail']);
Route::any('/deleteorder', [orderController::class, 'deleteorder']);
Route::any('/removefromorder', [orderController::class, 'removefromorder']);
Route::any('/ordertotalamount', [orderController::class, 'ordertotalamount']);
Route::any('/forwardedorderlist', [orderController::class, 'forwardedorderlist']);
Route::any('/pickedorderlist', [orderController::class, 'pickedorderlist']);
Route::any('/pickorder', [orderController::class, 'pickorder']);
Route::any('/unpickorder', [orderController::class, 'unpickorder']);
Route::any('/updateorderstatus', [orderController::class, 'updateorderstatus']);
Route::any('/orderprogress', [orderController::class, 'orderprogress']);
Route::any('/orderpaymentlist', [orderController::class, 'orderpaymentlist']);
Route::any('/updateorderpaymentstatus', [orderController::class, 'updateorderpaymentstatus']);
Route::any('/grouporderlist', [orderController::class, 'grouporderlist']);
Route::any('/clientwiseorderlist', [orderController::class, 'clientwiseorderlist']);
Route::any('/previousorderhistory', [orderController::class, 'previousorderhistory']);
Route::any('/updatemultiorderstatus', [orderController::class, 'updatemultiorderstatus']);
Route::any('/searchorder', [orderController::class, 'searchorder']);

Route::any('/creattask', [taskController::class, 'creattask']);
Route::any('/updatetask', [taskController::class, 'updatetask']);
Route::any('/tasklist', [taskController::class, 'tasklist']);
Route::any('/statuswisetasklist', [taskController::class, 'statuswisetasklist']);
Route::any('/taskdetail', [taskController::class, 'taskdetail']);
Route::any('/taskmemberdetail', [taskController::class, 'taskmemberdetail']);
Route::any('/taskcommentdetail', [taskController::class, 'taskcommentdetail']);
Route::any('/deletetask', [taskController::class, 'deletetask']);
Route::any('/removefromtask', [taskController::class, 'removefromtask']);
Route::any('/orderwisetasklist', [taskController::class, 'orderwisetasklist']);
Route::any('/addmembertotask', [taskController::class, 'addmembertotask']);
Route::any('/sendcommenttotask', [taskController::class, 'sendcommenttotask']);
Route::any('/sendreply', [taskController::class, 'sendreply']);
Route::any('/commentreplydetail', [taskController::class, 'commentreplydetail']);
Route::any('/movetask', [taskController::class, 'movetask']);
Route::any('/submitwork', [taskController::class, 'submitwork']);
Route::any('/taskworkattachment', [taskController::class, 'taskworkattachment']);

Route::any('/addppc', [ppcController::class, 'addppc']);
Route::any('/updateppc', [ppcController::class, 'updateppc']);
Route::any('/ppclist', [ppcController::class, 'ppclist']);
Route::any('/ppcdetail', [ppcController::class, 'ppcdetail']);
Route::any('/deleteppc', [ppcController::class, 'deleteppc']);

Route::any('/assignppc', [ppcController::class, 'assignppc']);
Route::any('/updateassignppc', [ppcController::class, 'updateassignppc']);
Route::any('/assignppclist', [ppcController::class, 'assignppclist']);
Route::any('/assignppcdetail', [ppcController::class, 'assignppcdetail']);
Route::any('/deleteassignppc', [ppcController::class, 'deleteassignppc']);
Route::any('/monthlyppcbudget', [ppcController::class, 'monthlyppcbudget']);

Route::any('/admindashboard', [dashboardController::class, 'admindashboard']);
Route::any('/adminbranddetails', [dashboardController::class, 'adminbranddetails']);
Route::any('/portaladmindashboard', [dashboardController::class, 'portaladmindashboard']);
Route::any('/billingmerchantdashboard', [dashboardController::class, 'billingmerchantdashboard']);
Route::any('/upcomingpaymentdashboard', [dashboardController::class, 'upcomingpaymentdashboard']);
Route::any('/workerdashboard', [dashboardController::class, 'workerdashboard']);
Route::any('/salesdashboard', [dashboardController::class, 'salesdashboard']);
Route::any('/leadashboard', [dashboardController::class, 'leadashboard']);
Route::any('/adminpatchdashboard', [dashboardController::class, 'adminpatchdashboard']);
Route::any('/salespatchdashboard', [dashboardController::class, 'salespatchdashboard']);
Route::any('/adminpatchandquerylist', [dashboardController::class, 'adminpatchandquerylist']);
Route::any('/patchqueryprofitlossstatement', [dashboardController::class, 'patchqueryprofitlossstatement']);

Route::any('/forwardedpaymentlist', [billingController::class, 'forwardedpaymentlist']);
Route::any('/pickedpaymentlist', [billingController::class, 'pickedpaymentlist']);
Route::any('/pickpayment', [billingController::class, 'pickpayment']);
Route::any('/unpickpayment', [billingController::class, 'unpickpayment']);
Route::any('/paymentdetails', [billingController::class, 'paymentdetails']);
Route::any('/updatepaymentstatus', [billingController::class, 'updatepaymentstatus']);
Route::any('/mergedeal', [billingController::class, 'mergedeal']);
Route::any('/unmergedeal', [billingController::class, 'unmergedeal']);
Route::any('/mergepickedpaymentlist', [billingController::class, 'mergepickedpaymentlist']);
Route::any('/statuswisepaymentlist', [billingController::class, 'statuswisepaymentlist']);
Route::any('/mergestatuswisepaymentlist', [billingController::class, 'mergestatuswisepaymentlist']);
Route::any('/paymentamount', [billingController::class, 'paymentamount']);
Route::any('/sumpaymentamount', [billingController::class, 'sumpaymentamount']);
Route::any('/multiupdatepaymentstatus', [billingController::class, 'multiupdatepaymentstatus']);
Route::any('/savebillingorderfollowup', [billingController::class, 'savebillingorderfollowup']);
Route::any('/billingorderfollowuplist', [billingController::class, 'billingorderfollowuplist']);
Route::any('/oldcrmbillingpaymentlist', [billingController::class, 'oldcrmbillingpaymentlist']);
Route::any('/markpaidonoldcrmpayment', [billingController::class, 'markpaidonoldcrmpayment']);

Route::any('/savefreshlead', [freshleadController::class, 'savefreshlead']);
Route::any('/freshleadlist', [freshleadController::class, 'freshleadlist']);
Route::any('/savefreshleadfollowup', [freshleadController::class, 'savefreshleadfollowup']);
Route::any('/getfreshleadfollowup', [freshleadController::class, 'getfreshleadfollowup']);

Route::any('/addbillingmerchant', [billingmerchantController::class, 'addbillingmerchant']);
Route::any('/updatebillingmerchant', [billingmerchantController::class, 'updatebillingmerchant']);
Route::any('/billingmerchantlist', [billingmerchantController::class, 'billingmerchantlist']);
Route::any('/billingmerchantdetails', [billingmerchantController::class, 'billingmerchantdetails']);
Route::any('/deletebillingmerchant', [billingmerchantController::class, 'deletebillingmerchant']);
Route::any('/addwithdrawalamount', [billingmerchantController::class, 'addwithdrawalamount']);
Route::any('/withdrawalamountlist', [billingmerchantController::class, 'withdrawalamountlist']);
Route::any('/billingmerchantreport', [billingmerchantController::class, 'billingmerchantreport']);
Route::any('/withdrawaltype', [billingmerchantController::class, 'withdrawaltype']);

Route::any('/addtarget', [targetController::class, 'addtarget']);
Route::any('/updatetarget', [targetController::class, 'updatetarget']);
Route::any('/nontargetlist', [targetController::class, 'nontargetlist']);
Route::any('/targetlist', [targetController::class, 'targetlist']);
Route::any('/usertargetdetails', [targetController::class, 'usertargetdetails']);

Route::any('/salestargetreport', [reportController::class, 'salestargetreport']);
Route::any('/commissionreport', [reportController::class, 'commissionreport']);
Route::any('/patchcommissionreport', [reportController::class, 'patchcommissionreport']);

Route::any('/addcommission', [commissionController::class, 'addcommission']);
Route::any('/commissionlist', [commissionController::class, 'commissionlist']);

Route::any('/sendquoterequest', [requestquoteController::class, 'sendquoterequest']);
Route::any('/quotelist', [requestquoteController::class, 'quotelist']);
Route::any('/quotedetails', [requestquoteController::class, 'quotedetails']);
Route::any('/deletequote', [requestquoteController::class, 'deletequote']);
Route::any('/movequoterequest', [requestquoteController::class, 'movequoterequest']);
Route::any('/updatequoterequest', [requestquoteController::class, 'updatequoterequest']);
Route::any('/chekprojectcost', [requestquoteController::class, 'chekprojectcost']);
Route::any('/deletequotepayment', [requestquoteController::class, 'deletequotepayment']);

Route::any('/generatelead', [leadgenerateController::class, 'generatelead']);

Route::any('/createuiorder', [uiorderController::class, 'createuiorder']);
Route::any('/uiorderlist', [uiorderController::class, 'uiorderlist']);
Route::any('/updateuiorderstatus', [uiorderController::class, 'updateuiorderstatus']);
Route::any('/uiorderdetail', [uiorderController::class, 'uiorderdetail']);

Route::any('/vendortype', [vendorController::class, 'vendortype']);
Route::any('/addvendor', [vendorController::class, 'addvendor']);
Route::any('/vendorlist', [vendorController::class, 'vendorlist']);
Route::any('/vendordetails', [vendorController::class, 'vendordetails']);
Route::any('/deletevendor', [vendorController::class, 'deletevendor']);

Route::any('/createpatchorder', [patchController::class, 'createpatchorder']);
Route::any('/patchorderlist', [patchController::class, 'patchorderlist']);
Route::any('/updatepatchorder', [patchController::class, 'updatepatchorder']);
Route::any('/patchorderdetails', [patchController::class, 'patchorderdetails']);
Route::any('/deletepatchorder', [patchController::class, 'deletepatchorder']);
Route::any('/movepatchorder', [patchController::class, 'movepatchorder']);
Route::any('/updatebillingpatchstatus', [patchController::class, 'updatebillingpatchstatus']);
Route::any('/patchbillingorderlist', [patchController::class, 'patchbillingorderlist']);
Route::any('addpatchpayment', [patchController::class, 'addpatchpayment']);
Route::any('patchpaymentlist', [patchController::class, 'patchpaymentlist']);
Route::any('patchorderreport', [patchController::class, 'patchorderreport']);

Route::any('/randomsearchlead', [searchleadController::class, 'randomsearchlead']);
Route::any('/movesearchlead', [searchleadController::class, 'movesearchlead']);
Route::any('/searchleadlist', [searchleadController::class, 'searchleadlist']);
Route::any('/dmeorderdetails', [searchleadController::class, 'dmeorderdetails']);
Route::any('/savesearchleadfollowup', [searchleadController::class, 'savesearchleadfollowup']);
Route::any('/getsearchleadfollowup', [searchleadController::class, 'getsearchleadfollowup']);

Route::any('/patchquerycategories', [patchqueryController::class, 'patchquerycategories']);
Route::any('/createpatchquery', [patchqueryController::class, 'createpatchquery']);
Route::any('/patchquerylist', [patchqueryController::class, 'patchquerylist']);
Route::any('/statuswisepatchquerylist', [patchqueryController::class, 'statuswisepatchquerylist']);
Route::any('/patchquerydetails', [patchqueryController::class, 'patchquerydetails']);
Route::any('/movepatchquery', [patchqueryController::class, 'movepatchquery']);
Route::any('/updatepatchquery', [patchqueryController::class, 'updatepatchquery']);
Route::any('/deletepatchquery', [patchqueryController::class, 'deletepatchquery']);
Route::any('/savepatchqueryfollowup', [patchqueryController::class, 'savepatchqueryfollowup']);
Route::any('/patchqueryfollowuplist', [patchqueryController::class, 'patchqueryfollowuplist']);
Route::any('/patchqueryandleaddetails', [patchqueryController::class, 'patchqueryandleaddetails']);
Route::any('/pickpatchquery', [patchqueryController::class, 'pickpatchquery']);
Route::any('/unpickpatchquery', [patchqueryController::class, 'unpickpatchquery']);
Route::any('/deletepatchqueryitem', [patchqueryController::class, 'deletepatchqueryitem']);
Route::any('/generatepatchqueryproposal', [patchqueryController::class, 'generatepatchqueryproposal']);
Route::any('/deliveredpatchquerylist', [patchqueryController::class, 'deliveredpatchquerylist']);
Route::any('/patchqueryinvoicedetails', [patchqueryController::class, 'patchqueryinvoicedetails']);
Route::any('/patchqueryshippingweight', [patchqueryController::class, 'patchqueryshippingweight']);
Route::any('/patchqueryshippingcost', [patchqueryController::class, 'patchqueryshippingcost']);
Route::any('/patchtype', [patchqueryController::class, 'patchtype']);
Route::any('/patchback', [patchqueryController::class, 'patchback']);
Route::any('/patchquerysub', [patchqueryController::class, 'patchquerysub']);
Route::any('/validatepatchquery', [patchqueryController::class, 'validatepatchquery']);

Route::any('/rawdatasheetlist', [rawdataController::class, 'rawdatasheetlist']);
Route::any('/rawdatalist', [rawdataController::class, 'rawdatalist']);
Route::any('/rawdatadetails', [rawdataController::class, 'rawdatadetails']);
Route::any('/saverawdatafollowup', [rawdataController::class, 'saverawdatafollowup']);
Route::any('/rawdatafollowuplist', [rawdataController::class, 'rawdatafollowuplist']);
Route::any('/uploadrawdata', [rawdataController::class, 'uploadrawdata']);
Route::any('/dedealdetails', [rawdataController::class, 'dedealdetails']);

Route::any('/sendsms', [twilioController::class, 'sendsms']);

Route::any('/savenotes', [notesController::class, 'savenotes']);
Route::any('/noteslist', [notesController::class, 'noteslist']);
Route::any('/notesdetails', [notesController::class, 'notesdetails']);
});
});