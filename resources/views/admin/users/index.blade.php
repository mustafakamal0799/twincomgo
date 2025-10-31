

{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="p-4">
    <h1 class="mb-3">Mapping User → Head Account</h1>

@if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
@if($errors->any()) <div class="alert alert-danger">{{ implode(', ', $errors->all()) }}</div> @endif

<form method="GET" class="mb-3 d-flex gap-2">
  <input name="q" value="{{ $q }}" class="form-control" placeholder="Cari nama/email">
  <button class="btn btn-secondary">Cari</button>
</form>

<table class="table table-sm align-middle">
  <thead>
    <tr>
      <th>Nama</th>
      <th>Email</th>
      <th>Account</th>
      <th style="width:420px">Head Account</th>
    </tr>
  </thead>
  <tbody>
    @foreach($users as $u)
      <tr>
        <td>{{ $u->name }}</td>
        <td>{{ $u->email }}</td>
        <td>{{ $u->accurate_account_id }}</td>
        <td>
          <form method="POST" action="{{ route('admin.users.set-head', $u) }}" class="d-flex gap-2">
            @csrf
            <select name="accurate_account_id" class="form-select">
              @foreach($heads as $h)
                <option value="{{ $h->id }}" @selected($u->accurate_account_id == $h->id)>
                  {{ $h->name }} (ID: {{ $h->id }})
                </option>
              @endforeach
            </select>
            <button class="btn btn-primary">Simpan</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
</div>

{{-- {{ $users->links() }} --}}
@endsection
