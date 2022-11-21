<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class BlogController extends Controller
{


    public function index(Request $request){
       
        $page = $request->page;
        $per_page = $request->per_page;
        $keyword = $request->keyword;
        $where = "blogs.title LIKE '%".$keyword."%'";
    
        $query = Blog::select('blogs.id');
        if($keyword != ''){
            $query->whereRaw($where);
        }

        $total_items = $query->get()->count();
    
        $offset = ($page-1) * $per_page; 
        $total_pages = ceil ($total_items / $per_page);  
        
        $query = Blog::orderBy('blogs.id', 'desc');
        if($keyword != ''){
            $query->whereRaw($where);
        }
        $query->join('files', 'files.id', '=', 'blogs.featured_image', 'LEFT');
        $query->select('blogs.id', 'blogs.post_title', 'blogs.slug',  'blogs.featured_image',  'blogs.post_content', 'blogs.categories', 'files.file_name', 'files.thumbnail', 'blogs.created_at');
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

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_title' => 'required|max:255',
            'category' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['sts' => false,'errors' => $errors]);
        }

        $slug = SlugService::createSlug(Blog::class, 'slug', $request->post_title);
        
        $blog = Blog::updateOrCreate(['id' => $request->id],[
            'post_title' => $request->post_title,
            'slug' => $slug,
            'post_content' => (isset($request->post_content) ? $request->post_content : ''),
            'categories' => implode(',',$request->category),
            'featured_image' => $request->featured_image,
        ]);

        if($blog){
            return response()->json(['sts' => true, 'data' => $blog, 'msg' => 'Successfully saved.']);
        }
        else{
            return response()->json(['sts' => false, 'errors' => ['Blog not created. Please try again'] ]);
        }
    }

    public function details(Request $request){
        $id = (int)$request->id;
        $blog = Blog::where('blogs.id', $id)
            ->orderBy('blogs.id', 'desc')
            ->join('files', 'files.id', '=', 'blogs.featured_image', 'LEFT')
            ->select('blogs.id', 'blogs.post_title', 'blogs.slug',  'blogs.featured_image',  'blogs.post_content', 'blogs.categories', 'files.file_name', 'files.thumbnail')
            ->first();

        if($blog == null){
            return response()->json(['sts' => false,'msg' => 'Blog not found.']);
        }

        return response()->json([ 'sts' => true,'result' => $blog]);
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $blog = Blog::where('blogs.id', $id)->first();

        if($blog == null){
            return response()->json(['sts' => false,'msg' => 'Blog not found.' ]);
        }

        if($blog->delete()){
            return response()->json(['sts' => true,'msg' => 'Successfully deleted']);
        }
        else{
            return response()->json(['sts' => false,'msg' => 'Some error occurred.']);
        }        
    }
}
