@props([
  'name' => 'description',
  'label' => '描述',
  'rows' => 5,
  'helpertext' => '描述文字',
  'model' => 'model',
])

<el-form-item label="{{ $label }}" prop="{{ $name }}" size="small" class="has-helptext">
  <el-input type="textarea"
    v-model="{{ $model }}.{{ $name }}"
    name="{{ $name }}"
    rows="{{ $rows }}"></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $helpertext }}</span>
</el-form-item>
