@extends('admin.body.adminmaster')

@section('admin')
<div class="container-fluid mt-4">

    <h3 class="mb-3">👥 User-wise Admin Dashboard</h3>

    <table class="table table-bordered table-striped table-sm">
        <thead class="table-dark">
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Deposit</th>
                <th>Total Bet</th>
                <th>Total Win</th>
                <th>Admin Profit</th>
                <th>Games</th>
                <th>Wins</th>
                <th>Win %</th>
                <th>Last 3 Wins</th>
                <th>VIP</th>
                <th>Suspicious</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr
                @if($u->suspicious_flag == 'YES')
                    style="background:#ffe6e6"
                @elseif($u->vip == 'YES')
                    style="background:#e6fff2"
                @endif
            >
                <td>{{ $u->user_id }}</td>
                <td>{{ $u->name }}</td>
                <td>₹{{ number_format($u->total_deposit) }}</td>
                <td>₹{{ number_format($u->total_bet) }}</td>
                <td>₹{{ number_format($u->total_win) }}</td>
                <td>
                    @if($u->admin_profit >= 0)
                        <span class="text-success">₹{{ number_format($u->admin_profit) }}</span>
                    @else
                        <span class="text-danger">₹{{ number_format($u->admin_profit) }}</span>
                    @endif
                </td>
                <td>{{ $u->total_games }}</td>
                <td>{{ $u->total_wins }}</td>
                <td>{{ $u->win_rate }}%</td>
                <td>{{ $u->last_3_wins }}</td>
                <td>
                    @if($u->vip == 'YES')
                        ⭐ VIP
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($u->suspicious_flag == 'YES')
                        🚨 YES
                    @else
                        NO
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
