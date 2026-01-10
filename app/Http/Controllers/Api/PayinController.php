<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PayinController extends Controller
{
	public function payzaaar(Request $request){
    $validator = Validator::make($request->all(), [
    'user_id' => 'required|exists:users,id',
    'cash'    => 'required',
    'type'    => 'required|in:0',
], [
    'type.in' => 'The selected payment type is invalid. Only type 0 is allowed.',
]);


    if ($validator->fails()) {
        return response()->json([
            'status'  => 400,
            'message' => $validator->errors()->first()
        ]);
    }

    $cash     = $request->cash;
    $userid   = $request->user_id;
    $orderid  = 'ORD'.rand(111111,999999);
	$username = DB::table('users')->where('id', $userid)->value('username');
    
    
    $merchantId = "USER000107";
    $apiToken   = "919f2825814d8fe0ea5ccf4a9e74b180";
    $username   = "$username";
    $email      = "johndoe@gmail.com"; 
    $phone      = "9876543210";
    $orderId    = "$orderid";
    $amount     = "$cash"; 
    $remark     = "Payment for order #$orderId";
    
    $payload = [
    'data' => [
        'merchantid' => $merchantId,
        'apitoken'   => $apiToken,
        'username'   => $username,
        'email'      => $email,
        'phone'      => $phone,
        'orderid'    => $orderId,
        'remark'     => $remark,
        'amount'     => $amount
    ],
    'apiToken' => $apiToken
];
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://payzaaar.com/dashboard/api/encodeData',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>json_encode($payload),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Cookie: ci_session=8l74oama3etp16m9o9mv819p7m1ebufk'
      ),
    ));
   
    $response = curl_exec($curl);
    curl_close($curl);
    $encoded = json_decode($response, true);
    $encryptedData = $encoded['data'];
    
    $payloadforpayin=[
        "data"=>"$encryptedData",
        "apitoken"=>"919f2825814d8fe0ea5ccf4a9e74b180"
        ];
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://payzaaar.com/dashboard/api/paynow',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>json_encode($payloadforpayin),
      CURLOPT_HTTPHEADER => array(
        'X-Merchant-ID: USER000107',
        'X-Api-Key: 919f2825814d8fe0ea5ccf4a9e74b180',
        'Content-Type: application/json',
        'Cookie: ci_session=8l74oama3etp16m9o9mv819p7m1ebufk'
      ),
    ));
    
    $responses = curl_exec($curl);

if ($responses === false) {
    return response()->json([
        'status' => 500,
        'message' => 'CURL error in paynow request: ' . curl_error($curl),
    ]);
}

curl_close($curl);

$encodedd = json_decode($responses, true);

// Check if decoding failed or data is missing
if (!isset($encodedd['data'])) {
    return response()->json([
        'status' => 500,
        'message' => 'Invalid paynow API response',
        'raw_response' => $responses,  // to help debug the response format
    ]);
}

