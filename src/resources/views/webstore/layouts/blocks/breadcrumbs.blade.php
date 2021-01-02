@if (!empty($breadCrumbs) && is_array($breadCrumbs))
    <ol class="breadcrumb">
        @if (!empty($breadCrumbs['showHome']))
            <li><a href="{{ url('/store') }}">Home</a></li>
        @endif
        @foreach ($breadCrumbs['crumbs'] as $breadCrumb)
            <li class="{{ !empty($breadCrumb['isActive']) ? 'active' : '' }}">
                @if (empty($breadCrumb['isActive']))
                    <a href="{{ $breadCrumb['href'] ?? 'javascript:void(0);' }}">
                        @endif
                        {{ $breadCrumb['text'] ?? ''}}
                        @if (empty($breadCrumb['isActive']))
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
@endif