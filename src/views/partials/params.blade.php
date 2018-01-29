<ul class="simple">
    @foreach ($parameters as $parameter)
        <li>
            @php
                $type = $parameter['type'] ?? 'mixed';
                $name = uniqid($parameter['name']);
                if (!empty($parameter['parameters'])) {
                    $inlineParameters = $parameter['parameters'];
                } elseif (isset($methodInfo['objects'][$type]['parameters'])) {
                    $inlineParameters = $methodInfo['objects'][$type]['parameters'];
                    $type = 'object';
                } elseif (isset($smd['objects'][$type]['parameters'])) {
                    $inlineParameters = $smd['objects'][$type]['parameters'];
                    $type = 'object';
                } else {
                    $inlineParameters = [];
                }
            @endphp

            <code class="docutils {{ empty($parameter['optional']) ? 'required' : 'literal' }}">{{ $parameter['name'] }}</code>
            <span class="guilabel">{{ $type }}{{ !empty($parameter['array']) ? '[]' : '' }}</span>

            @if (isset($parameter['default']) || !empty($parameter['optional']) || $type === 'enum')
                <input type="checkbox" name="additional-{{ $name }}"
                       id="additional-{{ $name }}"
                       class="show-additional">
                <label for="additional-{{ $name }}" title="Дополнительная информация">
                    {{ !empty($parameter['description']) ? ' - '.$parameter['description'] : ' ' }}
                    <i class="fa fa-caret-down"></i>
                </label>
                <div class="additional">
                    @if (!empty($parameter['optional']))
                        <div class="default">
                            Необязательный параметр
                        </div>
                    @endif
                    @if (isset($parameter['default']))
                        <div class="default">
                            Значение по умолчанию: <code class="docutils"><span
                                        class="pre">{{ $parameter['default'] }}</span></code>
                        </div>
                    @endif
                    @if ($type === 'enum')
                        @if (is_array($parameter['typeVariants']))
                            <div class="enum">Возможные значения:
                                @foreach ($parameter['typeVariants'] as $variant)
                                    <code class="docutils"><span class="pre">{{ $variant }}</span></code>
                                @endforeach
                            </div>
                        @else
                            <div class="enum">Возможные значения:</div>
                            @php
                                $variants = [];
                                if (isset($methodInfo['enumObjects'][$parameter['typeVariants']]['values'])) {
                                    $variants = $methodInfo['enumObjects'][$parameter['typeVariants']]['values'];
                                } elseif (isset($smd['enumObjects'][$parameter['typeVariants']]['values'])) {
                                    $variants = $smd['enumObjects'][$parameter['typeVariants']]['values'];
                                }
                            @endphp
                                @foreach ($variants as $variant)
                                    <code class="docutils"><span class="pre">{{ $variant['value'] }}</span></code>
                                    {{ !empty($variant['description']) ? '- '.$variant['description'] : '' }}<br>
                                @endforeach
                        @endif
                    @endif
                </div>
            @else
                {{ !empty($parameter['description']) ? '- '.$parameter['description'] : '' }}
            @endif

            @if (!empty($inlineParameters))
                <input type="checkbox" name="param-{{ $name }}" id="param-{{ $name }}" class="show-parameters">
                <label for="param-{{ $name }}" class="caret">
                    <i class="fa fa-caret-right"></i>
                </label>
                @include('jsonrpcdoc::partials.params', ['parameters' => $inlineParameters])
            @endif
        </li>
    @endforeach
</ul>