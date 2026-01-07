@extends('admin.body.adminmaster')

@section('admin')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
/* ===== SAME CSS AS WINGO (UNCHANGED) ===== */
body { background:#f5f5f5; }
.container-fluid { max-width:1200px; margin:0 auto; }
.mode-tab-group{background:#4d4d4c;display:flex;border-radius:12px;overflow:hidden;margin:20px 0}
.mode-tab-group a{flex:1;text-align:center;padding:15px 0;color:#fff;text-decoration:none}
.mode-tab-group a.active{background:linear-gradient(45deg,#F0D58D,#CA9C49);color:#000;font-weight:bold}
.mode-tab-group img{width:44px;height:44px;margin:0 auto 6px}
.card-modern{background:#fff;border-radius:12px;margin-bottom:25px}
.results-container{display:flex;flex-wrap:wrap;justify-content:center;gap:20px}
.result-wrapper{width:18%;min-width:100px;text-align:center}
.data-count{width:70px;height:70px;border-radius:50%;background:#fff;cursor:pointer;position:relative}
.data-count img{width:40px;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)}
.amount-box{background:#fff;border-radius:8px;padding:10px 0;margin-top:12px;font-weight:600}
.category-row{display:flex;gap:15px;margin:15px 0}
.category-box{flex:1;padding:15px;border-radius:10px;color:#fff;font-weight:bold;text-align:center}
.green-box{background:#4CAF50}
.violet-box{background:#9C27B0}
.red-box{background:#F44336}
.big-box{background:#FFEB3B;color:#333}
.small-box{background:#03A9F4}
.current-timer-container{background:linear-gradient(135deg,#1e3c72,#2a5298);color:#fff;padding:25px;border-radius:12px;text-align:center}
.timer-value{font-size:48px;font-family:monospace;font-weight:bold}
</style>

<div class="container-fluid py-5">

{{-- ===== PROFIT BOXES ===== --}}
<div class="row mb-4">
  <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h5>Total Admin Profit</h5><h3>₹{{ $total_admin_profit }}</h3></div></div></div>
  <div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body"><h5>Total User Profit</h5><h3>₹{{ $total_user_profit }}</h3></div></div></div>
  <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h5>Today Admin Profit</h5><h3>₹{{ $today_admin_profit }}</h3></div></div></div>
  <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h5>Today User Profit</h5><h3>₹{{ $today_user_profit }}</h3></div></div></div>
</div>

{{-- ===== HEADER ===== --}}
<div class="d-flex justify-content-between align-items-center">
  <div id="gmsno" style="font-size:25px;font-weight:bold"></div>
  <div style="font-size:22px">Total Users Playing - {{ $total_users_playing }}</div>
</div>

{{-- ===== TIMER ===== --}}
<div class="current-timer-container my-3">
  <div class="timer-label">
    @if($gameid==6) TRX2 30 SECOND GAME
    @elseif($gameid==7) TRX2 1 MINUTE GAME
    @elseif($gameid==8) TRX2 3 MINUTE GAME
    @elseif($gameid==9) TRX2 5 MINUTE GAME
    @endif
  </div>
  <div class="timer-value" id="current-timer">
    @if($gameid==6)00:30
    @elseif($gameid==7)01:00
    @elseif($gameid==8)03:00
    @elseif($gameid==9)05:00
    @endif
  </div>
</div>

{{-- ===== MODE TABS ===== --}}
<div class="mode-tab-group">
  @foreach($gameModes as $mode)
    @php
      $isActive = $gameid == $mode->id;
      $img = $isActive
        ? 'https://root.bdgcassino.com/wingo/redWatch.png'
        : 'https://root.bdgcassino.com/wingo/grayWatch.png';
    @endphp

    <a href="{{ route('trx2', $mode->id) }}"
       class="{{ $isActive ? 'active' : '' }}">
        <img src="{{ $img }}">
        <span>{{ $mode->name }}</span>
    </a>
  @endforeach
</div>


{{-- ===== RESULTS ===== --}}
<div class="card-modern">
<div class="p-3">

<div class="category-row">
  <div class="category-box green-box">Green <div id="green-amount">-</div></div>
  <div class="category-box violet-box">Violet <div id="violet-amount">-</div></div>
  <div class="category-box red-box">Red <div id="red-amount">-</div></div>
</div>

<div class="results-container">
@foreach(range(0,9) as $num)
<div class="result-wrapper">
  <div class="data-count" data-number="{{ $num }}" data-gameid="{{ $gameid }}">
    <img src="https://root.bdgcassino.com/wingo/{{ $num }}.png">
  </div>
  <div class="amount-box" id="amount-{{ $num }}">-</div>
</div>
@endforeach
</div>

<div class="category-row">
  <div class="category-box big-box">Big <div id="big-amount">-</div></div>
  <div class="category-box small-box">Small <div id="small-amount">-</div></div>
</div>

</div>
</div>

{{-- ===== FUTURE RESULT ===== --}}
<div class="card-modern p-3">
<form method="POST" action="{{ route('trx2.store') }}">
@csrf
<input type="hidden" name="game_id" value="{{ $gameid }}">
<input name="game_no" class="form-control mb-2" placeholder="Period No" required>
<select name="number" class="form-control mb-2" required>
<option value="">Select</option>
@for($i=0;$i<=9;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
</select>
<button class="btn btn-success w-100">Submit</button>
</form>
</div>

</div>

{{-- ===== SCRIPTS ===== --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

<script>
function formatTime(s){return String(Math.floor(s/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0')}

const socket=io('https://aviatorudaan.com/');
socket.on('bdgcasino_wingo',d=>{
const t=JSON.parse(d);
let sec=0;
if({{ $gameid }}==6)sec=t.timerBetTime;
if({{ $gameid }}==7)sec=t.oneMinTimer;
if({{ $gameid }}==8)sec=t.threeMinTimer;
if({{ $gameid }}==9)sec=t.fiveMinTimer;
$('#current-timer').text(formatTime(sec));
});

function fetchData(){
fetch('/trx2-fetch/{{ $gameid }}')
.then(r=>r.json())
.then(d=>{
if(!d.bets?.length)return;
$('#gmsno').text('Period No – ' + d.games_no);

let g=v=r=s=b=0;
d.bets.forEach(i=>{
$('#amount-'+i.number).text(i.amount||'-');
if(i.number==0||i.number==5)v+=i.amount;
else if([1,3,7,9].includes(i.number))r+=i.amount;
else g+=i.amount;
i.number>=5?b+=i.amount:s+=i.amount;
});
$('#green-amount').text('₹'+g);
$('#violet-amount').text('₹'+v);
$('#red-amount').text('₹'+r);
$('#big-amount').text('₹'+b);
$('#small-amount').text('₹'+s);
});
}
fetchData();
setInterval(fetchData,5000);
</script>
@endsection
