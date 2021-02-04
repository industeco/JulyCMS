@props([
  'name' => 'id',
  'label' => 'ID',
  'size' => 60,
  'readOnly' => false,
  'autoReadOnly' => null,
  'helpertext' => '只能使用小写字母、数字和下划线，且不能以数字开头',
  'model' => 'model',
  'uniqueAction',
])

<el-form-item label="{{ $label }}" prop="{{ $name }}" size="small" class="has-helptext"
  @if(!$readOnly)
  :rules="[
    { required:true, message:'不能为空', trigger:'submit' },
    { pattern:/^[a-z_][a-z0-9_]*$/, message:'格式不正确', trigger:'change' },
    { validator:unique('{{ $uniqueAction }}'), trigger:'blur' },
  ]"
  @endif>
  <el-input
    v-model="{{ $model }}.{{ $name }}"
    name="{{ $name }}"
    native-size="{{ $size }}"
    @if ($autoReadOnly)
    :disabled="{{ $autoReadOnly }}"
    @else
    {{ $readOnly ? 'disabled' : '' }}
    @endif></el-input>
  <span class="jc-form-item-help">
    <i class="el-icon-info"></i> {{ $helpertext }}
  </span>
</el-form-item>