$encryptedData = $encodedd['data'];

    $payloadForDecode=[
        "encodedData"=>"$encryptedData",
        "apiToken"=>"919f2825814d8fe0ea5ccf4a9e74b180"
        
        ]; 
     
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://payzaaar.com/dashboard/api/decodeData',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>json_encode($payloadForDecode),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Cookie: ci_session=8l74oama3etp16m9o9mv819p7m1ebufk'
      ),
    ));
    
    $responsess = curl_exec($curl);
    
    curl_close($curl);
        //echo $responsess;
       // die;
	   $responsess = json_decode($responsess, true);
     if(!isset($responsess['data']['status'])) {
        return response()->json([
            'status'       => 500,
            'message'      => 'Invalid decode response format',
            'raw_response' => $responsess
        ]);
    }
   
    // Step 6: Check transaction status
    if ($responsess['data']['status'] != 'success') {
        return response()->json([
            'status'         => 400,
            'message'        => $responsess['data']['msg'] ?? 'Transaction failed',
            'orderid'        => $responsess['data']['orderid'] ?? $orderid,
            'payment_status' => $responsess['data']['status']
        ]);
    }

    // Step 7: Save transaction
    DB::table('payins')->insert([
        'user_id'      => $userid,
        'cash'         => $cash,
        'type'         => 0,
        'order_id'     => $orderid,
        'redirect_url' => "https://root.pasawin.com/uploads/payment_success.php",
        'status'       => 1,
        'typeimage'    => "https://root.pasawin.com/public/uploads/payzaar.jpg",
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    // Step 8: Clean payment link (remove spaces)
    // if (isset($decodedResponse['data']['paymentlink'])) {
    //     $decodedResponse['data']['paymentlink'] = preg_replace('/\s+/', '', $decodedResponse['data']['paymentlink']);
    // }
    
    $responsess['data']['paymentlink'] = preg_replace('/\s+/', '', $responsess['data']['paymentlink']);
		$intent_link=$responsess['data']['paymentlink'];
		$qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?amp;size=200x200&data=' . urlencode($intent_link);
		$responsess['data']['Qr_Link'] =$qr_code_url;

    // Step 9: Return response
    return response()->json([
        'status'  => 200,
        'message' => 'Transaction successful',
        'data'    => $responsess['data']
    ]);
}

    public function payzaaarCallback(Request $request){
    $data = $request->all(); // Get all POST data

    // Log callback for reference/debugging
    DB::table('payzaar_callback')->insert([
        'data'     => json_encode($data),
        'datetime' => now()
    ]);

    // Validate required fields
    $orderId = $data['orderid'] ?? null;
    $amount  = $data['amount'] ?? null;
    $status  = $data['status'] ?? null;
    $utr     = $data['utr'] ?? null;

    if (!$orderId || !$amount || !$status) {
        return response()->json(['status' => 400, 'message' => 'Missing required callback data']);
    }
    if (strtolower($status) === 'success') {

        $payin = DB::table('payins')->where('order_id', $orderId)->first();

        if ($payin && $payin->status != 2) {
            // Mark payin as successful
            DB::table('payins')->where('order_id', $orderId)->update(['status' => 2, 'updated_at' => now()]);

            $userId = $payin->user_id;
            $cash   = $payin->cash;

            $user = DB::table('users')->where('id', $userId)->first();

            if ($user) {
                // Update user wallet balances
                DB::table('users')->where('id', $userId)->update([
                    'wallet'          => DB::raw("wallet + $cash"),
                    'recharge'        => DB::raw("recharge + $cash"),
                    'total_payin'     => DB::raw("total_payin + $cash"),
                    'no_of_payin'     => DB::raw("no_of_payin + 1"),
                    'deposit_balance' => DB::raw("deposit_balance + $cash")
                ]);

                // First recharge check
                if ($user->first_recharge == 0) {
                    DB::table('users')->where('id', $userId)->update([
                        'first_recharge'        => $cash,
                        'first_recharge_amount' => $cash
                    ]);

                    // Referral update
                    if ($user->referral_user_id) {
                        DB::table('users')->where('id', $user->referral_user_id)->update([
                            'yesterday_payin'         => DB::raw("yesterday_payin + $cash"),
                            'yesterday_no_of_payin'   => DB::raw("yesterday_no_of_payin + 1"),
                            'yesterday_first_deposit' => DB::raw("yesterday_first_deposit + $cash")
                        ]);
                    }
                }
            }
        }
    }

    return response()->json(['status' => 200, 'message' => 'Callback processed']);
}

    public function checkPayzaaarPayment(Request $request){
    $orderid = $request->input('order_id');

    if (empty($orderid)) {
        return response()->json(['status' => 400, 'message' => 'Order ID is required']);
    }

    $match_order = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->first();

    if (!$match_order) {
        return response()->json(['status' => 400, 'message' => 'Order ID not found or already processed']);
    }

    $uid      = $match_order->user_id;
    $cash     = $match_order->cash;
    $type     = $match_order->type;
    $datetime = now();

    $update_payin = DB::table('payins')
        ->where('order_id', $orderid)
        ->where('status', 1)
        ->where('user_id', $uid)
        ->update(['status' => 2]);

    if (!$update_payin) {
        return response()->json(['status' => 400, 'message' => 'Failed to update payment status']);
    }

    // Check if it's user's first recharge
    $referData = DB::table('users')->select('referral_user_id', 'first_recharge')->where('id', $uid)->first();
    $referuserid     = $referData->referral_user_id;
    $first_recharge  = $referData->first_recharge;

    if ($first_recharge == 0) {
        $extra = DB::table('extra_first_deposit_bonus')
            ->where('first_deposit_ammount', '<=', $cash)
            ->where('max_amount', '>=', $cash)
            ->first();

        if ($extra) {
            $bonus  = $extra->bonus;
            $amount = $cash + $bonus;

            DB::table('extra_first_deposit_bonus_claim')->insert([
                'userid'         => $uid,
                'extra_fdb_id'   => $extra->id,
                'amount'         => $cash,
                'bonus'          => $bonus,
                'status'         => 0,
                'created_at'     => $datetime,
                'updated_at'     => $datetime,
            ]);

            DB::update("UPDATE users 
                SET 
                    wallet = wallet + $amount,
                    first_recharge = 1,
                    first_recharge_amount = first_recharge_amount + $amount,
                    recharge = recharge + $amount,
                    total_payin = total_payin + $amount,
                    no_of_payin = no_of_payin + 1,
                    deposit_balance = deposit_balance + $amount
                WHERE id = ?", [$uid]);

        } else {
            // No extra bonus matched
            DB::update("UPDATE users 
                SET 
                    wallet = wallet + $cash,
                    first_recharge = 1,
                    first_recharge_amount = first_recharge_amount + $cash,
                    recharge = recharge + $cash,
                    total_payin = total_payin + $cash,
                    no_of_payin = no_of_payin + 1,
                    deposit_balance = deposit_balance + $cash
                WHERE id = ?", [$uid]);
        }

        if (!empty($referuserid)) {
            DB::update("UPDATE users 
                SET 
                    yesterday_payin = yesterday_payin + $cash,
                    yesterday_no_of_payin = yesterday_no_of_payin + 1,
                    yesterday_first_deposit = yesterday_first_deposit + $cash,
                    created_at = ?
                WHERE id = ?", [$datetime, $referuserid]);
        }

    } else {
        // Not first recharge
        DB::update("UPDATE users 
            SET 
                wallet = wallet + $cash,
                recharge = recharge + $cash,
                total_payin = total_payin + $cash,
                no_of_payin = no_of_payin + 1,
                deposit_balance = deposit_balance + $cash
            WHERE id = ?", [$uid]);

        if (!empty($referuserid)) {
            DB::update("UPDATE users 
                SET 
                    yesterday_payin = yesterday_payin + $cash,
                    yesterday_no_of_payin = yesterday_no_of_payin + 1
                WHERE id = ?", [$referuserid]);
        }
    }

    // ✅ Redirect to success page
    return redirect()->away('https://root.jupitergames.world/uploads/payment_success.php');
}
	
    public function withdraw_request(Request $request){
    	    $date = date('Ymd');
            $rand = rand(1111111, 9999999);
            $transaction_id = $date . $rand;
    	
    		 $userid=$request->userid;
    		 $amount=$request->amount;
    		   $validator=validator ::make($request->all(),
            [
                'userid'=>'required',
    			'amount'=>'required',
    			
            ]);
            $date=date('Y-m-d h:i:s');
            if($validator ->fails()){
                $response=[
                    'success'=>"400",
                    'message'=>$validator ->errors()
                ];                                                   
                
                return response()->json($response,400);
            }
          
    		 $datetime = date('Y-m-d H:i:s');
    		 
             $user = DB::select("SELECT * FROM `users` where `id` =$userid");
    		 $account_id=$user[0]->accountno_id;
    		 $mobile=$user[0]->mobile;
    		 $wallet=$user[0]->wallet;
    // 		 dd($wallet);
    		 $accountlist=DB::select("SELECT * FROM `bank_details` WHERE `id`=$account_id");
    		 
    		 $insert= DB::table('transaction_history')->insert([
            'userid' => $userid,
            'amount' => $amount,
            'mobile' => $mobile,
    		  'account_id'=>$account_id,
            'status' => 0,
    			 'type'=>1,
            'date' => $datetime,
    		  'transaction_id' => $transaction_id,
        ]);
    		  DB::select("UPDATE `users` SET `wallet`=`wallet`-$amount,`winning_wallet`=`winning_wallet`-$amount  WHERE `id`=$userid");
              if($insert){
              $response =[ 'success'=>"200",'data'=>$insert,'message'=>'Successfully'];return response ()->json ($response,200);
          }
          else{
           $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
          } 
        }
	
    public function redirect_success(){
        return view('success');
    }
	
	
// 	 public function qr_view() 
//     {

//       $show_qr = DB::select("SELECT* FROM `usdt_qr`");
//       //$show_qr = DB::select("SELECT `name`, `qr_code` FROM `usdt_qr`");

//         if ($show_qr) {
//             $response = [
//                 'message' => 'Successfully',
//                 'status' => 200,
//                 'data' => $show_qr
//             ];

//             return response()->json($response,200);
//         } else {
//             return response()->json(['message' => 'No record found','status' => 400,
//                 'data' => []], 400);
//         }
//     }
    
//   public function usdt_payin(Request $request){
//     $validator = Validator::make($request->all(), [
//         'user_id' => 'required|exists:users,id',
//         'cash' => 'required|numeric',
//         'type' => 'required|integer',
//     ]);

//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ]);
//     }

//     $usdt = $request->cash;
//     $image = $request->screenshot;
//     $type = $request->type;
//     $userid = $request->user_id;
//     $inr = $usdt;
//     $datetime = now();
//     $orderid = date('YmdHis') . rand(11111, 99999);

//     // Validate image input
//     if (empty($image) || $image === '0' || $image === 'null' || $image === null || $image === '' || $image === 0) {
//         return response()->json([
//             'status' => 400,
//             'message' => 'Please Select Image'
//         ]);
//     }

//     // Handle image saving
//     $path = '';
//     if (!empty($image)) {
//         $imageData = base64_decode($image);
//         if ($imageData === false) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Invalid base64 encoded image'
//             ]);
//         }

//         // Save image to /public/usdt_images directory
//         $newName = Str::random(6) . '.png';
//         $relativePath = 'usdt_images/' . $newName;

//         // Ensure directory exists
//         if (!file_exists(public_path('usdt_images'))) {
//             mkdir(public_path('usdt_images'), 0775, true);
//         }

//         // Save the image file
//         if (!file_put_contents(public_path($relativePath), $imageData)) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Failed to save image'
//             ]);
//         }

//         // Generate URL to store in DB
//         $path = asset('usdt_images/' . $newName);
//     }

//     // Handle type == 0 (payin logic)
//     if ($type == 1) {
//         $insert_usdt = DB::table('payins')->insert([
//             'user_id' => $userid,
//             'cash' => $usdt * 90,
//             'usdt_amount' => $inr,
//             'type' => '1',
//             'typeimage' => $path,
//             'order_id' => $orderid,
//             'status' => 1,
//             'created_at' => $datetime,
//             'updated_at' => $datetime
//         ]);

//         if ($insert_usdt) {
//             return response()->json([
//                 'status' => 200,
//                 'message' => 'USDT Payment Request sent successfully. Please wait for admin approval.'
//             ]);
//         } else {
//             return response()->json([
//                 'status' => 500,
//                 'message' => 'Failed to insert USDT Payment'
//             ]);
//         }
//     } else {
//         return response()->json([
//             'status' => 400,
//             'message' => 'Invalid Type'
//         ]);
//     }
// }

//     public function payin_usdt(Request $request){
//     // Validation
//         $validator = Validator::make($request->all(), [
//             'user_id' => 'required|exists:users,id',
//             'amount' => 'required|numeric|gt:0',
//             'type' => 'required|in:1',
//         ]);
     
//         $validator->stopOnFirstFailure();
        
//         if ($validator->fails()) {
//             return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
//         }
    
//         // Get input data
//         $user_id = $request->user_id;
//         $amount = $request->amount;
//         $type = $request->type;
//         $inr_amt = $amount * 94;
        
//         // Get client IP address
//       // $clientIp = $request->ip();
    
//         // Dump and die to see IP address
//         //dd('Client IP Address:', $clientIp); // Here, you can see the IP
    
//         $email = 'Globalbettech@gmail.com'; 
//         $token = '58839776549046321236110964258208'; // Replace with a secure token or config value
//         $apiUrl = "https://cryptofit.biz/Payment/coinpayments_api_call";
//         $coin = 'USDT.BEP20';
    
//         // Generate unique order ID
//         do {
//             $orderId = str_pad(mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
//         } while (DB::table('payins')->where('order_id', $orderId)->exists());
    
//         // User validation
//         $user_exist = DB::table('users')->where('id', $user_id)->first();
    
//         // Prepare API data
//         $formData = [
//             'txtamount' => $amount,
//             'coin' => $coin,
//             'UserID' => $email,
//             'Token' => $token,
//             'TransactionID' => $orderId,
//         ];
    
//         try {
//             // Make API request
//             $response = Http::asForm()->post($apiUrl, $formData);
//             Log::info('PayIn API Response:', ['response' => $response->body()]);
//             Log::info('PayIn API Status Code:', ['status' => $response->status()]);
//             // Decode the response
//             $apiResponse = json_decode($response->body());
//             // You can dump API response here
    
//             // Check if the API response is successful
//             if ($response->successful() && isset($apiResponse->error) && $apiResponse->error === 'ok') {
//                 // Insert data into payins table
//                 $inserted_id = DB::table('payins')->insertGetId([
//                     'user_id' => $user_id,
//                     'status' => 1,
//                     'order_id' => $orderId,
//                     'cash' => $inr_amt,
//                     'usdt_amount' => $amount,
//                     'type' => $type,
//                 ]);
    
//                 return response()->json([
//                     'status' => 200,
//                     'message' => 'Payment initiated successfully.',
//                     'data' => $apiResponse,
//                 ], 200);
//             }
    
//             return response()->json([
//                 'status' => 400,
//                 'message' => 'Failed to initiate payment.'
//             ], 400);
//         } catch (\Exception $e) {
//             Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
//             return response()->json(['status' => 400, 'message' => 'Internal Server Error'], 400);
//         }
//     }
    
//     public function payin_call_back(Request $request){
		
// 		$validator = Validator::make($request->all(), [
// 					'invoice' => 'required',
// 					'status_text' => 'required',
// 					'amount' => 'required'
// 				]);

// 				$validator->stopOnFirstFailure();

// 				if ($validator->fails()) {
// 					return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
// 				}
		
// 		$invoice = $request->invoice;
// 		$status_text = $request->status_text;
// 		$amount = $request->amount;
// 		if($status_text=='complete'){
			
//           $a =  DB::table('payins')->where('order_id',$invoice)->update(['status'=>2]);
			
// 			if($a){
// 				$user_detail = Payin::where('order_id', $invoice)
//                             ->where('status', 2)
//                             ->first();
// 				$user_id=$user_detail->user_id;
// 				$amount1=$user_detail->cash;
// 				//$update_wallet = jilli::update_user_wallet($user_id);
// 				$update=User::where('id', $user_id)->update(['wallet' => $amount1]);
// 				$add_jili = jilli::add_in_jilli_wallet($user_id,$amount1);
// 				return response()->json(['status'=>200,'message'=>'Payment successful.'],200);
// 			}else{
// 			   return response()->json(['status'=>400,'message'=>'Failed to update!'],400);
// 			}
// 		}else{
//           return response()->json(['status'=>400,'message'=>'Something went wrong!'],400);
// 		}
// 	}

public function payin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'cash' => 'required|numeric|min:100',
        'type' => 'required'
    ]);

    $validator->stopOnFirstFailure();
    if ($validator->fails()) {
        return response()->json([
            'status' => "400",
            'message' => $validator->errors()->first()
        ], 200);
    }

    $cash = $request->cash;
    $userid = $request->user_id;
    $type = $request->type;

    $dateTime = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
    $formattedDateTime = $dateTime->format('YmdHis');
    $rand = rand(11, 99);
    $orderid = "TXT".$formattedDateTime . $rand;

    // Get user manually via query
    $user = DB::table('users')->where('id', $userid)->first();
	
	$email = $user->email ?? Str::random(10) . '@example.com';
	$username = $user->username ?? 'user';
	
    $account_type = $user->account_type;
    if ($account_type == 1) {
		
        DB::table('users')->where('id', $userid)->increment('wallet', $cash);
		DB::table('payins')->insert([
                    'user_id' => $userid,
                    'cash' => $cash,
                    'type' => $type,
                    'order_id' => $orderid,
                    'status' => 2,
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ]);
        return view('success');
    }

    //dd($user);
    if ($user) { 
        if ($cash >= 1) {
            if ($type == '2') {
                $bonus = 0;
                $totalAmount = $cash;
                $baseUrl = URL::to('/');
                $redirect_url = $baseUrl . "/api/checkPayment?order_id=$orderid";

                // Insert into payin table manually
                $inserted = DB::table('payins')->insert([
                    'user_id' => $user->id,
                    'cash' => $cash,
                    'type' => $type,
                    'order_id' => $orderid,
                    'redirect_url' => $redirect_url,
                    'status' => 1,
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime,
                ]);

                if (!$inserted) {
                    return response()->json([
                        'status' => "400",
                        'message' => 'Failed to store record in payin history!'
                    ], 200);
                }

                 
                $postParameter = [
                    "merchantid" => "INDIANPAY00INDIANPAY00161",
                    "orderid" => $orderid,
                    "amount" => $totalAmount,
                    "name" => $username,
                    "email" => $email,
                    "mobile" => $user->mobile,
                    "remark" => "payIn",
                    "type" => "2",
                    "redirect_url" => $redirect_url
                ];
//dd($postParameter);
				
			//	echo json_encode($postParameter);die;
                // Call external payment API
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => 'https://indianpay.co.in/admin/paynow',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($postParameter),
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Cookie: ci_session=uvkdvmvc3n03msqrd4bfiudbgk658uif'
                    ],
                ]);
                $response = curl_exec($curl);
                curl_close($curl);
                echo $response;
				//dd($response);
            } else {
                return response()->json([
                    'status' => "400",
                    'message' => 'Invalid type value!'
                ], 200);
            }
        } else {
            return response()->json([
                'status' => "400",
                'message' => 'Minimum Deposit amount is 100 rs!'
            ], 200);
        }
    } else {
        return response()->json([
            'status' => "400",
            'message' => 'Internal error! User not found.'
        ], 400);
    }
}



	public function genericCheckPayment(Request $request){
		$orderid = $request->input('order_id');

		if (empty($orderid)) {
			return response()->json(['status' => 400, 'message' => 'Order ID is required']);
		}

		$match_order = DB::table('payins')
			->where('order_id', $orderid)
			->where('status', 1)
			->first();

		if (!$match_order) {
			return response()->json(['status' => 400, 'message' => 'Order ID not found or already processed']);
		}

		$uid  = $match_order->user_id;
		$cash = $match_order->cash;

		// ✅ Mark payin as success
		$updated = DB::table('payins')
			->where('order_id', $orderid)
			->update(['status' => 2]);

		if (!$updated) {
			return response()->json(['status' => 400, 'message' => 'Failed to update payment status']);
		}

		// ✅ Get user info
		$user = DB::table('users')->where('id', $uid)->first();
		$first_recharge = $user->first_recharge;
		$referral_user_id = $user->referral_user_id;

		// ✅ Update user wallet always
		DB::table('users')->where('id', $uid)->update([
			'wallet' => DB::raw("wallet + $cash"),
			'recharge' => DB::raw("recharge + $cash"),
			'total_payin' => DB::raw("total_payin + $cash"),
			'no_of_payin' => DB::raw("no_of_payin + 1"),
			'deposit_balance' => DB::raw("deposit_balance + $cash")
		]);
		
         DB::table('wallet_history')->insert([
						'userid' => $uid,
						'amount' => $cash,
						'subtypeid' => "3",
						'created_at' => now(),
                        'updated_at' => now()
					]);
		
		// ✅ First Recharge Logic
		if ($first_recharge == 0) {

			if ($cash >= 1000) {
				$self_bonus = $cash * 0.15;

				// ✅ Bonus to self
				DB::table('users')
					->where('id', $uid)
					->update([
						'wallet' => DB::raw("wallet + $self_bonus")
					]);
				
				 DB::table('wallet_history')->insert([
						'userid' => $uid,
						'amount' => $self_bonus,
						'subtypeid' => "9",
						'created_at' => now(),
                        'updated_at' => now()
					]);

				// ✅ Bonus to referral
				if (!empty($referral_user_id) && $referral_user_id != 0) {
					DB::table('users')
						->where('id', $referral_user_id)
						->update([
							'wallet' => DB::raw("wallet + $self_bonus")
						]);
					DB::table('wallet_history')->insert([
						'userid' => $referral_user_id,
						'amount' => $self_bonus,
						'subtypeid' => "33",
						'created_at' => now(),
                        'updated_at' => now()
					]);
				}
			}

			// ✅ Update first recharge
			DB::table('users')->where('id', $uid)->update([
				'first_recharge' => 1
			]);

		} else {
			// ✅ Check second recharge
			$success_count = DB::table('payins')
				->where('user_id', $uid)
				->where('status', 2)
				->count();
          
			if ($success_count == 2 && $cash >= 1000) {
				$self_bonus = $cash * 0.20;

				// ✅ 20% Bonus to self only
				DB::table('users')
					->where('id', $uid)
					->update([
						'wallet' => DB::raw("wallet + $self_bonus")
					]);
				DB::table('wallet_history')->insert([
						'userid' => $uid,
						'amount' => $self_bonus,
						'subtypeid' => "34",
						'created_at' => now(),
                        'updated_at' => now()
					]);
			}
		}

		return view('success');
	}
