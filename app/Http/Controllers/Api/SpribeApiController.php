<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\Slider;
use App\Models\BankDetail; // Import your model
use Carbon\Carbon;
use App\Models\Payin;
use App\Models\WalletHistory;
use App\Models\withdraw;
use App\Models\GiftCard;
use App\Models\GiftClaim;
use App\Models\CustomerService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class SpribeApiController extends Controller
{
	
	
	public function spribeinit()
	{
			if (
				empty($_GET['userId']) ||
				empty($_GET['amount']) ||
				empty($_GET['gmId'])
			) {
				http_response_code(400);
				echo json_encode([
					"status" => 400,
					"message" => "userId, amount and gmId are required"
				]);
				exit;
			}

			$userId = $_GET['userId'];
			$amount = $_GET['amount'];
			$gmId   = $_GET['gmId'];

			$user = DB::select(
				"SELECT mobile,u_id FROM users WHERE id = ? LIMIT 1",
				[$userId]
			);

			if (empty($user)) {
				return response()->json([
					'status' => 404,
					'message' => 'User not found'
				]);
			}

			$mobile = $user[0]->u_id;
			$TOKEN  = "1684f01d6aabbd958285a700a35ed978";  // Your unique API token
			$SECRET = "0e5e2e9ca52d68fb69eb8bad86ad138f"; // 32-character secret key

			// Step 2: Game details & player info
			$SERVER_URL   = "https://igamingapis.live/api/v1"; // API endpoint
			$RETURN_URL   = "https://pasawin.com/";       // After game ends
			$CALLBACK_URL = "https://pasawin.com/api.php"; // Game result callback
			$USER_ID      = $mobile;                                // Player ID
			$BALANCE      = $amount;                                // Player wallet balance
			$GAME_UID     = $gmId;                             // Unique game session ID

			// Step 3: Encrypt data using AES-256-ECB
			function ENCRYPT_PAYLOAD_ECB(array $DATA, string $KEY): string {
				if (strlen($KEY) !== 32) throw new Exception("Key must be 32 bytes long");
				$JSON = json_encode($DATA, JSON_UNESCAPED_UNICODE);
				$ENC  = openssl_encrypt($JSON, "AES-256-ECB", $KEY, OPENSSL_RAW_DATA);
				return base64_encode($ENC);
			}

			// Step 4: Prepare the data payload
			$PAYLOAD = [
				"user_id"   => $USER_ID,
				"balance"   => $BALANCE,
				"game_uid"  => $GAME_UID,
				"token"     => $TOKEN,
				"timestamp" => round(microtime(true) * 1000), // current time in ms
				"return"    => $RETURN_URL,
				"callback"  => $CALLBACK_URL
				// Optional parameters (can be omitted):
				// "currency_code" => "BDT",
				// "language" => "bn"
			];

			// Step 5: Encrypt it
			$ENCRYPTED = ENCRYPT_PAYLOAD_ECB($PAYLOAD, $SECRET);

			// Step 6: Send it to the SoftAPI server
		 	   echo $URL = $SERVER_URL . "?payload=" . urlencode($ENCRYPTED) . "&token=" . urlencode($TOKEN);

			$CH = curl_init($URL);
			curl_setopt($CH, CURLOPT_RETURNTRANSFER, true);
		 	  $RESPONSE = curl_exec($CH);
		//echo $RESPONSE; die;
			curl_close($CH);
		
			// Step 7: Decode the response
			$DATA = json_decode($RESPONSE,true);

			// Step 8: Show the result
			if (isset($DATA["code"])&& $DATA["code"]== 0) {
				
				$gammeUrl=$DATA["data"]["url"];
				$sss=[
					'status'=>200,
					'msg'=>'Game launched successfully',
					'gameUrl'=>$gammeUrl,
					'userId'=>$USER_ID
				];
				
				echo json_encode($sss);
				die;
			} else{
				
				$sss=[
					'status'=>400,
					'msg'=>'somthing went wrong from api side',
					'gameUrl'=>"",
					'userId'=>$USER_ID
				];
				echo json_encode($sss);
				
			}
	}
	
public function spribeGameList()
{
    return response()->json([
        'status' => 200,
        'message' => 'Game list fetched successfully',
        'data' => [
            ['id' => 737, 'name' => 'Aviator', 'status' => 1],
            ['id' => 635, 'name' => 'Dice', 'status' => 1],
            ['id' => 426, 'name' => 'Mines', 'status' => 1],
			['id' => 894, 'name' => 'Keno', 'status' => 1],
			['id' => 478, 'name' => 'Plinko', 'status' => 1],
			['id' => 826, 'name' => 'Hotline', 'status' => 1],
			['id' => 775, 'name' => 'Hilo', 'status' => 1],
			['id' => 1019, 'name' => 'Balloon', 'status' => 1],
			['id' => 904, 'name' => 'Big Goal Win', 'status' => 1],
			['id' => 5808, 'name' => 'Traders Fortune', 'status' => 1],
			['id' => 723, 'name' => 'Mini Roulette', 'status' => 1],
        ]
    ], 200);
}


	
	
	
	
}