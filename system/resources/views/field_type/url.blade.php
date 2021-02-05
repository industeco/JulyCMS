
{{-- url 类型字段 --}}
<el-form-item prop="{{ $id }}" size="small" class="{{ $helpertext ? 'has-helptext' : '' }}"
  :rules="[{!! implode(',', $rules) !!}]">
  <el-tooltip slot="label" content="{{ $id }}" placement="right" effect="dark" popper-class="jc-twig-output">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input v-model="model.{{ $id }}" native-size="100"></el-input>
  @if ($helpertext)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $helpertext }}</span>
  @endif
</el-form-item>

