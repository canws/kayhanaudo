<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{


    // public function saveCountryStates(){
    //     $countries = json_decode(file_get_contents('https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries.json'));
    //     $statesJSON = json_decode(file_get_contents('https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/states.json'));
    //     //$citiesJSON = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/cities.json';
      

    //    foreach($statesJSON as $key => $state){
    //        State::create([
    //             'sortname' => $state->state_code,
    //             'name' => $state->name,
    //             'country_id' => $state->country_id,
    //             'country_code' => $state->country_code,
    //        ]);
    //    }
    // }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:10',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $validate_error = [];
            if(!empty($errors->messages())){
                foreach($errors->messages() as $key => $error){
                    $validate_error[] = $error[0];
                }
            }
           
            return response()->json([
                'sts' => false,
                'validate_error' => $validate_error
            ]);
        }

        $user = User::where('username', $request->username)->first();
        if($user != null){
            return response()->json([
                'sts' => false,
                'msg' => 'The username has already been taken.'
            ]);
        }

        $isPhone = User::where('phone', $request->phone)->first();
        if($isPhone != null){
            return response()->json([
                'sts' => false,
                'msg' => 'The phone has already been taken.'
            ]);
        }

       
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        if($user){
            return response()->json([
                'sts' => true,
                'msg' => 'Account has been successfully created.'
            ]);
        }
        else{
            return response()->json([
                'sts' => false,
                'msg' => 'Account not created. Please try again'
            ]);
        }
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'sts' => false,
                'msg' => 'Invalid login details'
            ]);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;
        $userData = array(
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->email,
            'role' => $user->role,
        );
        return response()->json([
            'sts' => true,
            'access_token' => $token,
            'userData' => $userData,
            'token_type' => 'Bearer',
            'msg' => 'Successfully loggedin',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
    }

    public function fetchCountryies()
    {
        $result = Country::orderby('id', 'asc')
            ->get()->toArray();
       
        return response()->json(['sts' => true, 'msg' => '', 'result' => $result ]);
    }


    public function fetchCountry(Request $request)
    {
        $result = Country::orderby('id', 'asc')
            ->select('sortname as value', 'name as label')    
            ->get()->toArray();
       
        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $result,
        ]);
    }

    public function fetchStates(Request $request)
    {
        $country = $request->country;
        
        $states = State::orderby('id', 'asc')
            ->where('country_code', $country)
            ->select('id', 'sortname', 'name')    
            ->get()->toArray();
        
        return response()->json([ 'sts' => true, 'msg' => '', 'states' => $states]);
    }

    public function updateAddress(Request $request)
    {
        $user_id = $request->user_id;
        $type = $request->type;
        $inputData = $request->input();

        Address::updateOrCreate([
            'user_id' => $user_id,
            'type' => $type,
        ],[
            'user_id' => $user_id,
            'type' => 'shipping',
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

        return response()->json([ 'sts' => true, 'msg' => 'Successfully updated!']);
    }

    public function updateUserDetails(Request $request)
    {
        $user_id = $request->user_id;
        $email = $request->email;

        $email_check = User::where('email', $email)->where('id', '!=', $user_id)->first();
        if($email_check != null){
            return response()->json([ 'sts' => false, 'msg' => 'Email address already taken.']);
        }

        $phone_check = User::where('phone', $request->phone)->where('id', '!=', $user_id)->first();
        if($phone_check != null){
            return response()->json([ 'sts' => false, 'msg' => 'Phone already taken.']);
        }

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        User::where('id', $user_id)->update($data);

        return response()->json([ 'sts' => true, 'msg' => 'Successfully updated!']);
    }

    public function sendContactEnquiry(Request $request)
    {
        
        $result['name'] = $request->first_name.' '.$request->last_name; 
        $result['email'] = $request->email; 
        $result['phone'] = $request->phone; 
        $result['message'] = $request->message; 
        $result['subject'] = $request->subject; 
        
        $to = 'info@kayhanaudio.com.au';
        $subject = $request->subject;
       $message = view('email-templates.contact-us')->with($result)->render();
        if(env('ENVIRONMENT') == 'live'){
            sendEmail($to,$subject,$message);
        }

        return response()->json([ 'sts' => true, 'msg' => 'Successfully sent!']);
    }


    public function getUserInfo(){

        if(!Auth::check()){
            return response()->json(['sts' => false, 'msg' => 'User not logged In.' ]);
        }

        $user_id = auth()->user()->id;
        $userData = User::where('id', $user_id)
            ->select('id', 'first_name', 'last_name', 'username', 'email', 'phone', 'role')
            ->first();

        if($userData == null){
            return response()->json([
                'sts' => false,
                'msg' => 'User not logged in.',
            ]);
        }
        

        $shipping_address = Address::where('user_id',$userData->id)->where('type', 'shipping')->first();
        $billing_address = Address::where('user_id',$userData->id)->where('type', 'billing')->first();
        
        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $userData,
            'shipping_address' => $shipping_address,
            'billing_address' => $billing_address,
        ]);
       
    }

    public function fetchAddress(Request $request){

        if(!Auth::check()){
            return response()->json(['sts' => false, 'msg' => 'User not logged In.' ]);
        }

        $user_id = auth()->user()->id;
        $userData = User::where('id', $user_id)
            ->select('id', 'first_name', 'last_name', 'username', 'email', 'phone', 'role')
            ->first();

        if($userData == null){
            return response()->json([
                'sts' => false,
                'msg' => 'User not logged in.',
            ]);
        }
        

        $address = Address::where('user_id',$user_id)->where('type', $request->type)->first();
        if($address == null){
            return response()->json(['sts' => false, 'msg' => 'Address not found.']);
        }

        return response()->json(['sts' => true,'msg' => '','address' => $address]);
       
    }

    public function users(){
        $users = User::orderBy('id', 'desc')->get()
            ->toArray();
          
        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $users,
        ]);
    }

    public function userDetails(Request $request){
        $users = User::where('id', $request->id)->get()->first();

        if($users == null){
             $users = array();      
        }
      
        return response()->json([
            'sts' => true,
            'msg' => '',
            'result' => $users,
        ]);
    }

    public function saveUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:10',
            'username' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $validate_error = [];
            if(!empty($errors->messages())){
                foreach($errors->messages() as $key => $error){
                    $validate_error[] = $error[0];
                }
            }
           
            return response()->json([
                'sts' => false,
                'validate_error' => $validate_error
            ]);
        }

        $user = User::where('email', $request->email)->where('id', '!=', $request->id)->first();
        if($user != null){
            return response()->json([
                'sts' => false,
                'msg' => 'The email has already been taken.'
            ]);
        }

        $user = User::where('username', $request->username)->where('id', '!=', $request->id)->first();
        if($user != null){
            return response()->json([
                'sts' => false,
                'msg' => 'The username has already been taken.'
            ]);
        }

        $isPhone = User::where('phone', $request->phone)->where('id', '!=', $request->id)->first();
        if($isPhone != null){
            return response()->json([
                'sts' => false,
                'msg' => 'The phone has already been taken.'
            ]);
        }
        if($request->id > 0){
            $user = User::where(['id' => $request->id])->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'role' => $request->phone,
            ]);


            if($user){
                return response()->json([
                    'sts' => true,
                    'msg' => 'Account has been successfully updated.'
                ]);
            }
            else{
                return response()->json([
                    'sts' => false,
                    'msg' => 'Account not created. Please try again'
                ]);
            }
        }
        else{
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'role' => $request->role,
                'password' => Hash::make($request->password),
            ]);

            if($user){
                return response()->json([
                    'sts' => true,
                    'msg' => 'Account has been successfully created.'
                ]);
            }
            else{
                return response()->json([
                    'sts' => false,
                    'msg' => 'Account not created. Please try again'
                ]);
            }
        }
       
    }

    public function importUsers(Request $request){
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
            while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
            {
                if($i > 0 && $getData[9] != ''){
                
                    $first_name = $getData[9];
                    $last_name = $getData[10];
                    $username = $getData[2];
                    $email = $getData[5];
                    $role = $getData[12];
    
                    $billing_first_name = $getData[27];
                    $billing_last_name = $getData[28];
                    $billing_company = $getData[29];
                    $billing_email = $getData[30];
                    $billing_phone = $getData[31];
                    $billing_address_1 = $getData[32];
                    $billing_address_2 = $getData[33];
                    $billing_postcode = $getData[34];
                    $billing_city = $getData[35];
                    $billing_state = $getData[36];
                    $billing_country = $getData[37];
    
                    $shipping_first_name = $getData[38];
                    $shipping_last_name = $getData[39];
                    $shipping_company = $getData[40];
                    $shipping_phone = $getData[41];
                    $shipping_address_1 = $getData[42];
                    $shipping_address_2 = $getData[43];
                    $shipping_postcode = $getData[44];
                    $shipping_city = $getData[45];
                    $shipping_state = $getData[46];
                    $shipping_country = $getData[47];
                    

                    $error = 0;
                    $user = User::where('email', $email)->first();
                  
                   
                    $user = User::where('username', $username)->first();
                  
    
                    if($user == null){
                        $user = User::create([
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'username' => $username,
                            'phone' => $billing_phone,
                            'role' => $role,
                             'password' => Hash::make('General123..'),
                        ]);

                        Address::updateOrCreate([
                            'user_id' => $user->id,
                            'type' => 'billing',
                        ],[
                            'user_id' => $user->id,
                            'type' => 'billing',
                            'first_name' => ($billing_first_name != '' ? $billing_first_name : ''),
                            'last_name' => ($billing_last_name != '' ? $billing_last_name : ''),
                            'company' => ($billing_company != '' ? $billing_company : ''),
                            'email' => ($billing_email != '' ? $billing_email : ''),
                            'phone' => ($billing_phone != '' ? $billing_phone : ''),
                            'address_1' => ($billing_address_1 != '' ? $billing_address_1 : ''),
                            'address_2' => ($billing_address_2 != '' ? $billing_address_2 : ''),
                            'postcode' => ($billing_postcode != '' ? $billing_postcode : ''),
                            'city' => ($billing_city != '' ? $billing_city : ''),
                            'state' => ($billing_state != '' ? $billing_state : ''),
                            'country' => ($billing_country != '' ? $billing_country : ''),
                        ]);
            
                        Address::updateOrCreate([
                            'user_id' => $user->id,
                            'type' => 'shipping',
                        ],[
                            'user_id' => $user->id,
                            'type' => 'shipping',
                            'first_name' => ($shipping_first_name != '' ? $shipping_first_name : ''),
                            'last_name' => ($shipping_last_name != '' ? $shipping_last_name : ''),
                            'company' => ($shipping_company != '' ? $shipping_company : ''),
                            'email' => '',
                            'phone' => ($shipping_phone != '' ? $shipping_phone : ''),
                            'address_1' => ($shipping_address_1 != '' ? $shipping_address_1 : ''),
                            'address_2' => ($shipping_address_2 != '' ? $shipping_address_2 : ''),
                            'postcode' => ($shipping_postcode != '' ? $shipping_postcode : ''),
                            'city' => ($shipping_city != '' ? $shipping_city : ''),
                            'state' => ($shipping_state != '' ? $shipping_state : ''),
                            'country' => ($shipping_country != '' ? $shipping_country : ''),
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
    
    
}
