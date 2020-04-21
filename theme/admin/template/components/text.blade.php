<el-form-item label="{{ $label }}" prop="{{ $truename }}" size="small" class="{{ $help?'has-helptext':'' }}">
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
