<?php

namespace App\Http\Controllers;
use App\Models\Models;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class ModelsController extends Controller
{
    public function fetchModels(Request $request){
        if(isset($request->make_id)){
            $models = Models::orderBy('id', 'desc')
                ->where('make_id', $request->make_id)
                ->select('id', 'title', 'slug', 'make_id', 'created_at')
                ->get()->toArray();
            
            return response()->json([
                'sts' => true,
                'result' => $models
            ]);
        }
        else{
            $page = $request->page;
            $per_page = $request->per_page;
            $keyword = $request->keyword;
            $where = "models.title LIKE '%".$keyword."%'";
        
            $query = Models::select('models.id');
            if($keyword != ''){
                $query->whereRaw($where);
            }
            $query->join('makes', 'makes.id', '=', 'models.make_id', 'INNER');

            $total_items = $query->get()->count();
        
            $offset = ($page-1) * $per_page; 
            $total_pages = ceil ($total_items / $per_page);  
            
            $query = Models::orderBy('models.id', 'desc');
            if($keyword != ''){
                $query ->whereRaw($where);
            }
            $query->join('makes', 'makes.id', '=', 'models.make_id', 'INNER');
            $query->select('models.id', 'models.title', 'models.slug', 'models.make_id', 'makes.title as make', 'models.created_at');
            $query->limit($per_page);
            $query->offset($offset);
            $models = $query->get()->toArray();

            return response()->json([
                'sts' => true,
                'result' => array(
                    'total_items' => $total_items,
                    'total_pages' => $total_pages,
                    'current_page' => $page,
                    'items' => $models,
                )
            ]);
       }
        
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'make' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'sts' => false,
                'errors' => $errors->all()
            ]);
        }


        $model = Models::updateOrCreate(['id' => $request->id],[
            'title' => $request->title,
            'make_id' => $request->make,
            'slug' => SlugService::createSlug(Models::class, 'slug', $request->title),
        ]);

        if($model){
            return response()->json([
                'sts' => true,
                'msg' => 'Model has been successfully created.'
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'errors' => ['Model not created. Please try again']
            ]);
        }
    }

    public function details(Request $request){
        $id = (int)$request->id;
        $model = Models::where('id', $id)
            ->select('id', 'title', 'slug', 'make_id', 'created_at')
            ->first();

        if($model == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Models not found.',
            ], 401);
        }

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $model,
        ], 200);
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $model = Models::where('id', $id)->first();

        if($model == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Model not found.',
            ], 401);
        }

        if($model->delete()){
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
