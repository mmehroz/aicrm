<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use App\Models\Repositories\NotesRepository;
use Image;
use DB;
use Input;
use App\Item;
use Session;
use Response;
use Validator;
use URL;

class notesController extends Controller
{
    protected $note;
    public function __construct(NotesRepository $note)
    {
        $this->note = $note;
    }
    public function savenotes(Request $request){
		$validate = Validator::make($request->all(), [
	    	'notes_title'  		    => 'required',
		    'notes_description' 	=> 'required',
		]);
		if ($validate->fails()) {    
			return response()->json($validate->errors(), 400);
		}
		$data = $this->note->submitsavenotes($request);
		if($data){
			return response()->json(['message' => 'Notes Saved Successfully'],200);
		}else{
			return response()->json("Oops! Something Went Wrong", 400);
		}
	}
	public function noteslist(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'from'	=> 'required',
	      'to'		=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
		$data = $this->note->getnoteslist($request);
		if($data){
			return response()->json(['data' => $data,'message' => 'Notes List'],200);
		}else{
			return response()->json(['data' => array(),'message' => 'Notes List'],200);
		}
	}
    public function notesdetails(Request $request){
		$validate = Validator::make($request->all(), [ 
	      'notes_id'	=> 'required',
	    ]);
     	if ($validate->fails()) {
			return response()->json($validate->errors(), 400);
		}
        $data = $this->note->getnotesdetails($request->notes_id);
    	if($data){
			return response()->json(['data' => $data,'message' => 'Notes Details'],200);
		}else{
			return response()->json(['data' => array(),'message' => 'Notes Details'],200);
		}
	}
}