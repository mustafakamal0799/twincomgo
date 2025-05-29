<ul class="list-group">
@forelse ($items as $item)
    <li class="list-group-item">
        <strong>{{ $item['name'] }}</strong> - Kode: {{ $item['no'] }}
    </li>
@empty
    <li class="list-group-item">Tidak ada hasil ditemukan.</li>
@endforelse
</ul>
