@props([
  'field',
  'model' => 'model',
  'value' => null,
])

{{-- html 类型字段 --}}
<el-form-item prop="{{ $field['id'] }}" size="small" class="{{ $field['helpertext'] ? 'has-helptext' : '' }}">
  <el-tooltip slot="label" content="{{ $field['id'] }}" placement="right" effect="dark" popper-class="jc-twig-output">
    <span>{{ $label }}</span>
  </el-tooltip>
  <ckeditor
    ref="ckeditor_{{ $field['id'] }}"
    v-model="{{ $model }}.{{ $field['id'] }}"
    tag-name="textarea"
    :config="{filebrowserImageBrowseUrl: '{{ short_url('media.select') }}'}"></ckeditor>
  @if ($field['helpertext'])
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $field['helpertext'] }}</span>
  @endif
</el-form-item>
