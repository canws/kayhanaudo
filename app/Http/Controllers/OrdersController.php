<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CouponAplied;
use App\Models\OrderProducts;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;


class OrdersController extends Controller
{
    public function save(Request $request){
        $inputData = $request->input();
        $cartItems = $inputData['cartItems'];
     
        $user_id = (int)$inputData['user_id'];
        $token = null;
        $billing_address = array(
            'first_name' => (isset($inputData['first_name']) ? $inputData['first_name'] : ''),
            'last_name' => (isset($inputData['last_name']) ? $inputData['last_name'] : ''),
            'email' => (isset($inputData['email']) ? $inputData['email'] : ''),
            'company_name' => (isset($inputData['company_name']) ? $inputData['company_name'] : ''),
            'street_address_1' => (isset($inputData['street_address_1']) ? $inputData['street_address_1'] : ''),
            'street_address_2' => (isset($inputData['street_address_2']) ? $inputData['street_address_2'] : ''),
            'postcode' => (isset($inputData['postcode']) ? $inputData['postcode'] : ''),
            'phone' => (isset($inputData['phone']) ? $inputData['phone'] : ''),
            'country' => (isset($inputData['country']) ? $inputData['country'] : ''),
            'state' => (isset($inputData['state']) ? $inputData['state'] : ''),
            'city' => (isset($inputData['city']) ? $inputData['city'] : ''),
        );

        if($inputData['ship_to_different'] == ''){
            $shipping_address = $billing_address;
        }
        else{
            $shipping_address =  array(
                'first_name' => (isset($inputData['shipping_first_name']) ? $inputData['shipping_first_name'] : ''),
                'last_name' => (isset($inputData['shipping_last_name']) ? $inputData['shipping_last_name'] : ''),
                'email' => (isset($inputData['email']) ? $inputData['email'] : ''),
                'company_name' => (isset($inputData['shipping_company_name']) ? $inputData['shipping_company_name'] : ''),
                'street_address_1' => (isset($inputData['shipping_street_address_1']) ? $inputData['shipping_street_address_1'] : ''),
                'street_address_2' => (isset($inputData['shipping_street_address_2']) ? $inputData['shipping_street_address_2'] : ''),
                'postcode' => (isset($inputData['shipping_postcode']) ? $inputData['shipping_postcode'] : ''),
                'phone' => (isset($inputData['phone']) ? $inputData['phone'] : ''),
                'country' => (isset($inputData['shipping_country']) ? $inputData['shipping_country'] : ''),
                'state' => (isset($inputData['shipping_state']) ? $inputData['shipping_state'] : ''),
                'city' => (isset($inputData['shipping_city']) ? $inputData['shipping_city'] : ''),
            );
        }


        if($inputData['user_id'] == 0){

            $isPhone = User::where('email', $request->email)->first();
            if($isPhone != null){
                return response()->json([
                    'sts' => false,
                    'msg' => 'The email has been already taken.'
                ]);
            }

            $isPhone = User::where('phone', $request->phone)->first();
            if($isPhone != null){
                return response()->json([
                    'sts' => false,
                    'msg' => 'The phone has been already taken.'
                ]);
            }
            
            $emailArr = explode('@', $request->email);
            $username = $emailArr[0].''.$request->phone;
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $username,
                'phone' => $request->phone,
                'role' => 'customer',
                'password' => Hash::make($request->password),
            ]);

            if($user == null){
                return response()->json([
                    'sts' => false,
                    'msg' => 'Account not created. Please try again'
                ]);
            }

            $user_id = $user->id;

            
            $user = User::where('id', $user_id)->firstOrFail();

