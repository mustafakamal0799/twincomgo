<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" />
  @fluxAppearance
</head>
<body>
  <div class="card m-2 shadow-md">
    <div class="flex gap-2 justify-center m-2 p-4">
      <h1>Daftar item</h1>
      <div class="row">
        <div class="col-2">
          <label for="item-name" class="form-label">Kategori</label>
          <select class="form-select form-select-sm rounded shadow-sm" id="kategori" name="kategori">
            <option value="">Pilih Kategori</option>
          </select>
        </div>
        <div class="col-2">
          <label for="item-name" class="form-label">Kategori</label>
          <select class="form-select form-select-sm rounded shadow-sm" id="kategori" name="kategori">
            <option value="">Pilih Kategori</option>
          </select>
        </div>
        <div class="col-2">
          <label for="item-name" class="form-label">Kategori</label>
          <select class="form-select form-select-sm rounded shadow-sm" id="kategori" name="kategori">
            <option value="">Pilih Kategori</option>
          </select>
        </div>
        <div class="col-2">
          <label for="item-name" class="form-label">Kategori</label>
          <select class="form-select form-select-md shadow-sm form-select-rounded" id="kategori" name="kategori">
            <option value="">Pilih Kategori</option>
          </select>
        </div>
        <div class="col-3">
          <div class="mb-3">
            <label class="form-label">Separated inputs</label>
            <div class="row g-2">
              <div class="col">
                <input type="text" class="form-control form-control-rounded shadow-sm form-control-md" placeholder="Search for…" />
              </div>
              <div class="col-auto">
                <a href="#" class="btn btn-icon btn-pill shadow-sm" aria-label="Button">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="icon"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    fill="none"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <circle cx="10" cy="10" r="7" />
                    <line x1="21" y1="21" x2="15" y2="15" />
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-1">
          <flux:button variant="primary" color="zinc">Button</flux:button>
        </div>
      </div>
    </div>
  </div>
  <div class="card m-2 rounded-md">  
    <div class="table-responsive ">
      <table class="table table-vcenter">
        <thead class="sticky-top">
          <tr class="text-center">
            <th scope="col">No</th>
            <th scope="col">Nama</th>
            <th scope="col">Harga</th>
            <th scope="col">Stok</th>
            <th scope="col">Satuan</th>
          </tr>
        </thead>
        <tbody id="item-table-body">
          @include('partials.item-rows', ['items' => $items])
        </tbody>
      </table>
    </div>
  </div>
  
  @fluxScripts
  <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
</body>
</html>