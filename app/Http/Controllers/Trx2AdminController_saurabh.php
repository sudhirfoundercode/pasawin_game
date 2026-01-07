<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Trx2AdminController extends Controller
{
    // ===============================
    // MAIN VIEW (LIKE WINGO)
    // ===============================
    public function index($gameid)
    {
        $gameid = (int) $gameid;

        // 🔹 Bet logs (latest period)
        $bets = DB::table('betlogs')
            ->where('game_id', $gameid)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // 🔹 User bets
        $userBets = DB::table('bets')
            ->where('game_id', $gameid)
            ->orderByDesc('id')
            ->paginate(10);

        // 🔹 Future predictions
        $futurePredictions = DB::table('admin_winner_results as fpr')
            ->leftJoin('bet_results as br', 'br.games_no', '=', 'fpr.gamesno')
            ->select(
                'fpr.id',
                'fpr.gamesno',
                'fpr.number as predicted_number',
                DB::raw('IFNULL(br.number,"pending") as result_number'),
                'fpr.created_at',
                'fpr.updated_at'
            )
            ->orderByDesc('fpr.id')
            ->limit(10)
            ->get();

        // 🔹 Profit summary
        $total_admin_profit = DB::table('bets')->sum('amount');
        $total_user_profit  = DB::table('bets')->sum('win_amount');

        $today_admin_profit = DB::table('bets')
            ->whereDate('created_at', today())
            ->sum('amount');

        $today_user_profit = DB::table('bets')
            ->whereDate('created_at', today())
            ->sum('win_amount');

        $total_users_playing = DB::table('bets')
            ->where('game_id', $gameid)
            ->distinct('userid')
            ->count('userid');

        // 🔹 TRX2 Modes (NO DB IN BLADE)
        $gameModes = DB::table('game_settings')
            ->whereIn('id', [6, 7, 8, 9])
            ->orderByRaw('FIELD(id,6,7,8,9)')
            ->get();

        return view('trx2_prediction.index', compact(
            'gameid',
            'bets',
            'userBets',
            'futurePredictions',
            'total_admin_profit',
            'total_user_profit',
            'today_admin_profit',
            'today_user_profit',
            'total_users_playing',
            'gameModes'
        ));
    }

    // ===============================
    // AJAX FETCH (GRID DATA)
    // ===============================
    public function fetch($gameid)
    {
        $bets = DB::table('betlogs')
            ->select('number', DB::raw('SUM(amount) as amount'))
            ->where('game_id', $gameid)
            ->groupBy('number')
            ->get();

        return response()->json(['bets' => $bets]);
    }

    // ===============================
    // STORE RESULT (LIKE WINGO)
    // ===============================
    public function store(Request $request)
    {
        $request->validate([
            'game_no' => 'required',
            'game_id' => 'required|integer',
            'number'  => 'required|integer|min:0|max:9'
        ]);

        DB::table('admin_winner_results')->insert([
            'gamesno'     => $request->game_no,
            'game_id'     => $request->game_id,
            'number'      => $request->number,
            'status'      => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Result submitted successfully');
    }
}
