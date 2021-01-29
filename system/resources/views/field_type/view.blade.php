
{{-- input 类型字段 --}}
<el-form-item prop="{{ $id }}" size="small" class="{{ $helpertext ? 'has-helptext' : '' }}">
  <el-tooltip slot="label" content="{{ $id }}" placement="right" effect="dark" popper-class="jc-twig-output">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-select v-model="model.{{ $id }}" filterable allow-create default-first-option native-size="100" style="width:100%">
    @foreach ($views as $view)
    <el-option value="{{ $view }}"></el-option>
    @endforeach
  </el-select>
  @if ($helpertext)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $helpertext }}</span>
  @endif
</el-form-item>
