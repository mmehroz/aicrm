<?php
namespace App\Models\Repositories;

use DB;

class NotesRepository
{
    public function submitsavenotes($request)
    {
        $notes_token = openssl_random_pseudo_bytes(7);
        $notes_token = bin2hex($notes_token);
        $adds = array(
            'notes_title' 			=> $request->notes_title,
            'notes_token'			=> $notes_token,
            'notes_description'		=> $request->notes_description,
            'notes_date'			=> date('Y-m-d'),
            'brand_id'              => $request->brand_id,
            'status_id'	 			=> 1,
            'created_by'		 	=> $request->user_id,
            'created_at'	 		=> date('Y-m-d h:i:s'),
        );
        $save = DB::table('notes')->insert($adds);
        return $save;
    }
    public function getnoteslist($request)
    {
        if ($request->role_id == 1 || $request->role_id == 3) {
            $data = DB::table('notes')
            ->select('notes_id','notes_title','notes_token','notes_date')
            ->where('brand_id','=',$request->brand_id)
            ->whereBetween('notes_date',[$request->from, $request->to])
            ->where('status_id','=',1)
            ->get();		
        }else{
            $data = DB::table('notes')
            ->select('*')
            ->where('brand_id','=',$request->brand_id)
            ->where('created_by','=',$request->user_id)
            ->whereBetween('notes_date',[$request->from, $request->to])
            ->where('status_id','=',1)
            ->get();		
        }
        return $data;
    }
    public function getnotesdetails($notes_id)
    {
        $data = DB::table('notes')
        ->select('notes_description')
        ->where('notes_id','=',$notes_id)
        ->where('status_id','=',1)
        ->first();
        return $data;
    }
}
