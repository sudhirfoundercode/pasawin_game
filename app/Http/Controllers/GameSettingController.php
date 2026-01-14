<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class GameSettingController extends Controller
{
    // 🔹 Show setting page
    public function index()
    {
        $game = DB::table('game_settings')
            ->where('game_id', 1) // WINGO
            ->first();

        return view('user.wingo_settings', compact('game'));
    }

    // 🔹 Update ONLY winning percentage VALUE
    public function updatePercentage(Request $request)
    {
        $request->validate([
            'winning_percentage' => 'required|numeric|min:0|max:100'
        ]);

        DB::table('game_settings')
            ->where('game_id', 1)
            ->update([
                'winning_percentage' => $request->winning_percentage
            ]);

        return back()->with('success', 'Winning percentage updated successfully');
    }

    // 🔹 Update ONLY status (ON / OFF)
    public function updateStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:0,1'
        ]);

        DB::table('game_settings')
            ->where('game_id', 1)
            ->update([
                'status' => $request->status
            ]);

        return back()->with('success', 'Winning percentage status updated');
    }
}
