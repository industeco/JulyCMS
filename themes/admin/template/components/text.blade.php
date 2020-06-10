<el-form-item prop="{{ $truename }}" size="small" class="{{ $helptext?'has-helptext':'' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $truename }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input
    v-model="node.{{ $truename }}"
    @if ($max > 200 || $max === 0)
    type="textarea"
    rows="3"
    @else
    native-size="{{ $max <= 50 ? 60 : 100 }}"
    @endif
    @if ($placeholder ?? null)
    placeholder="{{ $placeholder }}"
    @endif
    @if ($max > 0)
    maxlength="{{ $max }}"
    show-word-limit
    @endif
    ></el-input>
  @if ($helptext)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $helptext }}</span>
  @endif
</el-form-item>
