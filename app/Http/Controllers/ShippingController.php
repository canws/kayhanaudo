<?php

namespace App\Http\Controllers;

use App\Models\MethodClassCost;
use App\Models\Regions;
use App\Models\Shipping;
use App\Models\ShippingClass;
use App\Models\ShippingZoone;
use App\Models\ZooneMethod;
use App\Models\ZooneRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    // public function fetchShipping(){
       
    //     $shipping = Shipping::orderBy('shippings.id', 'desc')
    //         ->join('countries','countries.sortname', '=', 'shippings.country', 'LEFT')
    //         ->select('shippings.id', 'shippings.title', 'shippings.country as sortname', 'countries.name', 'shippings.shipping_amount', 'shippings.created_at')
    //         ->get()->toArray();

    //     return response()->json([
    //         'sts' => true,
    //         'msg' => '',
    //         'result' => $shipping,
    //     ]);
    // }

    public function fecthRegionOptions(Request $request)
    {   
       $keyword = $request->q;
    
       $result = Regions::orderBy('id', 'ASC')->where('name', 'LIKE', '%'.$keyword.'%')->whereOr('short_name', 'LIKE', '%'.$keyword.'%')->select('name as label', 'short_name as value')->limit(5)->get()->toArray();
       return response()->json($result);
    }

    // public function save(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'title' => 'required|max:255',
    //         'country' => 'required',
    //         'shipping_amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
    //     ]);

    //     if ($validator->fails()) {
    //         $errors = $validator->errors();
    //         return response()->json([
    //             'sts' => false,
    //             'validate_error' => $errors
    //         ]);
    //     }

    //     $res = Shipping::where('country', $request->country)->where('id', '!=', $request->id)->first();
    //     if($res != ''){
    //         return response()->json([
    //             'sts' => false,
    //             'msg' => 'You have already created shipping for this country.',
    //         ]);
    //     }
    //     $shipping = Shipping::updateOrCreate(['id' => $request->id],[
    //         'title' => $request->title,
    //         'country' => $request->country,
    //         'shipping_amount' => $request->shipping_amount
    //     ]);

    //     if($shipping){
    //         return response()->json([
    //             'sts' => true,
    //             'msg' => 'Shipping has been successfully created.'
    //         ]);
    //     }
    //     else{
    //         return response()->json([
    //             'sts' => false,
    //             'msg' => 'Shipping not created. Please try again'
    //         ]);
    //     }
    // }

    // public function details(Request $request){
    //     $id = (int)$request->id;
    //     $shipping = Shipping::where('shippings.id', $id)
    //         ->join('countries','countries.sortname', '=', 'shippings.country', 'LEFT')
    //         ->select('shippings.id', 'shippings.title', 'shippings.country as sortname', 'countries.name', 'shippings.shipping_amount', 'shippings.created_at')
    //         ->first();

    //     if($shipping == null){
    //         return response()->json([
    //             'sts' => false,
    //             'msg' => 'Shipping not found.',
    //         ]);
    //     }

    //     return response()->json([
    //         'sts' => true,
    //         'msg' => '',
    //         'result' => $shipping,
    //     ]);
    // }

    // public function delete(Request $request){
    //     $id = (int)$request->id;
    //     $shipping = Shipping::where('id', $id)->first();

    //     if($shipping == null){
    //         return response()->json([
    //             'sts' => false,
    //             'msg' => 'Shipping not found.',
    //         ]);
    //     }

    //     if($shipping->delete()){
    //         return response()->json([
    //             'sts' => true,
    //             'msg' => 'Successfully deleted',
    //         ]);
    //     }
    //     else{
    //         return response()->json([
    //             'sts' => false,
    //             'msg' => 'Some error occurred.',
    //         ]);
    //     }        
    // }
    
    public function fetchShippingClassess(){
       
        $shippingClasses = ShippingClass::orderBy('id', 'desc')
            ->select('id','name','slug','description')
            ->get()->toArray();

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $shippingClasses,
        ]);
    }

    public function saveShippingClassess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'slug' => 'required|max:255',
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

        if(!empty($validate_error)){
            return response()->json([
                'sts' => false,
                'validate_error' => $validate_error
            ]);
        }

        $res = ShippingClass::where('slug', $request->slug)->where('id', '!=', $request->id)->first();
        if($res != ''){
            return response()->json([
                'sts' => false,
                'msg' => 'Class already exists in system. Try again with different slug.',
            ]);
        }
        $shippingClass = ShippingClass::updateOrCreate(['id' => $request->id],[
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'shipping_cost' => '',
        ]);

        if($shippingClass){
            return response()->json([
                'sts' => true,
                'msg' => 'Shipping class has been successfully created.'
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Shipping class not created. Please try again'
            ]);
        }
    }

    public function detailsShippingClassess(Request $request){
        $id = (int)$request->id;
        $shippingClass = ShippingClass::where('id', $id)
            ->select('id','name','slug','description')
            ->first();

        if($shippingClass == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Shipping class not found.',
            ]);
        }

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $shippingClass,
        ]);
    }

    public function deleteShippingClassess(Request $request){
        $id = (int)$request->id;
        $shippingClass = ShippingClass::where('id', $id)->first();

        if($shippingClass == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Shipping class not found.',
            ]);
        }

        if($shippingClass->delete()){
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


    public function fetchShippingZoones(){
       
        
        $shippingZoones = ShippingZoone::orderBy('id', 'asc')
            ->select('id','name','regions')
            ->get()->toArray();

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $shippingZoones,
        ]);
    }

    public function saveShippingZoones(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'regions' => 'required',
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

        if(!empty($validate_error)){
            return response()->json([
                'sts' => false,
                'validate_error' => $validate_error
            ]);
        }

        $regions = $request->regions;
        $regions = array_column($regions, 'value');
 
        if(!empty($regions)){
            $zoone_region = ZooneRegion::whereIn('zoone_region', $regions)->where('zoone_id', '!=', $request->id)->get()->toArray();
            
            if($zoone_region != null){
                return response()->json([
                    'sts' => false,
                    'msg' => 'Region already set with another zoone.'
                ]);
            }
        }

        $shippingZoone = ShippingZoone::updateOrCreate(['id' => $request->id],[
            'name' => $request->name,
            'regions' => '',
        ]);



        if($shippingZoone){
            if(!empty($regions)){
            
                foreach($regions as $key => $region){
                    ZooneRegion::updateOrCreate([
                        'zoone_id' => $shippingZoone->id,
                        'zoone_region' => $region,
                    ],[
                        'zoone_id' => $shippingZoone->id,
                        'zoone_region' => $region,
                    ]);
                }
            }
           

            return response()->json([
                'sts' => true,
                'msg' => 'Shipping zoone has been successfully created.',
                'zonne_id' => $shippingZoone->id,
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Shipping zoone not created. Please try again'
            ]);
        }
    }

    public function detailsShippingZoones(Request $request){
        $id = (int)$request->id;
        $shippingZoone = ShippingZoone::where('id', $id)
            ->select('id','name','regions')
            ->first();

        if($shippingZoone == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Shipping zoone not found.',
            ]);
        }
        $result['zoones'] = $shippingZoone;
        $zoon_regions = ZooneRegion::where('zoone_regions.zoone_id', $shippingZoone->id)
            ->select('regions.name as label', 'regions.short_name as value')
            ->join('regions', 'regions.short_name', '=', 'zoone_regions.zoone_region', 'LEFT')
            ->get()->toArray();
        $result['zoon_regions'] = $zoon_regions;

        $result['methods'] = ZooneMethod::where('zoone_id', $shippingZoone->id)->get()->toArray();

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $result,
        ]);
    }

    public function deleteShippingZoones(Request $request){
        $id = (int)$request->id;
        $shippingZoone = ShippingZoone::where('id', $id)->first();

        if($shippingZoone == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Shipping zoone not found.',
            ]);
        }

        if($shippingZoone->delete()){
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
    

    public function saveZooneMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_method' => 'required',
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

        if(!empty($validate_error)){
            return response()->json([
                'sts' => false,
                'validate_error' => $validate_error
            ]);
        }

        if($request->shipping_method == 'flate_rate'){
            $method_title = 'Flate rate';
            $description = 'Lets you charge a fixed rate for shipping.';
            $free_shipping_requires = '';
        }
        else if($request->shipping_method == 'free'){
            $method_title = 'Free shipping';
            $description = 'Free shipping is a special method which can be triggered with coupons and minimum spends.';
            $free_shipping_requires = $request->free_shipping_requires;
        }
        else{
            $method_title = 'Local pickup';
            $description = 'Allow customers to pick up orders themselves. By default, when using local pickup store base taxes will apply regardless of customer address.';
            $free_shipping_requires = '';
        }
       
        $method = ZooneMethod::create([
            'zoone_id' => $request->zoone_id,
            'method_title' => $method_title,
            'shipping_cost' => '',
            'shipping_method' => $request->shipping_method,
            'description' => $description,
            'free_shipping_requires' => $free_shipping_requires,
            'status' => 'active',
        ]);



        if($method){
           
            return response()->json([
                'sts' => true,
                'msg' => 'Shipping method has been successfully created.',
                'zonne_id' => $request->zoone_id,
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Shipping method not created. Please try again'
            ]);
        }
    }

    public function UpdateStatusZooneMethod(Request $request)
    {
       
        $method = ZooneMethod::where('id', $request->method_id)->update([
            'status' => $request->status,
        ]);



        if($method){
           
            return response()->json([
                'sts' => true,
                'msg' => 'Status has been changed.',
                'zonne_id' => $request->zoone_id,
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Some error occurred. Please try again'
            ]);
        }
    }

    public function zoomMethosDetails(Request $request){
        $id = (int)$request->id;
        $method = ZooneMethod::where('id', $id)
            ->select('id','shipping_method','method_title','shipping_cost')
            ->first();

        if($method == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Method not found.',
            ]);
        }
        $result['method'] = $method;
        $classes = array();
        if($method->shipping_method == 'flate_rate'){
            $classes = ShippingClass::orderBy('id', 'desc')
                ->select('shipping_classes.id','shipping_classes.name','shipping_classes.slug', 'method_class_costs.shipping_cost')
                ->join('method_class_costs', 'method_class_costs.class_id', '=','shipping_classes.id', 'LEFT')
                ->where('method_class_costs.method_id', $method->id)
                ->get()->toArray();

            if(empty($classes)){
                $classes = ShippingClass::orderBy('id', 'desc')
                    ->select('shipping_classes.id','shipping_classes.name','shipping_classes.slug')
                    ->get()->toArray();
            }
        }
        $result['classes'] = $classes;   

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $result,
        ]);
    }

    public function UpdateZooneMethod(Request $request)
    {

        $inputData = $request->input();
        $validate_error = [];

        $methodData = ZooneMethod::where('id', $request->method_id)
            ->select('*')
            ->first();

        if($inputData['method_title'] == ''){
            $validate_error[] = 'Metho title field is required.';
        }

        $floatPattern = "/^\d+(\.\d{1,2})?$/";

        if($methodData->shipping_method == 'flate_rate'){
            if($inputData['shipping_cost'] == ''){
                $validate_error[] = 'Cost field is required.';
            }
            else{
                if(preg_match($floatPattern, $request->shipping_cost) == false){
                    $validate_error[] = 'Cost should be numaric or float number.';
                }
            }
       
            foreach ($inputData['class_shipping_cost'] as $key => $cost) {
                if($cost != ''){
                    if(preg_match($floatPattern, $cost) == false){
                        $validate_error[] = 'Cost should be numaric or float number.';
                    }
                }
            }
        }

        if(!empty($validate_error)){
            return response()->json([
                'sts' => false,
                'validate_error' => $validate_error
            ]);
        }

        
        if($methodData == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Method not exist in system'
            ]);
        }

        

        if($methodData->shipping_method  == 'flate_rate'){
            $free_shipping_requires = '';
        }
        else if($methodData->shipping_method == 'free'){
            $free_shipping_requires = $request->free_shipping_requires;
        }
        else{
            $free_shipping_requires = '';
        }

        $method = ZooneMethod::where('id', $request->method_id)->update([
            'method_title' => $request->method_title,
            'shipping_cost' => (isset($inputData['shipping_cost']) ? $inputData['shipping_cost'] : ''),
            'free_shipping_requires' => $free_shipping_requires,
        ]);

        if($method){
            if($methodData->shipping_method == 'flate_rate'){
                $shipping_class_ids = $request->shipping_class_ids;
                $class_shipping_cost = $request->class_shipping_cost;
              
                foreach ($shipping_class_ids as $key => $class_id) {
                    MethodClassCost::updateOrCreate([
                        'method_id' => $request->method_id,
                        'class_id' => $class_id,
                    ],[
                        'method_id' => $request->method_id,
                        'class_id' => $class_id,
                        'shipping_cost' => $class_shipping_cost[$key]
                    ]);
                }
            }

            return response()->json([
                'sts' => true,
                'msg' => 'Method has been updated.',
                'zonne_id' => $methodData->zoone_id,
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Some error occurred. Please try again'
            ]);
        }
    }

}