public function bappa_venture(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id'   => 'required|exists:users,id',
        'cash'      => 'required|numeric|min:1',
        'type'      => 'required|in:1',
        'coupon_id' => 'nullable|exists:coupons,id',
    ])->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status'  => 400,
            'message' => $validator->errors()->first()
        ]);
    }

    $cash      = (float) $request->cash;
    $type      = (int) $request->type;
    $userid    = (int) $request->user_id;
    $coupon_id = $request->coupon_id;
    $bonus     = 0;

    $orderid  = date('YmdHis') . rand(11111, 99999);
    $datetime = now();

    $user = DB::table('users')->where('id', $userid)->first();
    if (!$user) {
        return response()->json(['status' => 400, 'message' => 'User not found']);
    }

    $merchantid = DB::table('admin_settings')->where('id', 12)->value('longtext');

    /* ================= COUPON ================= */
    if ($coupon_id) {
        $coupon = DB::table('coupons')->where('id', $coupon_id)->where('status', 1)->first();
        if (!$coupon) {
            return response()->json(['status' => 400, 'message' => 'Invalid or expired coupon']);
        }

        $used = DB::table('coupon_history')
            ->where('user_id', $userid)
            ->where('coupon_id', $coupon_id)
            ->exists();

        if ($used) {
            return response()->json(['status' => 400, 'message' => 'Coupon already used']);
        }

        $bonus = ($cash * $coupon->percentage) / 100;

        DB::table('coupon_history')->insert([
            'user_id'   => $userid,
            'coupon_id' => $coupon_id,
            'used_at'   => now()
        ]);
    }

    /* ================= PAYIN ================= */
    $redirect_url =  "https://root.pasawin.com/api/checkPayment?order_id=$orderid";

    DB::table('payins')->insert([
        'user_id'      => $userid,
        'cash'         => $cash,
        'bonus'        => $bonus,
        'type'         => $type,
        'order_id'     => $orderid,
        'redirect_url' => $redirect_url,
        'status'       => 1,
        'typeimage'    => "https://root.pasawin.com/uploads/fastpay_image.png",
        'created_at'   => $datetime,
        'updated_at'   => $datetime
    ]);

    /* ================= GATEWAY ================= */
    $payload = [
        'merchantid'   => $merchantid,
        'orderid'      => $orderid,
        'amount'       => $cash,
        'name'         => $user->u_id,
        'email'        => $user->email,
        'mobile'       => $user->mobile,
        'remark'       => 'payIn',
        'type'         => 1,
        'redirect_url' => $redirect_url
    ];
	
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => 'https://bappaventures.com/api/paynow',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);

    echo curl_exec($curl);
    curl_close($curl);
}

