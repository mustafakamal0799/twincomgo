<div class="d-flex justify-content-center">
    <nav>
        <ul class="pagination">
            @if ($pagination['prev_page_url'])
                <li class="page-item"><a class="page-link" href="{{ $pagination['prev_page_url'] }}">«</a></li>
            @endif

            @for ($i = 1; $i <= $pagination['last_page']; $i++)
                <li class="page-item {{ $i == $pagination['current_page'] ? 'active' : '' }}">
                    <a class="page-link" href="{{ route('items.index', ['page' => $i, 'q' => request('q'), 'stok_ada' => request('stok_ada')]) }}">{{ $i }}</a>
                </li>
            @endfor

            @if ($pagination['next_page_url'])
                <li class="page-item"><a class="page-link" href="{{ $pagination['next_page_url'] }}">»</a></li>
            @endif
        </ul>
    </nav>
</div>
