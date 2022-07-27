<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->header('token') && $request->user_id){
            $check = DB::table('user')
            ->select('user_id')
            ->where('user_token','=',$request->header('token'))
            ->where('status_id','=',1)
            ->count();
            if ($check == 0) {
                return redirect('/login');   
            }else{
                return $next($request);
            }
        }else{
                return redirect('/login');
        }
    }
}
