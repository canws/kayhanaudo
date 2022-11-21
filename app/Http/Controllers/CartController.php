<?php

namespace App\Http\Controllers;

use App\Models\AddOnOptions;
use App\Models\AddOns;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\MethodClassCost;
use App\Models\Products;
use App\Models\Regions;
use App\Models\ShippingZoone;
use App\Models\ZooneMethod;
use App\Models\ZooneRegion;
use Illuminate\Http\Request;
use Session;
use Auth;

class CartController extends Controller
{
    public function saveCartItems(Request $request){
        $shipping_cost = 0;
        $shippingAddress = '';
        $flateMethodes = [];
        $localMethodes = [];
        $freeMethodes = [];

        $cartItems = $request->cartItems;
     
        $isloggedin = (isset($request->isloggedin) ? $request->isloggedin : false);
        $items = array();
       
        if($isloggedin == true){
            $user_id = auth()->user()->id;

            Cart::where('user_id', $user_id)->delete();
            if(!empty($cartItems)){
                
                foreach($cartItems as $key => $value){
                    Cart::create(
                    [
                        'user_id' => $user_id,
                        'product_id' => $value['product_id'],
                        'quantity' => $value['quantity'],
                        'price' => $value['price'],
                        'item_data' => json_encode($value),
                    ]);
                }
            }

            $result = Cart::where('carts.user_id', $user_id)
                ->join('products', 'products.id', '=', 'carts.product_id','INNER')
                ->join('files', 'files.id', '=', 'products.featured_image', 'LEFT')
                ->select('carts.id', 'carts.product_id', 'carts.quantity', 'carts.price', 'carts.item_data', 'products.sku', 'products.product_title', 'products.slug', 'products.shipping_class', 'files.thumbnail as featured_image')
                ->get()->toArray();

            $shippingClasses = [];
            if(!empty($result)){
                foreach($result as $key =>$value){
                    $item_data = json_decode($value['item_data']);
                    if($value['shipping_class'] > 0){
                        $shippingClasses[] = $value['shipping_class'];
                    }
                   
                    $extra_options = $item_data->extra_options;
                    $addonsData = array();
                    if(!empty($extra_options)){
                        $addons_ids = array_column($extra_options, 'addons_id');
                        $addons = AddOns::whereIn('id',$addons_ids)->select('id', 'title', 'type')->get()->toArray();
                        
                        if(!empty($addons)){
                            foreach($addons as $k => $addons_val){
                                $optionids = $extra_options[$k]->optionids;
                                
                                $options = array();
                                if(!empty($optionids)){
                                    $options = AddOnOptions::whereIn('id',$optionids)->select('id', 'add_ons_id', 'option_title', 'option_price')->get()->toArray();
                                }
                                
                                $addons_val['options'] = $options;
                                $addonsData[] = $addons_val;
                            }
                        }
                        
                    }
                    

                    $items[] = array(
                        'id' => $value['id'],
                        'product_id' => $value['product_id'],
                        'quantity' => $value['quantity'],
                        'price' => $value['price'],
                        'extra_options' => $addonsData,
                        'sku' => $value['sku'],
                        'slug' => $value['slug'],
                        'product_title' => $value['product_title'],
                        'featured_image' => $value['featured_image'],
                    );
                }
            }
            if($request->address == null){
                $shippingAddress = Address::where('user_id', $user_id)->where('type', 'shipping')->first();
                if($shippingAddress != null){
                    $address = array(
                        'country' => $shippingAddress['country'],
                        'state' => $shippingAddress['state'],
                        'city' => $shippingAddress['city'],
                    );
                }
                else{
                    $address = array();
                }
            }
            else  if($request->address == 'undefined'){
                $address = array();
            }
            else{
                $shippingAddress = explode(', ', $request->address);
                $address = array(
                    'country' => $shippingAddress[2],
                    'state' => $shippingAddress[1],
                    'city' => $shippingAddress[0],
                );
            }
            if(!empty($address)){
                $shippingData = $this->calculateShippingCost($user_id,$shippingClasses,$address);
            }
            else{
                $shippingData = $this->calculateShippingCost($user_id,$shippingClasses);
            }
           
            
        }
        else{
          
            if(!empty($cartItems)){
                $product_ids = array_column($cartItems, 'product_id');
              
                $result = Products::whereIn('products.id', $product_ids)
                    ->join('files', 'files.id', '=', 'products.featured_image', 'LEFT')
                    ->select('products.id','products.sku', 'products.product_title', 'products.slug', 'products.shipping_class', 'files.thumbnail as featured_image' )
                    ->get()->toArray();
                
                $shippingClasses = [];
                    
                if(!empty($result)){
                    foreach($result as $key =>$value){

                        if($value['shipping_class'] > 0){
                            $shippingClasses[] = $value['shipping_class'];
                        }
                        
                        $extra_options = $cartItems[$key]['extra_options'];
                        $addonsData = array();
                        if(!empty($extra_options)){
                            $addons_ids = array_column($extra_options, 'addons_id');
                            $addons = AddOns::whereIn('id',$addons_ids)->select('id', 'title', 'type')->get()->toArray();
                           
                            if(!empty($addons)){
                                foreach($addons as $k => $addons_val){
                                    $optionids = $extra_options[$k]->optionids;
                                   
                                    $options = array();
                                    if(!empty($optionids)){
                                        $options = AddOnOptions::whereIn('id',$optionids)->select('id', 'add_ons_id', 'option_title', 'option_price')->get()->toArray();
                                    }
                                   
                                    $addons_val['options'] = $options;
                                    $addonsData[] = $addons_val;
                                }
                            }
                            
                        }

                        $items[] = array(
                            'id' => 0,
                            'product_id' => $value['id'],
                            'quantity' => $cartItems[$key]['quantity'],
                            'price' => $cartItems[$key]['price'],
                            'extra_options' => $addonsData,
                            'sku' => $value['sku'],
                            'slug' => $value['slug'],
                            'product_title' => $value['product_title'],
                            'featured_image' => $value['featured_image'],
                        );
                    }
                }
                if($request->address != null){
                    $shippingAddress = explode(', ', $request->address);
                 
                    $address = array(
                        'country' => $shippingAddress[2],
                        'state' => $shippingAddress[1],
                        'city' => $shippingAddress[0],
                    );
                    $shippingData = $this->calculateShippingCost(0,$shippingClasses, $address);
                }
                else{
                    $shippingData = $this->calculateShippingCost(0,$shippingClasses);
                }

                
            }
        }

        $countries = Country::select('id','sortname','name')->get()->toArray();
        

        return response()->json([
            'sts' => true,
            'msg' => '',
            'items' => $items,
            'shippingAddress' => $shippingData['shippingAddress'],
            'flate_methodes' =>  $shippingData['flateMethodes'],
            'localMethodes' =>  $shippingData['localMethodes'],
            'freeMethodes' =>  $shippingData['freeMethodes'],
            'shipping_cost' =>  $shippingData['shipping_cost'],
            'countries' => $countries,
            'city' => $shippingData['city'],
            'state' => $shippingData['state'],
            'country' => $shippingData['country'],
        ]);
    }

