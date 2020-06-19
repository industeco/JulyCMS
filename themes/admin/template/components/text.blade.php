<el-form-item prop="{{ $truename }}" size="small" class="{{ isset($parameters['helptext'])?'has-helptext':'' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $truename }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input
    v-model="node.{{ $truename }}"
    @if ($parameters['maxlength'] > 200 || $parameters['maxlength'] === 0)
    type="textarea"
    rows="3"
    @else
    native-size="{{ $parameters['maxlength'] <= 50 ? 60 : 100 }}"
    @endif
    @if (isset($placeholder))
    placeholder="{{ $placeholder }}"
    @endif
    @if ($parameters['maxlength'] > 0)
    maxlength="{{ $parameters['maxlength'] }}"
    show-word-limit
    @endif
    ></el-input>
  @if (isset($parameters['helptext']))
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $parameters['helptext'] }}</span>
  @endif
</el-form-item>
