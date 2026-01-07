<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Trx2PredictionController extends Controller
{
    /**
     * ==============================
     * TRX2 PREDICTION DASHBOARD
     * ==============================
     */
    public function trx2_prediction_create($gameid)
    {
        // ✅ TRX2 GAME ID GUARD
        if (!in_array($gameid, [6,7,8,9])) {
            abort(404);
        }

        // 🔹 Get bets for the current TRX2 game
        $bets = DB::table('trx2_betlogs')
            ->select(
                'trx2_betlogs.*',
                'game_settings.winning_percentage AS parsantage',
                'game_settings.id AS id'
            )
            ->leftJoin('game_settings', 'trx2_betlogs.game_id', '=', 'game_settings.id')
            ->where('trx2_betlogs.game_id', $gameid)
            ->orderByDesc('trx2_betlogs.id')
            ->limit(10)
            ->get();

        // 🔹 Latest period number
        $current_game_no = optional($bets->first())->games_no;

        /**
         * ==============================
         * PROFIT SUMMARY
         * ==============================
         */
        $today = Carbon::today();

        // Total profit (all time)
        $total = DB::table('trx2_bets')
            ->selectRaw('SUM(amount) as total_amount, SUM(win_amount) as total_win_amount')
            ->first();

        $total_admin_profit = ($total->total_amount ?? 0) - ($total->total_win_amount ?? 0);
        $total_user_profit  = $total->total_win_amount ?? 0;

        // Today profit
        $todayData = DB::table('trx2_bets')
            ->whereDate('created_at', $today)
            ->selectRaw('SUM(amount) as today_amount, SUM(win_amount) as today_win_amount')
            ->first();

        $today_admin_profit = ($todayData->today_amount ?? 0) - ($todayData->today_win_amount ?? 0);
        $today_user_profit  = $todayData->today_win_amount ?? 0;

        /**
         * ==============================
         * CURRENT PERIOD & USERS PLAYING
         * ==============================
         */
        $period_no = DB::table('trx2_betlogs')
            ->where('game_id', $gameid)
            ->orderByDesc('id')
            ->value('games_no');

        $total_users_playing = DB::table('trx2_bets')
            ->where('games_no', $period_no)
            ->distinct('userid')
            ->count('userid');

        /**
         * ==============================
         * FUTURE PREDICTIONS
         * ==============================
         */
        $futurePredictions = DB::table('trx2_admin_winner_results as fpr')
            ->select(
                'fpr.id',
                'fpr.gamesno',
                'fpr.number as predicted_number',
                DB::raw('IFNULL(fr.number, "pending") as result_number'),
                'fpr.created_at',
                'fpr.updated_at'
            )
            ->leftJoin(
                'trx2_bet_results as fr',
                'fr.games_no',
                '=',
                'fpr.gamesno'
            )
            ->orderByDesc('fpr.id')
            ->paginate(10);

        /**
         * ==============================
         * USER BETS
         * ==============================
         */
        $userBets = DB::table('trx2_bets')
            ->orderByDesc('id')
            ->paginate(10);

        /**
         * ==============================
         * GAME SETTINGS & MODES
         * ==============================
         */
        $gameSettings = DB::table('game_settings')->find($gameid);

        $gameModes = DB::table('game_settings')
            ->whereIn('name', [
                'TRX2 30 Second',
                'TRX2 1 Minute',
                'TRX2 3 Minute',
                'TRX2 5 Minute'
            ])
            ->orderByRaw(
                "FIELD(name, 'TRX2 30 Second', 'TRX2 1 Minute', 'TRX2 3 Minute', 'TRX2 5 Minute')"
            )
            ->get();

        return view('trx2_prediction.index', compact(
            'bets',
            'gameid',
            'total_admin_profit',
            'total_user_profit',
            'today_admin_profit',
            'today_user_profit',
            'futurePredictions',
            'userBets',
            'total_users_playing',
            'gameSettings',
            'gameModes'
        ));
    }

    /**
     * ==============================
     * FETCH LIVE BET DATA (AJAX)
     * ==============================
     */
    public function fetchData($gameid)
    {
        if (!in_array($gameid, [6,7,8,9])) {
            abort(404);
        }

        $bets = DB::select("
            SELECT 
                trx2_betlogs.*,
                game_settings.winning_percentage AS parsantage,
                game_settings.id AS id
            FROM trx2_betlogs
            LEFT JOIN game_settings ON trx2_betlogs.game_id = game_settings.id
            WHERE trx2_betlogs.game_id = ?
            LIMIT 20
        ", [$gameid]);

        return response()->json([
            'bets' => $bets,
            'gameid' => $gameid
        ]);
    }

    /**
     * ==============================
     * STORE FINAL RESULT
     * ==============================
     */
    public function store(Request $request)
    {
        $gameid  = $request->game_id;
        $gamesno = $request->game_no;
        $number  = $request->number;

        DB::table('trx2_admin_winner_results')->insert([
            'gamesno' => $gamesno,
            'gameId'  => $gameid,
            'number'  => $number,
            'status'  => 1
        ]);

        return redirect()->back();
    }

    /**
     * ==============================
     * STORE FUTURE RESULT
     * ==============================
     */
    public function future_store(Request $request)
    {
        $request->validate([
            'game_id' => 'required|integer',
            'game_no' => 'required|numeric',
            'number'  => 'required|numeric|min:0|max:9',
        ]);

        DB::table('trx2_admin_winner_results')->insert([
            'gamesno' => $request->game_no,
            'gameId'  => $request->game_id,
            'number'  => $request->number,
            'status'  => 1,
        ]);

        return redirect()->back()->with('success', 'Future TRX2 result added successfully.');
    }

    /**
     * ==============================
     * UPDATE WINNING PERCENTAGE
     * ==============================
     */
    public function color_update(Request $request)
    {
        DB::table('game_settings')
            ->where('id', $request->id)
            ->update([
                'winning_percentage' => $request->parsantage
            ]);

        return redirect()->back();
    }
}