    private function calculateShippingCost($user_id=0,$shippingClasses,$shippingAddress=null){
        $zoone = null;
        $flateMethodes = array();
        $localMethodes = array();
        $freeMethodes = array();
        $shipping_cost = 0;

        $country = '';
        $state = '';
        $city = '';
        $zoone_id = 0;
        
        if($shippingAddress == null){
            $zoone = ShippingZoone::orderby('id', 'asc')
                ->select('id', 'name')
                ->first();
            $zoone_id = $zoone->id;

        }
        else{
            $country = $shippingAddress['country'];
            $state = $shippingAddress['state'];
            $region = Regions::where('name', $country)->first();
           
            if($region == null){
                $name = $state.', '.$country;
                $region = Regions::where('name', $name)->first();
                if($region != null){
                    $zoone_id = $region->id;
                }
            }

            if($region != null){
                $zoone = ZooneRegion::where('zoone_region', $region->short_name)
                    ->select('id', 'zoone_id', 'zoone_region')
                    ->first();
                if($zoone != null){
                    $zoone_id = $zoone->zoone_id;
                }
            }
        }

        if($zoone_id == 0){
            $zoone = ShippingZoone::orderby('id', 'asc')
            ->select('id', 'name')
            ->first();
            $zoone_id = $zoone->id;
        }

        if($zoone != null){
            $flateMethodes = ZooneMethod::getMethodDetailsByZooneId('flate_rate',$zoone_id);
           
            if($flateMethodes != null){
                $method_shipping_cost = $flateMethodes->shipping_cost;
                if(!empty($shippingClasses)){
                    $shippingClassesCost = MethodClassCost::where('method_id', $flateMethodes->id)
                    ->whereIn('class_id',$shippingClasses)
                    ->select('shipping_cost')
                    ->get()->toArray();
                }
                else{
                    $shippingClassesCost = MethodClassCost::where('method_id', $flateMethodes->id)
                    ->select('shipping_cost')
                    ->get()->toArray();
                }
              
                $shippingClassesCost = array_column($shippingClassesCost, 'shipping_cost');

              
                
                $shipping_cost = max($shippingClassesCost);

                if($shipping_cost < $method_shipping_cost){
                    $shipping_cost = $method_shipping_cost;
                }
            }

            $localMethodes = ZooneMethod::getMethodDetailsByZooneId('local',$zoone_id);
            $freeMethodes = ZooneMethod::getMethodDetailsByZooneId('free',$zoone_id);
            
            if($shippingAddress == null){
                $zooneRegion = ZooneRegion::orderby('zoone_regions.id', 'asc')
                    ->join('regions', 'regions.short_name', '=', 'zoone_regions.zoone_region', 'INNER')
                    ->where('zoone_regions.zoone_id', $zoone_id)
                    ->select('zoone_regions.zoone_id','zoone_regions.zoone_region', 'regions.name')
                    ->first();

                $address = $zooneRegion->name;
                $addressArr = explode(', ', $address);
                $city = '';
                if(count($addressArr) == 2){
                    $state = $addressArr[0];
                    $country = $addressArr[1];
                }
                else{
                    $state = '';
                    $country = $addressArr[0];
                }
                
            }
            else{
                $city = $shippingAddress['city'];
                $state = $shippingAddress['state'];
                $country = $shippingAddress['country'];

                $address = $city.', '.$state.', '.$country;
            }
           
    
        }

        return array(
            'shipping_cost' => $shipping_cost,
            'flateMethodes' => ($flateMethodes != null ? $flateMethodes : array()),
            'localMethodes' => ($localMethodes != null ? $localMethodes : array()),
            'freeMethodes' => ($freeMethodes != null ? $freeMethodes : array()),
            'shippingAddress' => $address,
            'city' => $city,
            'state' => $state,
            'country' => $country,
        );
    }

