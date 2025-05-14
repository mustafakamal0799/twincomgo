@extends('layout')

@section('content')
    <h1>Halaman admin</h1>


    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('sync.accurate') }}" method="POST" onsubmit="return confirm('Yakin ingin melakukan sinkronisasi?')">
        @csrf
        <button type="submit" class="btn btn-primary">
            🔄 Sinkronisasi Data Accurate
        </button>
    </form>
    <a href="{{route('items.index')}}" class="btn btn-primary">List Item</a>    
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>{{$user->email}}</td>
                    <td>{{$user->name}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>    
@endsection