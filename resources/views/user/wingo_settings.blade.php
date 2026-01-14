@extends('admin.body.adminmaster')

@section('admin')

<div class="container mt-4">
    <h3>Wingo Game Settings</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ===============================
        UPDATE WINNING PERCENTAGE VALUE
    ================================ --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Update Winning Percentage</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/admin/wingo/update-percentage') }}">
                @csrf

                <div class="form-group mb-3">
                    <label>Winning Percentage (%)</label>
                    <input type="number"
                           name="winning_percentage"
                           class="form-control"
                           value="{{ $game->winning_percentage ?? 0 }}"
                           min="0"
                           max="100"
                           required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Update Percentage
                </button>
            </form>
        </div>
    </div>

    {{-- ===============================
        ON / OFF WINNING PERCENTAGE
    ================================ --}}
    <div class="card">
        <div class="card-header">
            <strong>Winning Percentage Status</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/admin/wingo/update-status') }}">
                @csrf

                <div class="form-group mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1" {{ $game->status == 1 ? 'selected' : '' }}>
                            ON
                        </option>
                        <option value="0" {{ $game->status == 0 ? 'selected' : '' }}>
                            OFF
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-warning">
                    Update Status
                </button>
            </form>
        </div>
    </div>

</div>

@endsection