    public function updateShippingAddress(Request $request){
        $country = $request->country;
        $state = $request->state;
        $city = $request->city;
        $cartItems = $request->cartItems;

        if(!empty($cartItems)){
            $product_ids = array_column($cartItems, 'product_id');
          
            $result = Products::whereIn('products.id', $product_ids)
                ->join('files', 'files.id', '=', 'products.featured_image', 'LEFT')
                ->select('products.id','products.sku', 'products.product_title', 'products.slug', 'products.shipping_class', 'files.thumbnail as featured_image' )
                ->get()->toArray();
            
            $shippingClasses = [];
                
            if(!empty($result)){
                foreach($result as $key =>$value){

                    if($value['shipping_class'] > 0){
                        $shippingClasses[] = $value['shipping_class'];
                    }

                }
            }
        }

        $address = array(
            'country' => $country,
            'state' => $state,
            'city' => $city,
        );
        $shippingData = $this->calculateShippingCost(0,$shippingClasses, $address);

        return response()->json([
            'sts' => true,
            'msg' => '',
            'shippingAddress' => $shippingData['shippingAddress'],
            'flate_methodes' =>  $shippingData['flateMethodes'],
            'localMethodes' =>  $shippingData['localMethodes'],
            'freeMethodes' =>  $shippingData['freeMethodes'],
            'shipping_cost' =>  $shippingData['shipping_cost'],
        ]);
    }

