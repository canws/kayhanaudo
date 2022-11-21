<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use App\Models\Pages;

class PageController extends Controller
{


    public function index(Request $request){
       
        $page = $request->page;
        $per_page = $request->per_page;
        $keyword = $request->keyword;
        $where = "pages.title LIKE '%".$keyword."%'";
    
        $query = Pages::select('pages.id');
        if($keyword != ''){
            $query->whereRaw($where);
        }

        $total_items = $query->get()->count();
    
        $offset = ($page-1) * $per_page; 
        $total_pages = ceil ($total_items / $per_page);  
        
        $query = Pages::orderBy('pages.id', 'desc');
        if($keyword != ''){
            $query->whereRaw($where);
        }
        $query->join('files', 'files.id', '=', 'pages.featured_image', 'LEFT');
        $query->select('pages.id', 'pages.title', 'pages.slug',  'pages.featured_image',  'pages.content', 'files.file_name', 'files.thumbnail', 'pages.created_at');
        $query->limit($per_page);
        $query->offset($offset);
        $pages = $query->get()->toArray();

        return response()->json([
            'sts' => true,
            'result' => array(
                'total_items' => $total_items,
                'total_pages' => $total_pages,
                'current_page' => $page,
                'items' => $pages,
            )
        ]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['sts' => false,'errors' => $errors]);
        }

        $slug = SlugService::createSlug(Page::class, 'slug', $request->title);
        
        $page = Pages::updateOrCreate(['id' => $request->id],[
            'title' => $request->title,
            'slug' => $slug,
            'content' => (isset($request->content) ? $request->content : ''),
            'featured_image' => $request->featured_image,
        ]);

        if($page){
            return response()->json(['sts' => true, 'data' => $page, 'msg' => 'Successfully saved.']);
        }
        else{
            return response()->json(['sts' => false, 'errors' => ['Page not created. Please try again'] ]);
        }
    }

    public function details(Request $request){
        $id = (int)$request->id;
        $page = Pages::where('pages.id', $id)
            ->orderBy('pages.id', 'desc')
            ->join('files', 'files.id', '=', 'pages.featured_image', 'LEFT')
            ->select('pages.id', 'pages.title', 'pages.slug',  'pages.featured_image',  'pages.content', 'files.file_name', 'files.thumbnail')
            ->first();

        if($page == null){
            return response()->json(['sts' => false,'msg' => 'Page not found.']);
        }

        return response()->json([ 'sts' => true,'result' => $page]);
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $page = Pages::where('pages.id', $id)->first();

        if($page == null){
            return response()->json(['sts' => false,'msg' => 'Page not found.' ]);
        }

        if($page->delete()){
            return response()->json(['sts' => true,'msg' => 'Successfully deleted']);
        }
        else{
            return response()->json(['sts' => false,'msg' => 'Some error occurred.']);
        }        
    }
}
