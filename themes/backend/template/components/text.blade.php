<el-form-item prop="{{ $id }}" size="small" class="{{ \Arr::get($parameters, 'helptext')?'has-helptext':'' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $id }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input
    v-model="node.{{ $id }}"
    @if ($parameters['maxlength'] > 200 || $parameters['maxlength'] === 0)
    type="textarea"
    rows="2"
    @else
    native-size="{{ $parameters['maxlength'] <= 50 ? 60 : 100 }}"
    @endif
    @if (\Arr::get($parameters, 'placeholder'))
    placeholder="{{ $parameters['placeholder'] }}"
    @endif
    @if ($parameters['maxlength'] > 0)
    maxlength="{{ $parameters['maxlength'] }}"
    show-word-limit
    @endif
    ></el-input>
  @if (\Arr::get($parameters, 'helptext'))
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $parameters['helptext'] }}</span>
  @endif
</el-form-item>
