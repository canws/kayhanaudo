<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class CategoriesController extends Controller
{
    public function sub_categories($parent_id) {
        $categories = Categories::where('parent', $parent_id)
        ->orderBy('categories.title', 'ASC')
        ->join('files', 'files.id', '=', 'categories.featured_image', 'LEFT')
        ->select('categories.id', 'categories.title', 'categories.slug', 'categories.content', 'categories.parent', 'files.file_name', 'files.thumbnail')
        ->get()->toArray();   
        
        $categorie_arr = array();
        if(!empty($categories)){
            foreach ($categories as $value){
                $categorie_arr[] = array(
                    'id' => $value['id'],
                    'title' => $value['title'],
                    'slug' => $value['slug'],
                    'content' => $value['content'],
                    'parent' => $value['parent'],
                    'file_name' => $value['file_name'],
                    'thumbnail' => $value['thumbnail'],
                    'subcategory' => $this->sub_categories($value['id']),
                );
            }
        }
        
        return $categorie_arr;
    }

    public function categories(Request $request){
       
        $categories = Categories::where('parent', 0)
        ->orderBy('id', $request->orderby)
        ->join('files', 'files.id', '=', 'categories.featured_image', 'LEFT')
        ->select('categories.id', 'categories.title', 'categories.slug', 'categories.featured_image', 'categories.content', 'categories.parent', 'files.file_name', 'files.thumbnail')
        ->get()->toArray();

        $categorie_arr = array();
	
        if(!empty($categories)){
            foreach($categories as $key => $value){
                $categorie_arr[] = array(
                    'id' => $value['id'],
                    'title' => $value['title'],
                    'slug' => $value['slug'],
                    'content' => $value['content'],
                    'parent' => $value['parent'],
                    'featured_image' => $value['featured_image'],
                    'file_name' => $value['file_name'],
                    'thumbnail' => $value['thumbnail'],
                    'subcategory' => $this->sub_categories($value['id']),
                );
            }
        }

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $categorie_arr,
        ], 200);
    }

    public function Allcategories(){
       
        $categories = Categories::orderBy('id', 'desc')
        ->join('files', 'files.id', '=', 'categories.featured_image', 'LEFT')
        ->select('categories.id', 'categories.title', 'categories.slug', 'categories.featured_image', 'categories.content', 'categories.parent', 'files.file_name', 'files.thumbnail')
        ->get()->toArray();

        $categorie_arr = array();
	
        if(!empty($categories)){
            foreach($categories as $key => $value){
                $categorie_arr[] = array(
                    'id' => $value['id'],
                    'title' => $value['title'],
                    'slug' => $value['slug'],
                );
            }
        }

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $categorie_arr,
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

        if($request->slug != ''){
            if($request->category_id == 0){
                $cat = Categories::where('slug', $request->slug)->first();
            }
            else{
                $cat = Categories::where('slug', $request->slug)->where('id', '!=', $request->category_id)->first();
            }

            if($cat != null){
                return response()->json([
                    'sts' => false,
                    'msg' => 'Category slug already taken.'
                ]);
            }

            $slug = $request->slug;
           
        }
        else{
            $slug = SlugService::createSlug(Categories::class, 'slug', $request->title);
        }


        $category = Categories::updateOrCreate(['id' => $request->category_id],[
            'title' => $request->title,
            'slug' => $slug,
            'content' => (isset($request->content) ? $request->content : ''),
            'parent' => ($request->parent != '' ? $request->parent : 0),
            'featured_image' => $request->featured_image,
        ]);

        if($category){
            return response()->json([
                'sts' => true,
                'msg' => 'Category has been successfully created.'
            ], 200);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Category not created. Please try again'
            ], 400);
        }
    }

    public function details(Request $request){
        $id = (int)$request->id;
        $category = Categories::where('categories.id', $id)
            ->orderBy('categories.id', 'desc')
            ->join('files', 'files.id', '=', 'categories.featured_image', 'LEFT')
            ->select('categories.id', 'categories.title', 'categories.slug',  'categories.featured_image',  'categories.content', 'categories.parent', 'files.file_name', 'files.thumbnail')
            ->first();

        if($category == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Category not found.',
            ], 401);
        }

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $category,
        ], 200);
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $category = Categories::where('categories.id', $id)->first();

        if($category == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Category not found.',
            ], 401);
        }

        if($category->delete()){

            Categories::where('parent', $id)->update(['parent' => 0]);

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