            $token = $user->createToken('auth_token')->plainTextToken;
        
        }

        $order = Orders::create([
            'user_id' => $user_id,
            'order_id' =>  (isset($inputData['order_id']) ? $inputData['order_id'] : ''),
            'amount' => (isset($inputData['amount']) ? $inputData['amount'] : ''),
            'currency' => (isset($inputData['currency_code']) ? $inputData['currency_code'] : ''),
            'payment_mode' => (isset($inputData['payment_mode']) ? $inputData['payment_mode'] : ''),
            'payment_date' => date('Y-m-d H:i:s', strtotime($inputData['create_time'])),
            'discount_amount' => (isset($inputData['discount_amount']) ? $inputData['discount_amount'] : ''),
            'shipping_cost' => (isset($inputData['shipping_cost']) ? $inputData['shipping_cost'] : ''),
            'shipping_method' => $inputData['shipping_method'],
            'status' => $inputData['status'],
            'billing_address' => serialize($billing_address),
            'shipping_address' => serialize($shipping_address),
            'payer_details' => serialize($inputData['payer_details']),
            'ordernote' => (isset($inputData['ordernote']) ? $inputData['ordernote'] : ''),
        ]);

        if($order){

           $shipping_address = Address::updateOrCreate([
                'user_id' => $user_id,
                'type' => 'shipping',
            ],[
                'user_id' => $user_id,
                'type' => 'shipping',
                'first_name' => (isset($inputData['shipping_first_name']) ? $inputData['shipping_first_name'] : ''),
                'last_name' => (isset($inputData['shipping_last_name']) ? $inputData['shipping_last_name'] : ''),
                'email' => (isset($inputData['email']) ? $inputData['email'] : ''),
                'company_name' => (isset($inputData['shipping_company_name']) ? $inputData['shipping_company_name'] : ''),
                'street_address_1' => (isset($inputData['shipping_street_address_1']) ? $inputData['shipping_street_address_1'] : ''),
                'street_address_2' => (isset($inputData['shipping_street_address_2']) ? $inputData['shipping_street_address_2'] : ''),
                'postcode' => (isset($inputData['shipping_postcode']) ? $inputData['shipping_postcode'] : ''),
                'phone' => (isset($inputData['phone']) ? $inputData['phone'] : ''),
                'country' => (isset($inputData['shipping_country']) ? $inputData['shipping_country'] : ''),
                'state' => (isset($inputData['shipping_state']) ? $inputData['shipping_state'] : ''),
                'city' => (isset($inputData['shipping_city']) ? $inputData['shipping_city'] : ''),

               

            ]);

            $billing_address = Address::updateOrCreate([
                'user_id' => $user_id,
                'type' => 'billing',
            ],[
                'user_id' => $user_id,
                'type' => 'billing',
                'first_name' => (isset($inputData['first_name']) ? $inputData['first_name'] : ''),
                'last_name' => (isset($inputData['last_name']) ? $inputData['last_name'] : ''),
                'email' => (isset($inputData['email']) ? $inputData['email'] : ''),
                'company_name' => (isset($inputData['company_name']) ? $inputData['company_name'] : ''),
                'street_address_1' => (isset($inputData['street_address_1']) ? $inputData['street_address_1'] : ''),
                'street_address_2' => (isset($inputData['street_address_2']) ? $inputData['street_address_2'] : ''),
                'postcode' => (isset($inputData['postcode']) ? $inputData['postcode'] : ''),
                'phone' => (isset($inputData['phone']) ? $inputData['phone'] : ''),
                'country' => (isset($inputData['country']) ? $inputData['country'] : ''),
                'state' => (isset($inputData['state']) ? $inputData['state'] : ''),
                'city' => (isset($inputData['city']) ? $inputData['city'] : ''),
            ]);

            $product_ids = $inputData['product_ids'];

            if(!empty($cartItems)){
                foreach($cartItems as $key => $item){
                    OrderProducts::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'extra_options' => serialize($item['extra_options']),
                    ]);
                    if($item['id'] > 0){
                        Cart::where('id', $item['id'])->delete();
                    }
                }
            }

            if($inputData['coupon'] != ''){
                CouponAplied::create([
                    'user_id' => $user_id,
                    'order_id' => $order->id,
                    'coupon_id' => $inputData['coupon'],
                ]);
            }
           


            return response()->json([
                'sts' => true,
                'order_id' => $order->order_id,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'msg' => 'Successfully created.'
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Order not created. Please try again'
            ]);
        }
      
    }

    public function orderDetails($order_id=null){
        if($order_id == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Order not found.'
            ]);
        }

        $order = Orders::where('order_id', $order_id)->first();

        if($order == null){
            return response()->json([
                'sts' => false,
                'msg' => 'Order not found.'
            ]);
        }

        $orderData = array(
            'id' => $order->id,
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'payment_mode' => $order->payment_mode,
            'payment_date' => date('M d, Y', strtotime($order->payment_date)),
            'discount_amount' => $order->discount_amount,
            'shipping_cost' => $order->shipping_cost,
            'shipping_method' => $order->shipping_method,
            'status' => $order->status,
            'billing_address' => unserialize($order->billing_address),
            'shipping_address' => unserialize($order->shipping_address),
            'payer_details' => unserialize($order->payer_details),
            'ordernote' => $order->ordernote,
            'created_at' => $order->created_at,
        );

        $order_products = OrderProducts::where('order_products.order_id', $order->id)
            ->join('products', 'products.id', '=', 'order_products.product_id', 'LEFT')
            ->select('order_products.id as order_product_id', 'order_products.product_id', 'order_products.quantity', 'order_products.price', 'order_products.extra_options', 'products.product_title', 'products.slug')
            ->get()->toArray();

            $orderProducts = [];
            if(!empty($order_products)){
                foreach($order_products as $key => $value){
                    $orderProducts[] = array(
                        'order_product_id' => $value['order_product_id'],
                        'product_id' => $value['product_id'],
                        'product_title' => $value['product_title'],
                        'slug' => $value['slug'],
                        'quantity' => $value['quantity'],
                        'price' => $value['price'],
                        'extra_options' => unserialize($value['extra_options']),
                    );
                }
            }
        

        return response()->json([
            'sts' => true,
            'order' => $orderData,
            'order_products' => $orderProducts,
            'msg' => ''
        ]);
    }

    public function orders(){
       
        $orders = Orders::orderBy('id', 'desc')->get()->toArray();
        $orderData = array();
        if(!empty($orders)){
            foreach($orders as $key => $order){
                $orderData[] = array(
                    'id' => $order['id'],
                    'order_id' => $order['order_id'],
                    'user_id' => $order['user_id'],
                    'amount' => $order['amount'],
                    'currency' => $order['currency'],
                    'payment_mode' => $order['payment_mode'],
                    'payment_date' => date('M d, Y', strtotime($order['payment_date'])),
                    'discount_amount' => $order['discount_amount'],
                    'shipping_cost' => $order['shipping_cost'],
                    'shipping_method' => $order['shipping_method'],
                    'status' => $order['status'],
                    'billing_address' => unserialize($order['billing_address']),
                    'shipping_address' => unserialize($order['shipping_address']),
                    'payer_details' => unserialize($order['payer_details']),
                    'ordernote' => $order['ordernote'],
                    'created_at' => $order['created_at'],
                );
            }
        }

        return response()->json([
            'sts' => true,
            'result' => $orderData,
        ]);
    }

    public function importOrders(Request $request){
        if($request->hasfile('file')) {

            $user_id = Auth::user()->id;

            $extensionArr = ['csv'];
            $destinationPath = public_path('uploads');

            $file = $request->file('file');
            $orignal_name = $file->getClientOriginalName();
            $file_type = $file->getClientMimeType();
            $fileTypeArr = explode('/', $file_type);
            $fileName = time() . '_' . $orignal_name;

           
            $extension = $file->getClientOriginalExtension();
            
            if(!in_array($extension, $extensionArr)){
                return response()->json([
                    'sts' => false,
                    'msg' => 'Only CSV file allowed to import orders.'
                ]);
            }

            $filename=$_FILES["file"]["tmp_name"];    
             
            $file = fopen($filename, "r");
            $i = 0;
            while (($data = fgetcsv($file, 10000, ",")) !== FALSE)
            {
                // echo "<pre>";
                // print_r($data);
                // echo "</pre>";

                $order_number = $data[1];
                $order_date = $data[2];
                $status = $data[4];
                $shipping_total = $data[5];
                $order_discount = $data[11];
                $discount_total = $data[12];
                $order_total = $data[13];
                $order_currency = $data[14];
                $payment_method = $data[15];
                $transaction_id = $data[17];
                $shipping_method = $data[20];
                $customer_id = $data[21];
                $customer_user = $data[22];
                $customer_email = $data[23];

                $billing_first_name = $data[24];
                $billing_last_name = $data[25];
                $billing_company = $data[26];
                $billing_email = $data[27];
                $billing_phone = $data[28];
                $billing_address_1 = $data[29];
                $billing_address_2 = $data[30];
                $billing_postcode = $data[31];
                $billing_city = $data[32];
                $billing_state = $data[33];
                $billing_country = $data[34];

                $shipping_first_name = $data[35];
                $shipping_last_name = $data[36];
                $shipping_company = $data[37];
                $shipping_phone = $data[38];
                $shipping_address_1 = $data[39];
                $shipping_address_2 = $data[40];
                $shipping_postcode = $data[41];
                $shipping_city = $data[42];
                $shipping_state = $data[33];
                $shipping_country = $data[44];

                $customer_note = $data[45];
                $coupon_items = $data[50];

                $billing_address = array(
                    'first_name' => $billing_first_name,
                    'last_name' => $billing_last_name,
                    'email' => $billing_email,
                    'company_name' => $billing_company,
                    'street_address_1' => $billing_address_1,
                    'street_address_2' => $billing_address_2,
                    'postcode' => $billing_postcode,
                    'phone' => $billing_phone,
                    'country' => $billing_country,
                    'state' => $billing_state,
                    'city' => $billing_city,
                );
        
              
                $shipping_address =  array(
                    'first_name' => $shipping_first_name,
                    'last_name' => $shipping_last_name,
                    'company_name' => $shipping_company,
                    'street_address_1' => $shipping_address_1,
                    'street_address_2' => $shipping_address_2,
                    'postcode' => $shipping_postcode,
                    'phone' => $shipping_phone,
                    'country' => $shipping_country,
                    'state' => $shipping_state,
                    'city' => $shipping_city,
                );

                $user = User::where('email', $customer_email)->first();

                if($user != null){
                    $order = Orders::create([
                        'user_id' => $customer_id,
                        'order_id' =>  $order_number,
                        'transaction_id' =>  $transaction_id,
                        'customer_email' =>  $customer_email,
                        'amount' => $order_total,
                        'currency' => $order_currency,
                        'payment_mode' => $payment_method,
                        'payment_date' => date('Y-m-d H:i:s', strtotime($order_date)),
                        'discount_amount' => $discount_total,
                        'shipping_cost' => $shipping_total,
                        'shipping_method' => $shipping_method,
                        'status' => $status,
                        'billing_address' => serialize($billing_address),
                        'shipping_address' => serialize($shipping_address),
                        'payer_details' => serialize(array()),
                        'ordernote' => $customer_note,
                    ]);

                    if($order){
                        $shipping_address = Address::updateOrCreate([
                            'user_id' => $user_id,
                            'type' => 'shipping',
                        ],[
                            'user_id' => $user_id,
                            'type' => 'shipping',
                            'first_name' => $billing_first_name,
                            'last_name' => $billing_last_name,
                            'email' => $billing_email,
                            'company_name' => $billing_company,
                            'street_address_1' => $billing_address_1,
                            'street_address_2' => $billing_address_2,
                            'postcode' => $billing_postcode,
                            'phone' => $billing_phone,
                            'country' => $billing_country,
                            'state' => $billing_state,
                            'city' => $billing_city,
                        ]);
            
                        $billing_address = Address::updateOrCreate([
                            'user_id' => $user_id,
                            'type' => 'billing',
                        ],[
                            'user_id' => $user_id,
                            'type' => 'billing',
                            'first_name' => $shipping_first_name,
                            'last_name' => $shipping_last_name,
                            'company_name' => $shipping_company,
                            'street_address_1' => $shipping_address_1,
                            'street_address_2' => $shipping_address_2,
                            'postcode' => $shipping_postcode,
                            'phone' => $shipping_phone,
                            'country' => $shipping_country,
                            'state' => $shipping_state,
                            'city' => $shipping_city,
                        ]);        
                    }
                }
               
                $i++;
            }
         
            return response()->json([
                'sts' => true,
                'msg' => ''
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'File not uploaded.'
            ]);
        }
    }

    public function fecthOrders(){
        
        $orders = Orders::orderBy('id', 'desc')->where('user_id', Auth::user()->id)->get()->toArray();
       

        return response()->json([
            'sts' => true,
            'result' => $orders,
        ]);
    }

}
