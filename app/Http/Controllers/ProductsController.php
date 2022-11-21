<?php

namespace App\Http\Controllers;

use App\Models\AddOnOptions;
use App\Models\Categories;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\Variation;
use App\Models\Files;
use App\Models\ProductAddOns;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Auth;
use \Cviebrock\EloquentSluggable\Services\SlugService;
use DB;


class ProductsController extends Controller
{
    public function index(Request $request){
        $page = $request->page;
        $per_page = $request->per_page;
        $keyword = $request->keyword;
        $where = "products.product_title LIKE '".$keyword."%'";
        
        $query = Products::orderBy('products.id', 'desc');
        if($keyword != ''){
            $query->whereRaw($where);
        }
        $query->join('files', 'files.id', '=', 'products.featured_image', 'LEFT');
        $query->select('*');
        $total_items = $query->get()->count();
        
        $offset = ($page-1) * $per_page; 
        $total_pages = ceil ($total_items / $per_page);  

        $query =  Products::orderBy('products.id', 'desc');
        if($keyword != ''){
            $query->whereRaw($where);
        }
        $query->join('files', 'files.id', '=', 'products.featured_image', 'LEFT');
        $query->select('products.id', 'products.user_id', 'products.sku', 'products.product_title', 'products.slug', 'products.categories', 'files.thumbnail as featured_image', 'products.product_type', 'products.regular_price', 'products.wholesaler_price', 'products.stock_status', 'products.shipping_class', 'products.status', 'products.created_at');
        $query->limit($per_page);
        $query->offset($offset);
        $products = $query->get()->toArray();

        $productArr = [];
        if(!empty($products)){
            foreach($products as $key => $value){
                $categoryData = DB::table('categories')
                    ->select(DB::raw('group_concat(categories.title) as categories') )
                    ->whereIn('id', explode(',', $value['categories']))
                    ->first(); 
               
                $category = '';
                if($categoryData != null){
                    $category = explode(',', $categoryData->categories);
                    $category = implode(', ', $category);
                }

                $productArr[] = array(
                    'id' => $value['id'],
                    'user_id' => $value['user_id'],
                    'product_title' => ($value['product_title'] != '' ? $value['product_title'] : '—'),
                    'slug' => ($value['slug'] != '' ? $value['slug'] : '—'),
                    'sku' => ($value['sku'] != '' ? $value['sku'] : '—'),
                    'featured_image' => $value['featured_image'],
                    'regular_price' => ($value['regular_price'] != '' ? '$'.number_format($value['regular_price']) : '—'),
                    'wholesaler_price' => ($value['wholesaler_price'] != '' ? '$'.number_format($value['wholesaler_price']) : '—'),
                    'stock_status' => ($value['stock_status'] != '' ? $value['stock_status'] : '—'),
                    'status' => $value['status'],
                    'created_at' => date('M d, y', strtotime($value['created_at'])),
                    'categories' => ($category != '' ? $category : '—'),
                );

            }
        }

       

        return response()->json([
            'sts' => true,
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            'page' => $page,
            'items' => $productArr,
        ]);
    }

