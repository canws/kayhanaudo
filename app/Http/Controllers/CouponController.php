<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Products;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function fetchCoupons(){
       
        $coupons = Coupon::orderBy('id', 'desc')
        ->get()->toArray();

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $coupons,
        ]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|max:255',
            'coupon_amount' => "required|regex:/^\d+(\.\d{1,2})?$/"
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

        if($request->discount_type == 'percent'){
            if($request->coupon_amount > 100){
                return response()->json([
                    'sts' => false,
                    'msg' => 'Amount value should be less than and equal to 100 in case of percentage.'
                ]);
            }
           
        }

        $coupon = Coupon::where('coupon_code', $request->coupon_code)->where('id', '!=', $request->id)
            ->select('*')
            ->first();
        if($coupon != null){
            return response()->json([
                'sts' => false,
                'msg' => 'Coupon code already taken'
            ]);
        }

        $includeProducts = array();
        if(!empty($request->includeProductIds)){
            $includeProductIds = $request->includeProductIds;
            $includeProducts = array_column($includeProductIds, 'value');
        }

        $excludeProducts = array();
        if(!empty($request->excludeProductIds)){
            $excludeProductIds = $request->excludeProductIds;
            $excludeProducts = array_column($excludeProductIds, 'value');
        }
       

        $res = Coupon::updateOrCreate(['id' => $request->id],[
            'coupon_code' => $request->coupon_code,
            'description' => $request->description,
            'coupon_amount' => $request->coupon_amount,
            'discount_type' => $request->discount_type,
            'free_shipping' => $request->free_shipping,
            'expiry_date' => date('Y-m-d', strtotime($request->expiry_date)),
            'minimum_spend' => $request->minimum_spend,
            'maximum_spend' => $request->maximum_spend,
            'individual_use' => $request->individual_use,
            'exclude_sale_items' => $request->exclude_sale_items,
            'includeProductIds' => implode(',', $includeProducts),
            'excludeProductIds' => implode(',', $excludeProducts),
            'usage_limit' => (isset($request->usage_limit) ? $request->usage_limit : ''),
        ]);

        if($res){
            return response()->json([
                'sts' => true,
                'msg' => 'Coupon has been successfully created.',
                'coupon_id' => $res->id
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Coupon not created. Please try again'
            ]);
        }
    }

    public function details(Request $request){
        $id = (int)$request->id;
        $coupon = Coupon::where('id', $id)->first();

        if($coupon == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Coupon not found.',
            ]);
        }

        $includeProducts = Products::whereIn('id', explode(',',$coupon->includeProductIds))->select('product_title as label', 'id as value')->get()->toArray();
        $excludeProducts = Products::whereIn('id', explode(',',$coupon->excludeProductIds))->select('product_title as label', 'id as value')->get()->toArray();
       
        $couponDetails = array(
            'coupon_code' => $coupon->coupon_code,
            'description' => $coupon->description,
            'coupon_amount' => $coupon->coupon_amount,
            'discount_type' => $coupon->discount_type,
            'minimum_spend' => $coupon->minimum_spend,
            'maximum_spend' => $coupon->maximum_spend,
            'free_shipping' => $coupon->free_shipping,
            'individual_use' => $coupon->individual_use,
            'exclude_sale_items' => $coupon->exclude_sale_items,
            'usage_limit' => $coupon->usage_limit,
            'includeProducts' => $includeProducts,
            'excludeProducts' => $excludeProducts,
            'expiry_date' => date('m/d/Y', strtotime($coupon->expiry_date))
        );

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $couponDetails,
        ]);
    }

    public function delete(Request $request){
        $id = (int)$request->id;
        $coupon = Coupon::where('id', $id)->first();

        if($coupon == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Coupon not found.',
            ]);
        }

        if($coupon->delete()){
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
