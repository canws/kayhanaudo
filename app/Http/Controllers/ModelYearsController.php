<?php

namespace App\Http\Controllers;

use App\Models\ModelYears;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class ModelYearsController extends Controller
{
    public function fetchModelYears(){
       
        $modelYears = ModelYears::orderBy('id', 'desc')
        ->select('id', 'title', 'slug', 'created_at')
        ->get()->toArray();

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $modelYears,
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


        $model_year = ModelYears::updateOrCreate(['id' => $request->id],[
            'title' => $request->title,
            'slug' => SlugService::createSlug(ModelYears::class, 'slug', $request->title),
        ]);

        if($model_year){
            return response()->json([
                'sts' => true,
                'msg' => 'Model Year has been successfully created.'
            ], 200);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Model Year not created. Please try again'
            ], 400);
        }
    }

    public function details(Request $request){
        $id = (int)$request->id;
        $model_year = ModelYears::where('id', $id)
            ->select('id', 'title', 'slug', 'created_at')
            ->first();

        if($model_year == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Model Year not found.',
            ], 401);
        }

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $model_year,
        ], 200);
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $model_year = ModelYears::where('id', $id)->first();

        if($model_year == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Model year not found.',
            ], 401);
        }

        if($model_year->delete()){
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