    public function details(Request $request){
        $product = Products::where('products.id', $request->id)
            ->join('files', 'files.id', '=', 'products.featured_image', 'LEFT')
            ->select('products.id', 'products.user_id', 'products.sku', 'products.product_title', 'products.slug', 'products.categories', 'products.product_content', 'products.featured_image', 'files.thumbnail', 'products.gallery_images', 'products.product_type', 'products.short_discription', 'products.regular_price', 'products.wholesaler_price', 'products.dimensions_length', 'products.dimensions_width', 'products.dimensions_height', 'products.weight', 'products.video_link', 'products.product_specification', 'products.make', 'products.model', 'products.model_year', 'products.stock_status', 'products.shipping_class', 'products.status')
            ->first();

        $gallery_images = Files::select('id', 'file_name', 'thumbnail', 'type')->whereIn('id', explode(',', $product->gallery_images))->get()->toArray();

        return response()->json([
            'sts' => true,
            'product' => $product,
            'gallery_images' => $gallery_images,
        ]);
    }

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'product_title' => 'required|max:255',
            //'dimensions_height' => "regex:/^\d+(\.\d{1,2})?$/"
        ]);
        $validate_error = [];
        if ($validator->fails()) {
            $errors = $validator->errors();
           
            if(!empty($errors->messages())){
                foreach($errors->messages() as $key => $error){
                    $validate_error[] = $error[0];
                }
            }
        }
        $floatPattern = "/^\d+(\.\d{1,2})?$/";
        if($request->weight != ''){
            if(preg_match($floatPattern, $request->weight) == false){
                $validate_error[] = 'Weight should be numaric or float number.';
            }
        }

        if($request->dimensions_length != ''){
            if(preg_match($floatPattern, $request->dimensions_length) == false){
                $validate_error[] = 'Length should be numaric or float number.';
            }
        }

        if($request->dimensions_width != ''){
            if(preg_match($floatPattern, $request->dimensions_width) == false){
                $validate_error[] = 'Width should be numaric or float number.';
            }
        }

        if($request->dimensions_height != ''){
            if(preg_match($floatPattern, $request->dimensions_height) == false){
                $validate_error[] = 'Height should be numaric or float number.';
            }
        }

        if($request->regular_price != ''){
            if(preg_match($floatPattern, $request->regular_price) == false){
                $validate_error[] = 'Regular price should be numaric or float number.';
            }
        }

        if($request->wholesaler_price != ''){
            if(preg_match($floatPattern, $request->wholesaler_price) == false){
                $validate_error[] = 'Sale price should be numaric or float number.';
            }

           
        }

        if(!empty($validate_error)){
            return response()->json([
                'sts' => false,
                'validate_error' => $validate_error
            ]);
        }

        $user_id = Auth::user()->id;
        $category_ids = $request->category_ids;
        $varitions = $request->varitions;

        $product = Products::updateOrCreate([
            'id' => $request->id
        ],[
            'user_id' => $user_id,
            'sku' => (isset($request->sku) ? $request->sku : ''),
            'product_title' => (isset($request->product_title) ? $request->product_title : ''),
            'slug' => SlugService::createSlug(Products::class, 'slug', $request->product_title),
            'categories' => (isset($category_ids) ? implode(',', $category_ids) : ''),
            'product_content' => (isset($request->product_content) ? $request->product_content : ''),
            'featured_image' => $request->featured_image,
            'gallery_images' => (isset($request->gallery_images) ? implode(',', $request->gallery_images) : ''),
            'product_type' => $request->product_type,
            'short_discription' => (isset($request->short_discription) ? $request->short_discription : ''),
            'regular_price' => (isset($request->regular_price) ? $request->regular_price : ''),
            'wholesaler_price' => (isset($request->wholesaler_price) ? $request->wholesaler_price : ''),
            'stock_status' => (isset($request->stock_status) ? $request->stock_status : 'instock'),
            'shipping_class' => (isset($request->shipping_class) ? $request->shipping_class : 0),
            'dimensions_length' => (isset($request->dimensions_length) ? $request->dimensions_length : ''),
            'dimensions_width' => (isset($request->dimensions_width) ? $request->dimensions_width : ''),
            'dimensions_height' => (isset($request->dimensions_height) ? $request->dimensions_height : ''),
            'weight' => (isset($request->weight) ? $request->weight : ''),
            'video_link' => (isset($request->video_link) ? $request->video_link : ''),
            'product_specification' => (isset($request->product_specification) ? $request->product_specification : ''),
            'make' => (isset($request->make) ? $request->make : 0),
            'model' => (isset($request->model) ? $request->model : 0),
            'model_year' => (isset($request->model_year) ? $request->model_year : 0),
            'status' => 'published',
        ]);

        if($product){
            if(!empty($varitions)){
                foreach($varitions as $key => $value){

                    $variationData = [
                        'id' => $value['variation_id'],
                        'product_id' => $product->id,
                        'unique_id' => '',
                        'variation_value' => implode(',', $value['variation_value']),
                        'image_id' => $value['image_id'],
                        'sku' => $value['variation_sku'],
                        'regular_price' => $value['variation_regular_price'],
                        'wholesaler_price' => $value['variation_wholesaler_price'],
                        'description' => $value['variation_description'],
                    ];
        
                    Variation::saveVariation($variationData);
                }
            }

            $productCategory = ProductCategory::where('product_id', $product->id)->delete();

            if(!empty($category_ids)){
                foreach($category_ids as $key => $category_id){
                    $data = [
                        'product_id' => $product->id,
                        'category_id' => $category_id,
                    ];
        
                    ProductCategory::create($data);
                }
            }
            else{
                $data = [
                    'product_id' => $product->id,
                    'category_id' => 0,
                ];
    
                ProductCategory::create($data);
            }

            return response()->json([
                'sts' => true,
                'msg' => 'Product has been successfully created.',
                'id' => $product->id
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'product not created'
            ]);
        }
        
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $product = Products::where('id', $id)->first();

        if($product == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Product not found.',
            ], 401);
        }

        if($product->delete()){
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

    public function getBestSellerProducts(Request $request){
        $products = Products::orderBy('products.id', $request->order)
            ->join('files', 'files.id', '=', 'products.featured_image', 'LEFT')
            ->select('products.id', 'products.user_id', 'products.sku', 'products.product_title', 'products.slug', 'products.categories', 'files.thumbnail as featured_image', 'products.product_type', 'products.regular_price', 'products.wholesaler_price', 'products.stock_status', 'products.shipping_class', 'products.status', 'products.created_at')
            ->where('products.status', 'published')
            ->limit(10)
            ->get()->toArray();

        
        return response()->json([
            'sts' => true,
            'result' => $products
        ]);
    }

    public function fecthProducts(Request $request){

        $where = "products.status='published'";
        $categoryData = array();
        if(isset($request->category_slug)){
            $categoryData = Categories::where('slug', $request->category_slug)
                ->first();
            if($categoryData !== null){
                $categoryid = $categoryData->id;
                $where .= " AND products.categories  LIKE '%".$categoryid."' ";
            }
        }

        $products = Products::orderBy('products.id', $request->orderby)
            ->join('files', 'files.id', '=', 'products.featured_image', 'LEFT')
            ->select('products.id', 'products.user_id', 'products.sku', 'products.product_title', 'products.slug', 'products.categories', 'files.thumbnail as featured_image', 'products.product_type', 'products.regular_price', 'products.wholesaler_price', 'products.stock_status', 'products.shipping_class', 'products.status', 'products.created_at')
            ->whereRaw($where)
            ->limit($request->limit)
            ->get()->toArray();       
        
        return response()->json([
            'sts' => true,
            'result' => $products,
            'category' => $categoryData
        ]);
    }

    public function fecthProductDetails(Request $request){
        $slug = $request->slug;
        $product = Products::join('files', 'files.id', '=', 'products.featured_image', 'LEFT')
            ->select('products.id', 'products.user_id', 'products.sku', 'products.product_title', 'products.slug', 'products.product_content', 'products.short_discription', 'products.categories', 'products.product_type', 'products.regular_price', 'products.wholesaler_price', 'products.dimensions_length', 'products.dimensions_width', 'products.dimensions_height', 'products.weight', 'products.video_link', 'products.product_specification', 'products.make', 'products.model', 'products.model_year', 'products.stock_status', 'products.shipping_class', 'products.gallery_images', 'files.thumbnail as featured_image', 'files.file_name as large_image', 'products.status', 'products.created_at')
            ->where('products.status', 'published')
            ->where('products.slug', $slug)
            ->first();
       
        $productArr = [];
        if($product != null){
            $product_add_ons = ProductAddOns::where('product_add_ons.product_id', $product->id)
                ->join('add_ons', 'add_ons.id', '=', 'product_add_ons.add_ons_id', 'INNER')
                ->select('add_ons.id', 'add_ons.title', 'add_ons.type')
                ->get()->toArray();

            $addOns = array();
            if(!empty($product_add_ons)){
                foreach($product_add_ons as $key => $value){
                    $addOnsarr = $value;
                    $addOnsarr['options'] = AddOnOptions::where('add_ons_id', $value['id'])->select('id','option_title', 'option_price')->get()->toArray();
                    $addOns[] = $addOnsarr;
                }
            }
            
            $gallery_images_ids = $product['gallery_images'];

            $gallery_images = Files::whereIn('id', explode(',', $gallery_images_ids))
                ->get()->toArray();

            $category_ids = $product['categories'];
            $category_Arr = explode(',', $category_ids);

            $related_product = array();
            if(!empty($category_Arr)){
                $relatedProducts = ProductCategory::whereIn('category_id', $category_Arr)
                    ->select(DB::raw('group_concat(product_id) as product_ids') )
                    ->groupBy('product_id')
                    ->where('product_id', '!=', $product['id'])
                    ->first();
                
                if(!empty($relatedProducts)){
                    $relatedids = $relatedProducts['product_ids'];
                    $relatedpro = Products::join('files', 'files.id', '=', 'products.featured_image', 'LEFT')
                        ->select('products.id', 'products.user_id', 'products.sku', 'products.product_title', 'products.slug', 'products.categories', 'products.product_type', 'products.regular_price', 'products.wholesaler_price', 'products.stock_status', 'products.gallery_images', 'products.shipping_class', 'files.thumbnail as featured_image', 'files.file_name as large_image', 'products.status', 'products.created_at')
                        ->where('products.status', 'published')
                        ->whereIn('products.id', explode(',', $relatedids))
                        ->limit(4)
                        ->get()->toArray();

                    if(!empty($relatedpro)){
                        
                        foreach($relatedpro as $key => $product_value){
                        
                            $related_product[] = array(
                                'id' => $product_value['id'],
                                'user_id' => $product_value['user_id'],
                                'product_title' => $product_value['product_title'],
                                'slug' => $product_value['slug'],
                                'sku' => ($product_value['sku'] != '' ? $product_value['sku'] : 'N/A'),
                                'featured_image' => $product_value['featured_image'],
                                'regular_price' => ($product_value['regular_price'] != '' ? '$'.number_format($product_value['regular_price']) : '$0.00'),
                                'wholesaler_price' => ($product_value['wholesaler_price'] != '' ? '$'.number_format($product_value['wholesaler_price']) : '$0.00'),
                                'stock_status' => $product_value['stock_status'],
                                'status' => $product_value['status'],
                                'created_at' => date('M d, y', strtotime($product_value['created_at'])),
                            );
            
                        }
                    }
                }
            }

            $categories = DB::table('categories')
                ->select('id','slug','title')
                ->whereIn('id', explode(',', $category_ids))
                ->get()->toArray(); 
        
            $short_discription = preg_replace( "/<br>|\n/", "", html_entity_decode($product['short_discription']));
            $product_content = preg_replace( "/<br>|\n/", "", html_entity_decode($product['product_content']));
            $product_specification = preg_replace( "/<br>|\n/", "", html_entity_decode($product['product_specification']));
           
            $productArr = array(
                'id' => $product['id'],
                'user_id' => $product['user_id'],
                'product_title' => $product['product_title'] ,
                'slug' => $product['slug'],
                'sku' => ($product['sku'] != '' ? $product['sku'] : 'N/A'),
                'product_content' => $product_content,
                'short_discription' => $short_discription,
                'categories' => $categories,
                'product_type' => $product['product_type'],
                'featured_image' => $product['featured_image'],
                'large_image' => $product['large_image'],
                'regular_price' => $product['regular_price'],
                'wholesaler_price' => $product['wholesaler_price'],
                'dimensions_length' => $product['dimensions_length'],
                'dimensions_width' => $product['dimensions_width'],
                'dimensions_height' => $product['dimensions_height'],
                'weight' => $product['weight'],
                'video_link' => $product['video_link'],
                'product_specification' => $product_specification,
                'make' => $product['make'],
                'model' => $product['model'],
                'model_year' => $product['model_year'],
                'stock_status' => $product['stock_status'],
                'gallery_images' => $gallery_images,
                'addOns' => $addOns,
                'status' => $product['status'],
                'related_product' => $related_product,
                'created_at' => date('M d, y', strtotime($product['created_at'])),
            );
        }
        
        return response()->json([
            'sts' => true,
            'result' => $productArr
        ]);
    }

    
    public function seacrhProducts(Request $request)
    {   
       $keyword = $request->q;
    
       $result = Products::where('product_title', 'LIKE', '%'.$keyword.'%')->select('product_title as label', 'id as value')->limit(10)->get()->toArray();
       return response()->json($result);
    }
}