public function checkPayment(Request $request)
{
    $request->validate([
        'order_id' => 'required|string'
    ]);

    $orderid = $request->order_id;

    // 1️⃣ Fetch order first
    $payin = DB::table('payins')->where('order_id', $orderid)->first();

    if (!$payin) {
        return redirect()->away('https://root.pasawin.com/uploads/failed.php');
    }

    // 2️⃣ If already processed → just redirect
    if ($payin->status == 2) {
        return redirect()->away('https://root.pasawin.com/uploads/payment_success.php');
    }

    if ($payin->status == 3) {
        return redirect()->away('https://root.pasawin.com/uploads/failed.php');
    }

    // 3️⃣ Only PENDING (status = 1) reaches here

    $response = Http::get(
        'https://bappaventures.com/api/payinstatus',
        ['order_id' => $orderid]
    );

    $result = $response->json();

    // ❌ Payment Failed
    if (($result['status'] ?? '') !== 'success') {
        DB::table('payins')
            ->where('order_id', $orderid)
            ->where('status', 1)
            ->update(['status' => 3]);

        return redirect()->away('https://root.pasawin.com/uploads/failed.php');
    }

    // ✅ SUCCESS — PROCESS ONLY ONCE
    DB::transaction(function () use ($payin) {

        // Lock row to stop race condition
        $payin = DB::table('payins')
            ->where('id', $payin->id)
            ->where('status', 1)
            ->lockForUpdate()
            ->first();

        if (!$payin) {
            return; // already processed by another request
        }

        DB::table('payins')->where('id', $payin->id)->update([
            'status' => 2
        ]);

        $user = DB::table('users')
            ->where('id', $payin->user_id)
            ->lockForUpdate()
            ->first();

        $cash = (float) $payin->cash;
        $bonus = ($user->first_recharge == 0) ? ($cash * 0.10) : 0;

        if ($user->first_recharge == 0) {
            DB::table('extra_first_deposit_bonus_claim')->insert([
                'userid' => $user->id,
                'extra_fdb_id' => 1,
                'bonus' => $bonus,
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        DB::table('users')->where('id', $user->id)->update([
            'wallet' => DB::raw("wallet + " . ($cash + $bonus)),
            'recharge' => DB::raw("recharge + $cash"),
            'total_payin' => DB::raw("total_payin + $cash"),
            'no_of_payin' => DB::raw("no_of_payin + 1"),
            'deposit_balance' => DB::raw("deposit_balance + $cash"),
            'first_recharge' => 1,
            'first_recharge_amount' =>
                DB::raw("IF(first_recharge_amount = 0, $cash, first_recharge_amount)")
        ]);
    });

    return redirect()->away('https://root.pasawin.com/uploads/payment_success.php');
}



}
