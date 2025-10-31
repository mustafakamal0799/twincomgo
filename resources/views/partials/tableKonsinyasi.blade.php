@forelse($warehousesKonsinyasi as $wh)
    <tr>
        <td>{{ $wh['name'] }}</td>
        <td class="text-center">{{ $wh['balance']}}</td>
        @php
            $balanceUnit = trim(str_replace(['[', ']'], '', $wh['balanceUnit']));
            $stock = $stokNew[$wh['id']]['balance'] ?? $wh['balance'];
            $ratio2 = $ratio ?? null;

            preg_match_all('/\b(PCS|METER|ROLL|DUS|PAKET|MTR|POTONG|BATANG|BOX|PACK)\b/i', $balanceUnit, $matches);
            
            preg_match('/^(\d+)/', $balanceUnit, $firstNumberMatch);
            $firstNumber = isset($firstNumberMatch[1]) ? (int)$firstNumberMatch[1] : null;

            $showBalanceUnit = false;

            if (count($matches[0]) > 1) {
                $showBalanceUnit = true;
            } elseif ($ratio2 && $firstNumber !== $stock) {
                $showBalanceUnit = true;
            }

            $unitOnly = preg_replace('/^[\d.,]+\s+/', '', $balanceUnit);
        @endphp
        <td class="text-center">
            {{ $showBalanceUnit ? $balanceUnit : $unitOnly }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="text-center text-muted">Tidak ada gudang konsinyasi</td>
    </tr>
@endforelse