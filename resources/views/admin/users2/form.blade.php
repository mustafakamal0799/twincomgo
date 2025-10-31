<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>{{ $user->exists ? 'Edit User' : 'Tambah User' }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h1 class="h3 mb-3">{{ $user->exists ? 'Edit User' : 'Tambah User' }}</h1>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ $user->exists ? route('users2.update',$user->id) : route('users2.store') }}">
    @csrf
    @if($user->exists) @method('put') @endif

    {{-- <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" name="name" value="{{ old('name',$user->name) }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" value="{{ old('email',$user->email) }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Password {{ $user->exists ? '(isi jika ingin ganti)' : '' }}</label>
      <input type="password" name="password" class="form-control" {{ $user->exists ? '' : 'required' }}>
    </div>

    <div class="mb-3">
      <label class="form-label">Konfirmasi Password</label>
      <input type="password" name="password_confirmation" class="form-control" {{ $user->exists ? '' : 'required' }}>
    </div> --}}

    <div class="mb-3">
      <label class="form-label">Kepala (Accurate Account)</label>
      <select name="accurate_account_id" class="form-select">
        <option value="">— Tidak di-assign —</option>
        @foreach($accounts as $a)
          <option value="{{ $a->id }}" @selected(old('accurate_account_id',$user->accurate_account_id)==$a->id)>
            {{ $a->label ?? ('AA#'.$a->id) }}
          </option>
        @endforeach
      </select>
      <small class="text-muted">Pilih kepala supaya user ini memakai token & session kepala tersebut.</small>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary">{{ $user->exists ? 'Update' : 'Simpan' }}</button>
      <a class="btn btn-outline-secondary" href="{{ route('users2.index') }}">Kembali</a>
    </div>
  </form>
</div>
</body>
</html>