    public function removeCartItems(Request $request){
        $id = $request->item_id; 
        $cartItem = Cart::where('id', $id)->first();
        if($cartItem == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Item no longer in cart.',
            ]);
        }

        if($cartItem->delete()){
            return response()->json([
                'sts' => true,
                'msg' => 'Item has been successfully deleted.',
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Some error accoured.',
            ]);
        }
    }

    public function getCartItems(Request $request){

        $user_id = auth()->user()->id;
        $result = Cart::where('user_id', $user_id)
                ->select('product_id', 'quantity', 'price', 'item_data')
                ->get()->toArray();

        $cartItems = array();
        if(!empty($result)){
            foreach($result as $key => $value){
                $cartItems[] =  json_decode($value['item_data']);
            }
        }

        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $cartItems,
        ],);
        
    }


    public function applyCoupon(Request $request){
        $coupon_code = $request->coupon;
        $cartItems = $request->cartItems;
        $product_ids = array_column($cartItems, 'product_id');
        

        $couponData = Coupon::where('coupon_code', $coupon_code)->first();
        
        if($couponData == null){
            return response()->json([
                'sts' => false,
                'msg' => 'This coupon code is invalid.',
            ]);
        }

        $current_date = date('Y-m-d');
        $expiry_date = $couponData->expiry_date;
        if($expiry_date < $current_date){
            return response()->json([
                'sts' => false,
                'msg' => 'This coupon code is invalid or has expired',
            ]);
        }


        $totalCartPrice = 0;
        if(!empty($cartItems)){
            foreach($cartItems as $key => $item){
                $totalCartPrice = $totalCartPrice+($item['price']*$item['quantity']);
            }
        }

        $discount_type = $couponData->discount_type;
        $free_shipping = $couponData->free_shipping;
        $coupon_amount = $couponData->coupon_amount;
        $minimum_spend = $couponData->minimum_spend;
        $maximum_spend = $couponData->maximum_spend;
        $individual_use = $couponData->individual_use;
        $exclude_sale_items = $couponData->exclude_sale_items;
        $includeProductIds = $couponData->includeProductIds;
        $excludeProductIds = $couponData->excludeProductIds;
        $usage_limit = $couponData->usage_limit;

        if($totalCartPrice < $minimum_spend){
            return response()->json([
                'sts' => false,
                'msg' => 'For this coupon minimum spend $'.$minimum_spend,
            ]);
        }

        if($totalCartPrice > $maximum_spend){
            return response()->json([
                'sts' => false,
                'msg' => 'This coupon is allowed with maximimum spaned $'.$maximum_spend,
            ]);
        }

        if($discount_type == 'fixed_cart'){
            $discount_amount = $coupon_amount;
            $final_total = $totalCartPrice-$discount_amount;
        }
        else{
            $percentage = $coupon_amount;
            $discount_amount = ($percentage / 100) * $totalCartPrice;
            $final_total = $totalCartPrice - $discount_amount;
        }
        $applycoupon['coupon_code'] = $coupon_code;
        $applycoupon['discount_amount'] = $discount_amount;
        return response()->json([
            'sts' => true,
            'msg' => 'successfully applied',
            'applycoupon' => $applycoupon,
            'discount_amount' => $discount_amount,
        ]);

    }
}
