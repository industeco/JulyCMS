<el-form-item label="{{ $label }}" prop="{{ $truename }}" size="small" class="{{ $help?'has-helptext':'' }}">
  <el-input
    v-model="node.{{ $truename }}"
    native-size="100"
    maxlength="100"
    show-word-limit></el-input>
  @if ($help)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $help }}</span>
  @endif
</el-form-item>
