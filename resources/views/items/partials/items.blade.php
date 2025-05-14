
    <div class="col-md-4 mb-3">
        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama Item</th>
                            <th>No</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['no'] }}</td>
                                <td>{{ $item['availableToSell'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@if(empty($items))
    <div class="col-12">
        <p class="text-center">Tidak ada data ditemukan.</p>
    </div>
@endif
