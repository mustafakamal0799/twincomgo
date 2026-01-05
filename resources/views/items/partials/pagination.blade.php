@php
    $queryString = http_build_query($queryParams);
@endphp

@if($pageCount > 1)
    <nav>
        <ul class="pagination justify-content-center mb-0">
            @if($page > 1)
                <li class="page-item">
                    <a href="?page={{ $page - 1 }}&{{ $queryString }}" class="page-link page-link-ajax">&laquo; Prev</a>
                </li>
            @endif

            @if($page < $pageCount)
                <li class="page-item">
                    <a href="?page={{ $page + 1 }}&{{ $queryString }}" class="page-link page-link-ajax">Next &raquo;</a>
                </li>
            @endif
        </ul>
    </nav>
@endif
