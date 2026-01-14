@extends('admin.body.adminmaster')

@section('admin')

<style>
.dataTables_paginate.paging_simple_numbers {
    display: none !important;
}
.dataTables_info{
	display: none !important;
	}
    .btn-group .btn {
        margin-right: 5px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .btn-group {
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>

<style>
    th{
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>

<div class="container-fluid">
<div class="row">
<div class="col-md-12">

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="white_shd full margin_bottom_30">
<div class="full graph_head">
<div class="heading1 margin_0 d-flex">
    <h2>User List</h2>
</div>
</div>

<div class="table_section padding_infor_info">
<div class="table-responsive-sm">

<table id="example" class="table table-striped" style="width:100%">
<thead class="thead-dark">
<tr>
    <th>Id</th>
    <th>user_id</th>
    <th>User_name</th>
    <th>Email</th>
    <th>Mobile</th>
    <th>Sponser</th>
    <th>Sponser Id</th>
    <th>Wallet</th>
    <th>Winning_Wallet</th>
    <th>Commission</th>
    <th>Bonus</th>
    <th>Turn Over</th>
    <th>Today TurnOver</th>
    <th>Bet Amount</th>
    <th>Password</th>
    <th>Date</th>
    <th>Status</th>
    <th>Action</th>
    <th>filterSubordinateData</th>
    <th>ViewMoreDetails</th>
</tr>
</thead>

<tbody>
@foreach ($users as $item)
<tr>
    <td>{{$item->id}}</td>
    <td>{{$item->u_id}}</td>
    <td>{{$item->username}}</td>
    <td>{{$item->email}}</td>
    <td>{{$item->mobile}}</td>
    <td>{{$item->sname}}</td>

    <td>
        {{$item->referral_user_id}}
        <i class="fa fa-edit mt-1 ml-3" data-toggle="modal"
           data-target="#editReferralModal{{$item->id}}" style="font-size:20px"></i>

        <div class="modal fade" id="editReferralModal{{$item->id}}" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('referral.update', $item->id) }}" method="post">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Sponser ID</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="text" class="form-control" name="referral_user_id"
                                   value="{{$item->referral_user_id}}">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                    data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </td>

    <td>
        {{$item->wallet}}
        <div class="btn-group">
            <button class="btn btn-info btn-sm" data-toggle="modal"
                    data-target="#exampleModalCenter{{$item->id}}">
                <i class="fa fa-plus" style="font-size:20px"></i>
            </button>
            <button class="btn btn-danger btn-sm" data-toggle="modal"
                    data-target="#subtractWalletModal{{$item->id}}">
                <i class="fa fa-minus" style="font-size:20px"></i>
            </button>
        </div>
    </td>

    <td>{{$item->winning_wallet}}</td>
    <td>{{$item->commission}}</td>
    <td>{{$item->bonus}}</td>
    <td>{{$item->turnover}}</td>
    <td>{{$item->today_turnover}}</td>

    <td>
        {{$item->recharge}}
        <div class="btn-group">
            <button class="btn btn-info btn-sm" data-toggle="modal"
                    data-target="#exampleBetamountModalCenter{{$item->id}}">
                <i class="fa fa-plus" style="font-size:20px"></i>
            </button>
            <button class="btn btn-danger btn-sm" data-toggle="modal"
                    data-target="#subtractBetamountModal{{$item->id}}">
                <i class="fa fa-minus" style="font-size:20px"></i>
            </button>
        </div>
    </td>

    <td>{{$item->password}}</td>
    <td>{{$item->created_at}}</td>

    @if($item->status == 1)
    <td>
        <a href="{{route('user.inactive', $item->id)}}">
            <i class="fa fa-check-square-o green_color" style="font-size:30px"></i>
        </a>
    </td>
    @else
    <td>
        <a href="{{route('user.active', $item->id)}}">
            <i class="fa fa-ban red_color" style="font-size:30px"></i>
        </a>
    </td>
    @endif

    <td>
        <a href="{{route('userdetail', $item->id)}}">
            <i class="fa fa-eye mt-1 ml-2" style="font-size:30px"></i>
        </a>
    </td>

    <td>
        <a href="{{ route('filterSubordinateData', $item->id) }}"
           class="btn btn-sm btn-primary">Subordinates</a>
    </td>

    <td>
        <a href="{{ route('all_details', $item->id) }}"
           class="btn btn-sm btn-success">ViewMoreDetails</a>
    </td>
</tr>
@endforeach
</tbody>
</table>

<!-- 🔽 ONLY ADDITION : BACKEND PAGINATION FOOTER -->
<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">

    <form method="GET">
        Items per page :
        <select name="per_page" onchange="this.form.submit()">
            @foreach([10,20,30,40] as $n)
                <option value="{{ $n }}"
                    {{ request('per_page',8)==$n ? 'selected' : '' }}>
                    {{ $n }}
                </option>
            @endforeach
        </select>

        &nbsp;
        {{ $users->firstItem() }}–{{ $users->lastItem() }}
        of {{ $users->total() }} items
    </form>

    <div>
        {{ $users->links('pagination::bootstrap-4') }}
    </div>
</div>
<!-- 🔼 ONLY ADDITION -->

</div>
</div>
</div>
</div>
</div>
</div>

<script>
$('#myModal').on('shown.bs.modal', function () {
    $('#myInputs').trigger('focus')
})
</script>

@endsection
