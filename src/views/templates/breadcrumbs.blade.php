<div role="navigation" aria-label="breadcrumbs navigation">
    <ul class="wy-breadcrumbs">
        @if (is_null($currentGroup))
            <li>Документация</li>
        @else
            <li><a href="{{ route('jsonrpcdoc.main') }}">Документация</a> »</li>
            @if (is_null($currentMethod))
                <li>{{ isset($smd['services'][$currentGroup]['description']) ? $smd['services'][$currentGroup]['description'] : 'Группа методов "' . $currentGroup . '"' }}</li>
            @else
                <li><a href="{{ route('jsonrpcdoc.group', ['group' => $currentGroup]) }}">
                        {{ isset($smd['services'][$currentGroup]['description']) ? $smd['services'][$currentGroup]['description'] : 'Группа методов "' . $currentGroup . '"' }}
                    </a> »
                </li>
                <li>{{ $currentMethod }}</li>
            @endif
        @endif
        <li class="wy-breadcrumbs-aside"></li>
    </ul>
    <hr>
</div>