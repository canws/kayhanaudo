<?php

namespace App\Http\Controllers;

use App\Models\AddOnOptions;
use App\Models\AddOns;
use App\Models\Products;
use App\Models\ProductAddOns;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ProductAddOnsController extends Controller
{
    public function fetchAddOns(){
       
        $addons = AddOns::select('id', 'title', 'type')
            ->get()->toArray();

        $addonsArr = array();
        if(!empty($addons)){
            foreach($addons as $key => $value){
                $add_on = $value;
                $product_addons = ProductAddOns::where('add_ons_id', $value['id'])->get()->toArray();
                $product_ids = array_column($product_addons, 'product_id');
                $products = Products::whereIn('id', $product_ids)->select('product_title', 'id')->get()->toArray();
               
                $add_on['products'] = $products;

                $addonsArr[] = $add_on;
            }
        }

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $addonsArr,
        ]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'product_ids' => 'required',
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


        $option_ids = $request->option_ids;
        $option_titles = $request->option_titles;
        $option_prices = $request->option_prices;
        $floatPattern = "/^\d+(\.\d{1,2})?$/";
        if(!empty($option_ids)){
            foreach($option_ids as $key => $option_id){
                if(preg_match($floatPattern, $option_prices[$key]) == false){
                    $validate_error[] = 'Price should be numaric or float number.';
                    break;
                }
            }
        }

        
        if(!empty($validate_error)){
            return response()->json([
                'sts' => false,
                'validate_error' => $validate_error
            ]);
        }

        
        $product_ids = $request->product_ids;
        $product_ids = array_column($product_ids, 'value');

        $addons = AddOns::updateOrCreate(['id' => $request->id],[
            'title' => $request->title,
            'type' => $request->type,
        ]);

        if($addons){

            if(!empty($product_ids)){
                foreach($product_ids as $key => $product_id){
                    ProductAddOns::updateOrCreate([
                        'product_id' => $product_id,
                        'add_ons_id' => $addons->id,
                    ],[
                        'product_id' => $product_id,
                        'add_ons_id' => $addons->id,
                    ]);
                }
            }


            if(!empty($option_ids)){
                foreach($option_ids as $key => $option_id){
                    AddOnOptions::updateOrCreate(['id' => $option_id],[
                        'add_ons_id' => $addons->id,
                        'option_title' => $option_titles[$key],
                        'option_price' => $option_prices[$key],
                    ]);
                }
            }

            return response()->json([
                'sts' => true,
                'msg' => 'Add-ons has been successfully created.',
                'add_ons_id' => $addons->id,
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Add-ons not created. Please try again'
            ]);
        }
    }

    public function details(Request $request){
        $id = (int)$request->id;
        $addons = AddOns::where('id', $id)
            ->select('*')
            ->first();

        if($addons == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Add-ons not found.',
            ]);
        }
        $result['addons'] = $addons; 

        $product_addons = ProductAddOns::where('add_ons_id', $addons->id)->get()->toArray();
        $product_ids = array_column($product_addons, 'product_id');

        $result['product_ids'] = Products::whereIn('id', $product_ids)->select('product_title as label', 'id as value')->get()->toArray();
        $result['options'] = AddOnOptions::where('add_ons_id', $id)->select('id','option_title', 'option_price')->get()->toArray();


        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $result,
        ]);
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $addons = AddOns::where('id', $id)->first();

        if($addons == null){
            
            return response()->json([
                'sts' => false,
                'msg' => 'Add-ons not found.',
            ]);
        }

        if($addons->delete()){
            ProductAddOns::where('add_ons_id', $id)->delete();
            AddOnOptions::where('add_ons_id', $id)->delete();
            return response()->json([
                'sts' => true,
                'msg' => 'Successfully deleted',
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Some error occurred.',
            ]);
        }        
    }

    public function deleteAddonOptions(Request $request){
        $id = (int)$request->id;
        $option = AddOnOptions::where('id', $id)->first();

        if($option == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Add-ons option not found.',
            ]);
        }

        if($option->delete()){
           
            return response()->json([
                'sts' => true,
                'msg' => 'Successfully deleted',
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Some error occurred.',
            ]);
        }        
    }
}
