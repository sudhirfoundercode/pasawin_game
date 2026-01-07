@php
    $trx = DB::select("
        SELECT id, name
        FROM game_settings
        WHERE id IN (6,7,8,9)
    ");
@endphp

<li class="{{ Request::routeIs('trx') ? 'active' : '' }}">
    <a href="#trxMenu"
       data-bs-toggle="collapse"
       aria-expanded="false"
       aria-controls="trxMenu"
       class="dropdown-toggle">
        <i class="fa fa-list red_color"></i>
        <span>Trx Game</span>
    </a>

    <ul class="collapse list-unstyled"
        id="trxMenu"
        data-bs-parent="#sidebar">

        @foreach($trx as $item)
            <li class="{{ request()->route('id') == $item->id ? 'active' : '' }}">
                <a href="{{ route('trx2', $item->id) }}">
                    <span>{{ $item->name }}</span>
                </a>
            </li>
        @endforeach

    </ul>
</li>
