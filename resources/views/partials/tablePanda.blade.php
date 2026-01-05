@php use Illuminate\Support\Str; @endphp

@forelse ($warehousesPanda as $wh)
<tr>
    <td>{{ $wh['name'] }}</td>

    <td class="text-center" id="stock_{{ Str::slug($wh['name']) }}">
        {{ $wh['balance'] }}
    </td>

    <td class="text-center">{{ $wh['unit_display'] }}</td>
</tr>
@empty
<tr>
    <td colspan="3" class="text-center text-muted">Tidak ada gudang panda</td>
</tr>
@endforelse
