<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    //
    public function getPrograms(){
        $programs = Program::with('sections', 'speakerHighlights')->latest()->get();

        return response()->json(['status'=>true, 'programs'=>$programs]);
    }

    public function getProgram($id){
        $program = Program::where('id', $id)->with('sections', 'speakerHighlights')->first();
        return response()->json(['status'=>true, 'program'=>$program]);
    }
}
