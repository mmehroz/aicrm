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
/*
|---------------------------------------------------------------------	-----
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
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

Route::any('/createbrand', [brandController::class, 'createbrand']);
Route::any('/updatebrand', [brandController::class, 'updatebrand']);
Route::any('/brandlist', [brandController::class, 'brandlist']);
Route::any('/branddetail', [brandController::class, 'branddetail']);
Route::any('/deletebrand', [brandController::class, 'deletebrand']);

Route::any('/adduser', [userController::class, 'adduser']);
Route::any('/updateuser', [userController::class, 'updateuser']);
Route::any('/userlist', [userController::class, 'userlist']);
Route::any('/alluserlist', [userController::class, 'alluserlist']);
Route::any('/userdetails', [userController::class, 'userdetails']);
Route::any('/deleteuser', [userController::class, 'deleteuser']);
Route::any('/updateusercoverpicture', [userController::class, 'updateusercoverpicture']);

Route::any('/createlead', [leadController::class, 'createlead']);
Route::any('/updatelead', [leadController::class, 'updatelead']);
Route::any('/leadlist', [leadController::class, 'leadlist']);
Route::any('/leaddetails', [leadController::class, 'leaddetails']);
Route::any('/deletelead', [leadController::class, 'deletelead']);
Route::any('/forwardedleadlist', [leadController::class, 'forwardedleadlist']);

Route::any('/createorder', [orderController::class, 'createorder']);
Route::any('/updateorder', [orderController::class, 'updateorder']);
Route::any('/orderlist', [orderController::class, 'orderlist']);
Route::any('/orderdetail', [orderController::class, 'orderdetail']);
Route::any('/deleteorder', [orderController::class, 'deleteorder']);
Route::any('/removefromorder', [orderController::class, 'removefromorder']);

Route::any('/creattask', [taskController::class, 'creattask']);
Route::any('/updatetask', [taskController::class, 'updatetask']);
Route::any('/tasklist', [taskController::class, 'tasklist']);
Route::any('/taskdetail', [taskController::class, 'taskdetail']);
Route::any('/deletetask', [taskController::class, 'deletetask']);
Route::any('/removefromtask', [taskController::class, 'removefromtask']);
Route::any('/orderwisetasklist', [taskController::class, 'orderwisetasklist']);
});
});