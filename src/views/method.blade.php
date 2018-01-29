@extends('jsonrpcdoc::templates.layout')

@section('title', $methodInfo['name'])

@section('content')
    <div class="section" id="header">
        <h1>
            {{ $methodInfo['name'] }}
            <a class="headerlink" href="#header" title="Ссылка на этот заголовок">¶</a>
        </h1>
        <p>{{ $methodInfo['description'] }}</p>
        @if (!empty($methodInfo['note']))
            <div class="admonition note">
                <p class="first admonition-title">Примечание</p>
                <p class="last">{{ $methodInfo['note'] }}</p>
            </div>
        @endif
        @if (!empty($methodInfo['warning']))
            <div class="admonition warning">
                <p class="first admonition-title">Внимание</p>
                <p class="last">{{ $methodInfo['warning'] }}</p>
            </div>
        @endif
        @if (!empty($methodInfo['parameters']))
        <div class="section" id="parameters">
            <h3>
                @if ($smd['namedParameters'])
                    Именованные параметры:
                @else
                    Параметры:
                @endif
                <a class="headerlink" href="#parameters" title="Ссылка на этот заголовок">¶</a>
            </h3>

            @include('jsonrpcdoc::partials.params', ['parameters' => $methodInfo['parameters']])
        </div>
        @endif
        @if (!empty($methodInfo['returns']))
        <div class="section" id="return">
            <h3>
                Результат:
                @if (!empty($methodInfo['returns']['types']))
                    @foreach($methodInfo['returns']['types'] as $type)
                        <span class="guilabel">{{ $type }}</span>
                    @endforeach
                @elseif (!empty($methodInfo['returns']['type']))
                    <span class="guilabel">{{ $methodInfo['returns']['type'] }}</span>
                @else
                    <span class="guilabel">mixed</span>
                @endif
                <a class="headerlink" href="#return" title="Ссылка на этот заголовок">¶</a>
            </h3>
            {{ $methodInfo['returns']['description'] ?? '' }}
            @if (!empty($methodInfo['returnParameters']))
                @include('jsonrpcdoc::partials.returns', ['parameters' => $methodInfo['returnParameters']])
            @endif
        </div>
        @endif
        @if (!empty($methodInfo['requestExample']))
        <div class="section" id="requestExample">
            <h3>
                Пример запроса:
                <a class="headerlink" href="#requestExample" title="Ссылка на этот заголовок">¶</a>

                <pre>
                    <code class="json">{{ $methodInfo['requestExample'] }}</code>
                </pre>
            </h3>
        </div>
        @endif
        @if (!empty($methodInfo['responseExample']))
            <div class="section" id="responseExample">
                <h3>
                    Пример ответа:
                    <a class="headerlink" href="#responseExample" title="Ссылка на этот заголовок">¶</a>

                    <pre>
                        <code class="json">{{ $methodInfo['responseExample'] }}</code>
                    </pre>
                </h3>
            </div>
        @endif
    </div>
@endsection