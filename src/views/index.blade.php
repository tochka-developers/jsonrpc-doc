@extends('jsonrpcdoc::templates.layout')

@section('title', 'Page Title')

@section('content')
    <div class="section" id="id1">
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
            <a class="headerlink" href="#id1" title="Ссылка на этот заголовок">¶</a>
        </h1>
        @empty ($currentGroup)
            <p>Содержание:</p>
        @else
            <p>Методы:</p>
        @endempty
        <div class="toctree-wrapper compound">
            @include('jsonrpcdoc::templates.menu', ['exposed' => true, 'parent' => $currentGroup])
        </div>
    </div>
@endsection