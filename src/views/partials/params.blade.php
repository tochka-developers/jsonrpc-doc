<ul class="simple">
    @foreach ($parameters as $parameter)
        <li>
            @php
                $type = isset($parameter['type']) ? $parameter['type'] : 'mixed';
                $additionalType = isset($parameter['typeAdditional']) ? $parameter['typeAdditional'] : null;
                $name = uniqid($parameter['name']);

                if (!empty($parameter['parameters'])) {
                    $inlineParameters = $parameter['parameters'];
                } elseif (isset($methodInfo['objects'][$additionalType]['parameters'])) {
                    $inlineParameters = $methodInfo['objects'][$additionalType]['parameters'];
                    $type = 'object';
                } elseif (isset($smd['objects'][$additionalType]['parameters'])) {
                    $inlineParameters = $smd['objects'][$additionalType]['parameters'];
                    $type = 'object';
                } else {
                    $inlineParameters = [];
                }

                if ($additionalType === 'enum' && !empty($parameter['typeVariants']) && !is_array($parameter['typeVariants'])) {
                    if (isset($methodInfo['enumObjects'][$parameter['typeVariants']]['type'])) {
                        $type = $methodInfo['enumObjects'][$parameter['typeVariants']]['type'];
                    } elseif (isset($smd['enumObjects'][$parameter['typeVariants']]['type'])) {
                        $type = $smd['enumObjects'][$parameter['typeVariants']]['type'];
                    }
                }
            @endphp

            <code class="docutils {{ empty($parameter['optional']) ? 'required' : 'literal' }}">{{ $parameter['name'] }}</code>
            <span class="guilabel">{{ $type }}{{ !empty($parameter['array']) ? '[]' : '' }}</span>

            @if (isset($parameter['default']) || !empty($parameter['optional']) || $additionalType === 'enum' || ($additionalType === 'date' && !empty($parameter['typeFormat'])))
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
                                        class="pre">{{ var_export($parameter['default'], true) }}</span></code>
                        </div>
                    @endif
                    @if ($additionalType === 'date' && !empty($parameter['typeFormat']))
                        <div class="enum">
                            Формат даты: <code class="docutils"><span class="pre">{{ $parameter['typeFormat'] }}</span></code>
                        </div>
                    @endif
                    @if ($additionalType === 'enum')
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