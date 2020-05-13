<el-form-item prop="{{ $truename }}" size="small" class="{{ $help?'has-helptext':'' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $truename }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input
    v-model="node.{{ $truename }}"
    @if ($length <= 100)
    native-size="{{ $length < 50 ? 60 : 100 }}"
    @else
    type="textarea"
    rows="3"
    @endif
    @if ($placeholder ?? null)
    placeholder="{{ $placeholder }}"
    @endif
    maxlength="{{ $length }}"
    show-word-limit></el-input>
  @if ($help)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $help }}</span>
  @endif
</el-form-item>
