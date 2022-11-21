<?php

namespace App\Http\Controllers;

use App\Models\BlogCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class BlogCategoriesController extends Controller
{


    public function categories(Request $request){
       
        $page = $request->page;
        $per_page = $request->per_page;
        $keyword = $request->keyword;
        $where = "blog_categories.title LIKE '%".$keyword."%'";
    
        $query = BlogCategories::select('blog_categories.id');
        if($keyword != ''){
            $query->whereRaw($where);
        }

        $total_items = $query->get()->count();
    
        $offset = ($page-1) * $per_page; 
        $total_pages = ceil ($total_items / $per_page);  
        
        $query = BlogCategories::orderBy('blog_categories.id', 'desc');
        if($keyword != ''){
            $query->whereRaw($where);
        }
        $query->join('files', 'files.id', '=', 'blog_categories.featured_image', 'LEFT');
        $query->select('blog_categories.id', 'blog_categories.title', 'blog_categories.slug', 'blog_categories.featured_image', 'blog_categories.content', 'files.file_name', 'files.thumbnail');
        $query->limit($per_page);
        $query->offset($offset);
        $categories = $query->get()->toArray();

        return response()->json([
            'sts' => true,
            'result' => array(
                'total_items' => $total_items,
                'total_pages' => $total_pages,
                'current_page' => $page,
                'items' => $categories,
            )
        ]);
    }

    public function Allcategories(){
       
        $categories = BlogCategories::orderBy('id', 'desc')
            ->join('files', 'files.id', '=', 'blog_categories.featured_image', 'LEFT')
            ->select('blog_categories.id', 'blog_categories.title', 'blog_categories.slug', 'blog_categories.featured_image', 'blog_categories.content', 'files.file_name', 'files.thumbnail')
            ->get()->toArray();

        return response()->json(['sts' => true,'result' => $categories]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['sts' => false,'validate_error' => $errors]);
        }

        if($request->slug != ''){
            if($request->category_id == 0){
                $cat = BlogCategories::where('slug', $request->slug)->first();
            }
            else{
                $cat = BlogCategories::where('slug', $request->slug)->where('id', '!=', $request->category_id)->first();
            }

            if($cat != null){
                return response()->json(['sts' => false,'msg' => 'Category slug already taken.']);
            }

            $slug = $request->slug;
           
        }
        else{
            $slug = SlugService::createSlug(BlogCategories::class, 'slug', $request->title);
        }


        $category = BlogCategories::updateOrCreate(['id' => $request->category_id],[
            'title' => $request->title,
            'slug' => $slug,
            'content' => (isset($request->content) ? $request->content : ''),
            'featured_image' => $request->featured_image,
        ]);

        if($category){
            return response()->json(['sts' => true,'msg' => 'Category has been successfully created.']);
        }
        else{
            return response()->json(['sts' => false, 'msg' => 'Category not created. Please try again' ]);
        }
    }

    public function details(Request $request){
        $id = (int)$request->id;
        $category = BlogCategories::where('blog_categories.id', $id)
            ->orderBy('blog_categories.id', 'desc')
            ->join('files', 'files.id', '=', 'blog_categories.featured_image', 'LEFT')
            ->select('blog_categories.id', 'blog_categories.title', 'blog_categories.slug',  'blog_categories.featured_image',  'blog_categories.content', 'files.file_name', 'files.thumbnail')
            ->first();

        if($category == null){
            return response()->json(['sts' => false,'msg' => 'Category not found.']);
        }

        return response()->json([ 'sts' => true,'result' => $category]);
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $category = BlogCategories::where('blog_categories.id', $id)->first();

        if($category == null){
            return response()->json(['sts' => false,'msg' => 'Category not found.' ]);
        }

        if($category->delete()){
            return response()->json(['sts' => true,'msg' => 'Successfully deleted']);
        }
        else{
            return response()->json(['sts' => false,'msg' => 'Some error occurred.']);
        }        
    }
}
