@props([
  'scope',
  'model' => 'model',
  'mode' => 'creation',
])

<el-form ref="field_{{ $mode }}_form" :model="{{ $model }}" label-width="108px">

  {{-- 字段类型 --}}
  @if ($mode === 'creation')
  <el-form-item label="字段类型" prop="field_type_id" size="small" class="has-helptext"
    :rules="[{ required:true, message:'『字段类型』不能为空', trigger:'submit' }]">
    <el-select v-model="{{ $model }}.field_type_id" placeholder="--选择字段类型--" @change="handleFieldTypeChange('{{ $scope }}', $event)">
      @foreach (get_field_types() as $type)
      <el-option label="{{ $type['label'] }}" value="{{ $type['id'] }}"></el-option>
      @endforeach
    </el-select>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ '{'.'{ '.$scope.'.fieldTypeHelper }'.'}' }}</span>
  </el-form-item>
  @endif

  {{-- 字段 id --}}
  <x-handle :model="$model" :read-only="$mode==='editing'" :unique-action="short_url('node_fields.exists', '_ID_')" />

  {{-- 字段标签 --}}
  <x-label :model="$model" label="字段标签" />

  {{-- 字段描述 --}}
  <x-description :model="$model" helpertext="在列表或表单中显示" />

  {{-- 是否必填 --}}
  <el-form-item label="必填" size="small">
    <el-switch v-model="{{ $model }}.is_required"></el-switch>
  </el-form-item>

  {{-- 搜索权重 --}}
  <el-form-item label="搜索权重" size="small" class="has-helptext">
    <el-input-number
      v-model="{{ $model }}.search_weight"
      size="small"
      controls-position="right"
      :min="0" :step="1" :precision="0"></el-input-number>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 用于对搜索结果排序，权重越高，搜索结果越靠前；0 表示不允许搜索。</span>
  </el-form-item>

  {{-- 帮助文本 --}}
  <el-form-item label="帮助" prop="helpertext" size="small" class="has-helptext">
    <el-input
      v-model="{{ $model }}.helpertext"
      type="textarea"
      rows="3"></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 在字段下方显示的提示文字，如果为空，则显示『描述』</span>
  </el-form-item>

  {{-- 建议最大字数 --}}
  <el-form-item label="字数" prop="maxlength" size="small" class="has-helptext"
    v-if="{{ $model }}.field_type_id=='text'">
    <el-input-number
      v-model="{{ $model }}.maxlength"
      size="small"
      controls-position="right"
      :min="0"></el-input-number>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 建议的字数限制，主要作为输入提示，不是强制约束。</span>
  </el-form-item>

  {{-- 默认值 --}}
  <el-form-item label="默认值" size="small" class="has-helptext" native-size="100">
    <el-input v-model="{{ $model }}.default_value"></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 字段默认值</span>
  </el-form-item>

  {{-- 预选值 --}}
  <el-form-item label="预选值" size="small" class="has-helptext">
    <el-input v-model="{{ $model }}.options" type="textarea" rows="3"></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 多个值以 "|" 分隔</span>
  </el-form-item>
</el-form>

@once
@push('methods')
  handleFieldTypeChange(scope, type_id) {
    const fieldTypes = @json(get_field_types(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    const type = fieldTypes[type_id];
    this[scope].fieldTypeHelper = type ? type.description : '';
  },
@endpush
@endonce
