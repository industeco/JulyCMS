@props([
  'field',
  'model' => 'model',
  'value' => null,
])

{{-- text 类型字段 --}}
<el-form-item prop="{{ $field['id'] }}" size="small" class="{{ $field['helpertext'] ? 'has-helptext' : '' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $field['id'] }}" placement="right">
    <span>{{ $field['label'] }}</span>
  </el-tooltip>
  <el-input
    v-model="{{ $model }}.{{ $field['id'] }}"
    type="textarea"
    rows="2"></el-input>
  @if ($field['helpertext'])
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $field['helpertext'] }}</span>
  @endif
</el-form-item>
