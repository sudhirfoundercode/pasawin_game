<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Storage;
class AviatorAdminController extends Controller
{
      

//   public function aviator_prediction_create(string $game_id)
//     {
	    
// 	    $perPage = 10;
	   
// 		$results = DB::table('aviator_result')
// 			->join('game_settings', 'aviator_result.game_id', '=', 'game_settings.id')
// 			->where('aviator_result.game_id', $game_id)
// 			->orderByDesc('aviator_result.id')
// 			->first();

//         $aviator_res = DB::table('aviator_result')->where('game_id',5)->orderByDesc('id')->paginate($perPage);

	   
//         return view('aviator.result')->with('results', $results)->with('game_id', $game_id)->with('aviator_res',$aviator_res);
//     }
 public function aviator_prediction_create(string $game_id)
{
    $perPage = 10;
    $today = \Carbon\Carbon::today();

    // 🔹 Latest Aviator Result for Given Game
    $latestResult = DB::table('aviator_result')
        ->join('game_settings', 'aviator_result.game_id', '=', 'game_settings.id')
        ->where('aviator_result.game_id', $game_id)
        ->orderByDesc('aviator_result.id')
        ->first();


    $period_no=$latestResult->game_sr_num;
    //dd($period_no);

    // 🔹 Total unique users playing in current period
    $total_users_playing = DB::table('aviator_bet')
        ->where('game_sr_num', $period_no)
        ->distinct('uid')
        ->count('uid');

    // 🔹 Profit Summary

    // Total profit (all time)
    $total = DB::table('aviator_bet')
        ->selectRaw('SUM(amount) as total_amount, SUM(win) as total_win_amount')
        ->first();

    $total_admin_profit = $total->total_amount - $total->total_win_amount;
    $total_user_profit = $total->total_win_amount;

    // Today's profit
    $todayData = DB::table('aviator_bet')
        ->whereDate('created_at', $today)
        ->selectRaw('SUM(amount) as today_amount, SUM(win) as today_win_amount')
        ->first();

    $today_admin_profit = $todayData->today_amount - $todayData->today_win_amount;
    $today_user_profit = $todayData->today_win_amount;

    // 🔹 Future Predictions with result match (or pending)
    $futurePredictions = DB::table('aviator_admin_result as fpr')
        ->select(
            'fpr.id',
            'fpr.game_sr_num',
            'fpr.number as predicted_number',
            DB::raw('IFNULL(fr.number, "pending") as result_number'),
            'fpr.datetime'
        )
        ->leftJoin('aviator_result as fr', 'fr.game_sr_num', '=', 'fpr.game_sr_num')
        ->orderByDesc('fpr.id')
        ->paginate($perPage);

    // 🔹 User Bets
    $userBets = DB::table('aviator_bet')
        ->orderByDesc('id')
        ->paginate($perPage);

    // 🔹 Aviator Results for Game ID = 5 (Hardcoded, if intentional)
    $aviator_res = DB::table('aviator_result')
        ->where('game_id', 5)
        ->orderByDesc('id')
        ->paginate($perPage);

    // 🔹 Return View
    return view('aviator.result', [
        'results' => $latestResult,
        'game_id' => $game_id,
        'aviator_res' => $aviator_res,
        'total_users_playing' => $total_users_playing,
        'total_admin_profit' => $total_admin_profit,
        'total_user_profit' => $total_user_profit,
        'today_admin_profit' => $today_admin_profit,
        'today_user_profit' => $today_user_profit,
        'futurePredictions' => $futurePredictions,
        'userBets' => $userBets,
    ]);
}


    public function aviator_fetchDatacolor($game_id)
    {
		
        $bets = DB::select("SELECT bet_logs.*,game_settings.winning_percentage AS percentage ,game_settings.id AS id FROM `bet_logs` LEFT JOIN game_settings ON bet_logs.game_id=game_settings.id where bet_logs.game_id=$game_id Limit 10");

        return response()->json(['bets' => $bets, 'game_id' => $game_id]);
    }
	
	
	public function aviator_store(Request $request)
	{
		
		  	date_default_timezone_set('Asia/Kolkata');
           $datetime = date('Y-m-d H:i:s');
		
	  
      $game_id =$request->game_id;
		 $game_sr_num =$request->game_sr_num;
		 $number=$request->number;
       $multiplier =$request->multiplier;
    //   dd($multiplier);
		
		DB::table('aviator_admin_result')->insert([
		'game_sr_num'=>$game_sr_num,
        'game_id'=>$game_id,
			'number'=>$multiplier,
			'multiplier'=>$multiplier,
			'status'=>1,
			'datetime'=>$datetime
		]);
		
             return redirect()->back(); 
	}
  
   public function aviator_update(Request $request)
      {
	   //dd($request);
	     	date_default_timezone_set('Asia/Kolkata');
           $datetime = date('Y-m-d H:i:s');
	   
	   $game_id=$request->game_id;
        $percentage = $request->winning_percentage;
	    
         //$data= DB::select("UPDATE `game_setting` SET `percentage` = '$percentage','datetime'='$datetime' WHERE `id` ='$gamid'");
	  $data =  DB::table('game_settings')->where('id',$game_id)->update(['winning_percentage'=>$percentage]);
         if($data){
        return redirect()->back()->with('message', "Percentage updated Successfully...!");
		 }else{
			 return 'Can not update';
		 }
      }
   
      
	
	  public function aviator_bet_history(string $game_id)
    {
		  $perPage = 10;

			$bets = DB::table('aviator_bet')
				->select('aviator_bet.id as id','aviator_bet.game_sr_num as game_sr_num','aviator_bet.amount as amount', 'aviator_bet.win as win','aviator_bet.created_at as datetime','users.u_id as username', 'users.mobile as mobile')
				->where('aviator_bet.game_id', $game_id)
				->join('users', 'aviator_bet.uid', '=', 'users.id')
				->orderByDesc('aviator_bet.id')
				->paginate($perPage);
		  
	    return view('aviator.bet')->with('bets', $bets); 
	  }
	
	

}


