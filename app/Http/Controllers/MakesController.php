<?php

namespace App\Http\Controllers;

use App\Models\Makes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;


class MakesController extends Controller
{

    public function fetchMakes(){
       
        $makes = Makes::orderBy('id', 'desc')
        ->select('id', 'title', 'slug', 'created_at')
        ->get()->toArray();

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $makes,
        ], 200);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'sts' => false,
                'validate_error' => $errors
            ], 400);
        }


        $make = Makes::updateOrCreate(['id' => $request->id],[
            'title' => $request->title,
            'slug' => SlugService::createSlug(Makes::class, 'slug', $request->title),
        ]);

        if($make){
            return response()->json([
                'sts' => true,
                'msg' => 'Make has been successfully created.'
            ], 200);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Make not created. Please try again'
            ], 400);
        }
    }

    public function details(Request $request){
        $id = (int)$request->id;
        $makes = Makes::where('id', $id)
        ->select('id', 'title', 'slug', 'created_at')
            ->first();

        if($makes == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Makes not found.',
            ], 401);
        }

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $makes,
        ], 200);
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $make = Makes::where('id', $id)->first();

        if($make == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Make not found.',
            ], 401);
        }

        if($make->delete()){
            return response()->json([
                'sts' => true,
                'msg' => 'Successfully deleted',
            ], 200);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Some error occurred.',
            ], 401);
        }        
    }
}
