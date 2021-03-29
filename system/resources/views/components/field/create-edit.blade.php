@props([
  'scope',
  'model' => 'model',
  'mode' => 'creation',
  'entity',
])

<el-form ref="field_{{ $mode }}_form" :model="{{ $model }}" label-width="108px">

  {{-- 字段类型 --}}
  @if ($mode === 'creation')
  <el-form-item label="字段类型" prop="field_type" size="small" class="has-helptext"
    :rules="[{required:true,message:'『字段类型』不能为空',trigger:'submit'}]">
    <el-select v-model="{{ $model }}.field_type" placeholder="--选择字段类型--">
      <el-option v-for="type in fieldTypes" :label="type['label']" :value="type['class']"></el-option>
    </el-select>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> @{{ fieldTypeHelper }}</span>
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
    <el-switch v-model="{{ $model }}.required"></el-switch>
  </el-form-item>

  {{-- 帮助文本 --}}
  <el-form-item label="帮助" prop="helptext" size="small" class="has-helptext">
    <el-input
      v-model="{{ $model }}.helptext"
      type="textarea"
      rows="3"></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 在字段下方显示的提示文字，如果为空，则显示『描述』</span>
  </el-form-item>

  {{-- 搜索权重 --}}
  <el-form-item label="搜索权重" size="small" class="has-helptext">
    <el-input-number
      v-model="{{ $model }}.weight"
      size="small"
      controls-position="right"
      :min="0" :step="1" :precision="0"></el-input-number>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 用于对搜索结果排序，权重越高，搜索结果越靠前；0 表示不允许搜索。</span>
  </el-form-item>

  {{-- 默认值 --}}
  <el-form-item label="默认值" size="small" class="has-helptext" native-size="100">
    <el-input v-model="{{ $model }}.default" native-size="100"></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 字段默认值</span>
  </el-form-item>

  {{-- 建议最大字数 --}}
  <el-form-item label="字数" size="small" class="has-helptext">
    <el-input-number
      v-model="{{ $model }}.maxlength"
      size="small"
      controls-position="right"
      :min="0"></el-input-number>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 建议的字数限制，主要作为输入提示，不是强制约束。</span>
  </el-form-item>

  {{-- 预选值 --}}
  <el-form-item label="预选值" size="small" class="has-helptext">
    <el-input v-model="{{ $model }}.options" type="textarea" rows="3"></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 多个值以 "|" 分隔</span>
  </el-form-item>

  {{-- 验证规则 --}}
  <el-form-item label="验证" size="small" class="has-helptext">
    <el-input v-model="{{ $model }}.rules" type="textarea" rows="3"></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 多个规则以 "|" 分隔</span>
  </el-form-item>

  {{-- 引用范围 --}}
  <el-form-item v-if="fieldMetakeys.indexOf('reference_scope') >= 0" label="引用范围" size="small"
    :rules="[{required:true,message:'『引用范围』不能为空',trigger:'submit'}]" class="has-helptext">
    @if ($mode == 'creation')
    <el-cascader size="small" placeholder="--选择引用范围--" clearable
      v-model="{{ $model }}.reference_scope"
      :options="referenceScope"
      @hook:created="initReferenceScope"></el-cascader>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 选择实体类型做引用范围</span>
    @else
    <el-cascader v-model="{{ $model }}.reference_scope" size="small" disabled></el-cascader>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 实体引用范围不可更改</span>
    @endif
  </el-form-item>
</el-form>

@once
@push('methods')

@endpush
@endonce
