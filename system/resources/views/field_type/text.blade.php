
{{-- text 类型字段 --}}
<el-form-item prop="{{ $id }}" size="small" class="{{ $helpertext ? 'has-helptext' : '' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $id }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input
    v-model="model.{{ $id }}"
    type="textarea"
    rows="2"></el-input>
  @if ($helpertext)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $helpertext }}</span>
  @endif
</el-form-item>
