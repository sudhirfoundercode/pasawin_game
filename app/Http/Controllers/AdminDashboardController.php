<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function userDashboard()
    {
        /*
        |--------------------------------------------------------------------------
        | CONFIG
        |--------------------------------------------------------------------------
        */
        $VIP_DEPOSIT_LIMIT = 10000;

        /*
        |--------------------------------------------------------------------------
        | MAIN USER QUERY
        |--------------------------------------------------------------------------
        | 1 = WIN
        | 2 = LOSS
        */
        $users = DB::select("
            SELECT 
                u.id AS user_id,
                u.name,

                -- Total successful deposit (cash only)
                IFNULL((
                    SELECT SUM(p.cash)
                    FROM payins p
                    WHERE p.user_id = u.id AND p.status = 2
                ), 0) AS total_deposit,

                -- Total bet amount
                IFNULL((
                    SELECT SUM(b.amount)
                    FROM bets b
                    WHERE b.userid = u.id
                ), 0) AS total_bet,

                -- Total win amount
                IFNULL((
                    SELECT SUM(b.win_amount)
                    FROM bets b
                    WHERE b.userid = u.id AND b.status = 1
                ), 0) AS total_win,

                -- Total games played
                (
                    SELECT COUNT(*)
                    FROM bets b
                    WHERE b.userid = u.id
                ) AS total_games,

                -- Total wins
                (
                    SELECT COUNT(*)
                    FROM bets b
                    WHERE b.userid = u.id AND b.status = 1
                ) AS total_wins,

                -- Last 3 rounds win count (continuous win check)
                (
                    SELECT COUNT(*)
                    FROM bets b
                    WHERE b.userid = u.id
                      AND b.status = 1
                      AND b.games_no >= (
                          SELECT MAX(games_no) - 3 FROM bets
                      )
                ) AS last_3_wins,

                -- Suspicious flag (7 wins in last 10 rounds with small bet)
                (
                    SELECT 
                        CASE 
                            WHEN COUNT(*) >= 7 THEN 'YES'
                            ELSE 'NO'
                        END
                    FROM bets b
                    WHERE b.userid = u.id
                      AND b.games_no >= (
                          SELECT MAX(games_no) - 10 FROM bets
                      )
                      AND b.status = 1
                      AND b.amount <= 100
                ) AS suspicious_flag

            FROM users u
        ");

        /*
        |--------------------------------------------------------------------------
        | CALCULATE EXTRA FIELDS
        |--------------------------------------------------------------------------
        */
        foreach ($users as $u) {

            // Admin profit = total bet - total win
            $u->admin_profit = $u->total_bet - $u->total_win;

            // Win rate %
            $u->win_rate = $u->total_games > 0
                ? round(($u->total_wins / $u->total_games) * 100, 2)
                : 0;

            // VIP status
            $u->vip = ($u->total_deposit >= $VIP_DEPOSIT_LIMIT) ? 'YES' : 'NO';
        }

        return view('admin.user_dashboard', compact('users'));
    }
}
