@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ruangan</h1>
    <ul>
        @foreach($rooms as $room)
            <li>{{ $room->name }} - {{ $room->location ?? '-' }} ({{ $room->capacity }})</li>
        @endforeach
    </ul>
</div>
@endsection
