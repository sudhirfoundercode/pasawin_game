@extends('admin.body.adminmaster')

@section('admin')

<div class="container-fluid mt-2">
	@if(session('success'))
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			{{ session('success') }}
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	@endif

	@if(session('error'))
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			{{ session('error') }}
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	@endif
    <div class="row">
        <div class="col-md-12">
            <div class="white_shd full margin_bottom_30">
                <div class="full graph_head">
                    <div class="heading1 margin_0 d-flex">
                        <h2>USDT Deposit List</h2>
                        {{-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModalCenter" style="margin-left:620px;">Add Work Name</button> --}}
                    </div>
                </div>

                <div class="table_section padding_infor_info">
                    <div class="table-responsive-sm">
                        <table id="example" class="table table-striped" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Id</th>
                                    <th>User Id</th>
                                    <th>User Name</th>
                                    <!--<th>Mobile</th>-->
                                    <th>Order Id</th>
                                    <th>INR Amount</th>
                                    <th>USDT Amount</th>
                                     <th>screenshot</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deposits as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->userid }}</td>
                                    <td>{{ $item->uname }}</td>
                                    <!--<td>{{ $item->mobile }}</td>-->
                                    <td>{{ $item->order_id }}</td>
                                    <td>{{ $item->cash }}</td>
                                    <td>{{ $item->usdt_amount }}</td>
									<!-- Thumbnail inside table cell -->
<td>
    <img src="{{ 'https://root.bdgcassino.com/'.$item->screenshot }}" 
         alt="screenshot" 
         style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;" 
         data-bs-toggle="modal" 
         data-bs-target="#imageModal{{ $item->id }}">
</td>

<!-- Bootstrap Modal -->
<div class="modal fade" id="imageModal{{ $item->id }}" tabindex="-1" aria-labelledby="imageModalLabel{{ $item->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalLabel{{ $item->id }}">Screenshot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="{{ 'https://root.bdgcassino.com/'.$item->screenshot }}" 
             alt="screenshot full" 
             class="img-fluid" 
             style="max-height: 500px;">
      </div>
    </div>
  </div>
</div>

                                    
     <td>
    @if($item->status == 1)
        <div class="dropdown">
            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Pending
            </button>
            <ul class="dropdown-menu">

                {{-- Mark as Success --}}
                <li>
                    <form method="POST" action="{{ route('payin.update.status') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item->id }}">
						<input type="hidden" name="userid" value="{{ $item->userid }}">
                        <input type="hidden" name="status" value="2">
                        <button type="submit" class="dropdown-item text-primary">Mark as Success</button>
                    </form>
                </li>

                {{-- Mark as Reject --}}
                <li>
                    <form method="POST" action="{{ route('payin.update.status') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item->id }}">
						<input type="hidden" name="userid" value="{{ $item->userid }}">
                        <input type="hidden" name="status" value="3">
                        <button type="submit" class="dropdown-item text-danger">Mark as Reject</button>
                    </form>
                </li>

            </ul>
        </div>
    @elseif($item->status == 2)
        <button class="btn btn-success btn-sm">Success</button>
    @elseif($item->status == 3)
        <button class="btn btn-danger btn-sm">Reject</button>
    @else
        <span class="badge badge-secondary">Unknown</span>
    @endif
</td>

                                    <td>{{ $item->created_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- JS Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

@endsection
