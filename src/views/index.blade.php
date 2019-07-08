@extends('jsonrpcdoc::templates.layout')

@section('title', 'Page Title')

@section('content')
    <div class="section" id="header">
        <h1>
            @empty ($currentGroup)
                Добро пожаловать!
            @else
                @if (!empty($smd['services'][$currentGroup]['description']))
                    {{ $smd['services'][$currentGroup]['description'] }}
                @else
                    Группа методов "{{ $currentGroup }}"
                @endif
            @endempty
            <a class="headerlink" href="#header" title="Ссылка на этот заголовок">¶</a>
        </h1>
        @empty ($currentGroup)
            <p>Содержание:</p>
        @else
            <p>Методы:</p>
        @endempty
        <div class="toctree-wrapper compound">
            @isLumen
                @include('jsonrpcdoc::templates.menuLumen', ['exposed' => true, 'parent' => $currentGroup])
            @else
                @include('jsonrpcdoc::templates.menu', ['exposed' => true, 'parent' => $currentGroup])
            @endisLumen
        </div>
    </div>
@endsection