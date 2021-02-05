@props([
  'field',
  'model' => 'model',
  'value' => null,
])

{{-- url 类型字段 --}}
<el-form-item prop="{{ $field['id'] }}" size="small" class="{{ $field['helpertext'] ? 'has-helptext' : '' }}"
  :rules="[{type:'url', message:'网址格式不正确', trigger:'blur'}]">
  <el-tooltip slot="label" content="{{ $field['id'] }}" placement="right" effect="dark" popper-class="jc-twig-output">
    <span>{{ $field['label'] }}</span>
  </el-tooltip>
  <el-input
    v-model="{{ $model }}.{{ $field['id'] }}"
    nativ-size="100"
    placeholder="https://www.example.com"></el-input>
  @if ($field['helpertext'])
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $field['helpertext'] }}</span>
  @endif
</el-form-item>

