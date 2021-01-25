@props([
  'name' => 'label',
  'label' => '标签',
  'size' => 60,
  'readOnly' => false,
  'helpertext' => '标签文字',
  'model' => 'model',
])

<el-form-item label="{{ $label }}" prop="{{ $name }}" size="small" class="has-helptext">
  <el-input
    v-model="{{ $model }}.{{ $name }}"
    name="{{ $name }}"
    native-size="{{ $size }}"></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $helpertext }}</span>
</el-form-item>
