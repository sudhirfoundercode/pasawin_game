
@extends('admin.body.adminmaster')

@section('admin')

<!-- ✅ SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .game-container {
        max-width: 1100px;
        margin: auto;
        padding: 30px;
        background: #f4f6f9;
        border-radius: 15px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
    }

    .clickable-card {
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 15px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 1.2rem;
        box-shadow: 6px 6px 12px #d1d9e6, -6px -6px 12px #ffffff;
    }

    .clickable-card:hover {
        transform: scale(1.05);
        box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
    }

    .card-bg-andar {
        background: linear-gradient(135deg, #ff416c, #ff4b2b);
    }

    .card-bg-bahar {
        background: linear-gradient(135deg, #1d976c, #93f9b9);
    }

    .game-header {
        font-size: 2rem;
        font-weight: bold;
        /*text-align: center;*/
        color: #333;
        margin-bottom: 30px;
    }

    .amount-box {
        background: #ffffff;
        margin-top: 10px;
        border-radius: 10px;
        font-weight: bold;
        color: #333;
        padding: 10px;
        font-size: 0.95rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        text-align: center;
    }

    @media (max-width: 767px) {
        .clickable-card {
            height: 80px;
            font-size: 1rem;
        }

        .amount-box {
            font-size: 0.85rem;
        }
    }
</style>

<div class="container game-container mt-5">
    <input type="hidden" id="game_id" value="{{ $game_id }}">
    <input type="hidden" id="games_no" value="{{ $bets[0]->games_no ?? '' }}">

    

    {{-- 🔹 Profit Summary Boxes --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5>Total Admin Profit</h5>
                    <h3>₹{{ $total_admin_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <h5>Total User Profit</h5>
                    <h3>₹{{ $total_user_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow">
                <div class="card-body">
                    <h5>Today Admin Profit</h5>
                    <h3>₹{{ $today_admin_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <h5>Today User Profit</h5>
                    <h3>₹{{ $today_user_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
    
  <div class="row d-flex justify-content-between align-items-center">
    <!-- Left Side -->
    <div class="col-md-6">
        <div class="game-header">
            Period No - {{ $results->game_sr_num ?? '-' }}
        </div>
    </div>

    <!-- Right Side -->
    <div class="col-md-6 text-end">
        <b id="users_playing_block" style="font-weight: bold; font-size: 22px;">
            Total Users Playing - <span>{{ $total_users_playing ?? 0 }}</span>
        </b>
    </div>
</div>


    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-gamepad mr-2"></i>Game Result Entry</h4>
                    <!--<div id="gmsno" class="text-white ml-3"></div>-->
                </div>

                <div class="card-body bg-light">

                    <form action="{{ route('aviator.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="game_id" value="{{ $game_id }}">

                        <div class="form-row">
                            <div class="form-group col-md-5">
                                <label for="game_sr_num">Game Period</label>
                                <input type="number" name="game_sr_num" class="form-control" value="{{ $results->game_sr_num + 2 }}" required>
                            </div>

                            <div class="form-group col-md-5">
                                <label for="multiplier">Multiplier</label>
                                <input type="number" name="multiplier" class="form-control" step="any" placeholder="e.g. 1.25" required>
                            </div>
                        
                        <div class="form-group col-md-1" style="margin-top:27px">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Submit
                            </button>
                        </div>
                    </div>


                    </form>

                </div>
            </div>

        </div>
    </div>
    
    {{-- 🔹 Toggle Buttons --}}
    <div class="d-flex justify-content-start gap-3 mb-3 mt-4">
        <button class="btn btn-primary toggle-btn" data-target="future">Future Predictions</button>
        <button class="btn btn-secondary toggle-btn" data-target="user">User Bets</button>
    </div>

    {{-- 🔹 Future Predictions Table --}}
    <div id="table-future" class="toggle-table">
        <div class="card shadow">
            <div class="card-header bg-info text-white"><strong>Future Prediction List</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Period No</th><th>Predicted Number</th><th>Result</th><th>datetime</th></tr>
                    </thead>
                    <tbody>
                        @forelse($futurePredictions as $prediction)
                            <tr>
                                <td>{{ $prediction->id }}</td>
                                <td>{{ $prediction->game_sr_num }}</td>
                                <td>{{ $prediction->predicted_number }}</td>
                                <td>{!! $prediction->result_number === 'pending' ? '<span class="badge bg-warning text-dark">Pending</span>' : '<span class="badge bg-success">'.$prediction->result_number.'</span>' !!}</td>
                                <td>{{ $prediction->datetime }}</td>
                               
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No predictions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 🔹 User Bets Table --}}
    <div id="table-user" class="toggle-table d-none">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark"><strong>User Bet List</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Period Number</th>
                            <th>Game ID</th>
                            <th>Bet Amount</th>
                            <th>Win Amount</th>
                            <th>Status</th>
                            <th>datetime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userBets as $bet)
                            <tr>
                                <td>{{ $bet->id }}</td>
                                <td>{{ $bet->uid }}</td>
                                <td>{{ $bet->game_sr_num }}</td>
                                <td>{{ $bet->game_id }}</td>
                                <td>{{ $bet->amount }}</td>
                                <td>{{ $bet->win }}</td>
                                <td>{{ $bet->status }}</td>
                                <td>{{ $bet->created_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center">No user bets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                {{ $userBets->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- ✅ Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
    
    setInterval(() => {
        const block = document.getElementById('users_playing_block');
        if (!block) {
            console.warn('❌ users_playing_block was removed from the DOM!');
        } else {
            console.log('✅ users_playing_block still exists.');
        }
    }, 1000);
    
    <!-- Somewhere below (can be in <script> or layout file) -->

    function copyPeriodNo() {
        const periodText = document.getElementById("copy-period").innerText;
        navigator.clipboard.writeText(periodText)
            .then(() => {
                alert("Period No copied: " + periodText);
            })
            .catch(err => {
                alert("Failed to copy: " + err);
            });
    }

    
    function fetchData() {
        const gameid = {{ $game_id }};
        fetch('/fetch/' + gameid)
            .then(response => response.json())
            .then(data => {
                updateBets(data.bets);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function updateBets(bets) {
        let currentGameNo = '';
        $('#card-area .amount-box').each(function () {
            $(this).text('₹0');
        });

        bets.forEach(item => {
            $(`#amount-${item.number}`).text(`₹${item.amount}`);
            currentGameNo = item.games_no;
        });

        $('#gmsno').html(`<div class="game-header">Period No - ${currentGameNo}</div>`);
        $('#games_no').val(currentGameNo);
    }

    function refreshData() {
        fetchData();
        setInterval(fetchData, 5000);
    }

    $(document).ready(function () {
        refreshData();

        $('.clickable-card').on('click', function () {
            const number = $(this).data('number');
            const game_id = $('#game_id').val();
            const game_no = $('#games_no').val();

            $.ajax({
                url: "{{ route('colour_prediction.store') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    number: number,
                    game_id: game_id,
                    game_no: game_no
                },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Result Submitted!',
                        text: 'Your result was added successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    fetchData();
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong while submitting!',
                    });
                    console.error(xhr.responseText);
                }
            });
        });

        $('.toggle-btn').on('click', function () {
            let target = $(this).data('target');
            $('.toggle-table').addClass('d-none');
            $('#table-' + target).removeClass('d-none');
        });
    });

    function copyPeriodNo() {
        let period = $('#games_no').val();
        navigator.clipboard.writeText(period);
        $('#copy-period').text(period);
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Period number copied to clipboard.',
            timer: 1000,
            showConfirmButton: false
        });
    }

    setInterval(() => location.reload(), 20000);
    
    
</script>


@endsection





