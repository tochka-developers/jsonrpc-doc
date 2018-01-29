@if (!empty($parent))
    <ul class="current">
        @foreach ($smd['services'][$parent]['methods'] as $methodKey => $method)
            <li class="toctree-l1 {{ $methodKey === $currentMethod ? 'current' : '' }}">
                <a class="current reference internal" href="{{ url('/' . $parent . '/' . $methodKey) }}">
                    <strong>{{ $method['name'] }}</strong><br>
                    @if (!empty($method['description']))
                        <span class="description">{{ $method['description'] }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
@else
    <ul class="current">
        @foreach ($smd['services'] as $groupKey => $group)
        <li class="toctree-l1 {{ $groupKey === $currentGroup ? 'current' : '' }}">
            <a class="current reference internal" href="{{ url('/' . $groupKey) }}">
                @if (!empty($group['methods']))
                    <span class="toctree-expand"></span>
                @endif
                {{ $group['description'] ?? $group['name'] }}
            </a>
            @if ($groupKey === $currentGroup || $exposed)
            <ul>
                @foreach ($group['methods'] as $methodKey => $method)
                    <li class="toctree-l2 {{ $methodKey === $currentMethod ? 'current' : '' }}">
                        <a class="reference internal" href="{{ url('/' . $groupKey . '/' . $methodKey) }}">
                            {{ $method['name'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
            @endif
        </li>
        @endforeach
    </ul>
@endif