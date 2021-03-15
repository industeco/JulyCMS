
{{-- text 类型字段 --}}
<el-form-item prop="{{ $id }}" size="small" class="{{ isset($helptext) ? 'has-helptext' : '' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $id }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input
    v-model="model.{{ $id }}"
    type="textarea"
    rows="2"></el-input>
  @if ($helptext ?? false)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $helptext }}</span>
  @endif
</el-form-item>
