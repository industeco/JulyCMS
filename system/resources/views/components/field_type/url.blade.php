<el-form-item prop="{{ $id }}" size="small" class="{{ isset($parameters['helptext'])?'has-helptext':'' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $id }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input
    v-model="{{ $entityName }}.{{ $id }}"
    native-size="100"
    maxlength="200"
    show-word-limit
    @if (isset($parameters['placeholder']))
    placeholder="{{ $parameters['placeholder'] }}"
    @endif
    ></el-input>
  @if (isset($parameters['helptext']))
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $parameters['helptext'] }}</span>
  @endif
</el-form-item>
