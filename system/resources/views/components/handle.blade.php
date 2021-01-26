@props([
  'name' => 'id',
  'label' => 'ID',
  'size' => 60,
  'readOnly' => false,
  'helpertext' => '只能使用小写字母、数字和下划线，且不能以数字开头',
  'readOnlyHelper' => '不可修改',
  'model' => 'model',
  'uniqueAction',
])

<el-form-item label="{{ $label }}" prop="{{ $name }}" size="small" class="has-helptext"
  :rules="[
    { required:true, message:'不能为空', trigger:'submit' },
    { pattern:/^[a-z_][a-z0-9_]+$/, message:'格式不正确', trigger:'change' },
    { validator:unique('{{ $uniqueAction }}'), trigger:'blur' },
  ]">
  <el-input
    v-model="{{ $model }}.{{ $name }}"
    name="{{ $name }}"
    native-size="{{ $size }}"
    {{ $readOnly ? 'disabled' : '' }}></el-input>
  <span class="jc-form-item-help">
    <i class="el-icon-info"></i> {{ $readOnly ? $readOnlyHelper : $helpertext }}
  </span>
</el-form-item>
