<el-form-item prop="{{ $id }}" size="small" class="{{ \Arr::get($parameters, 'helptext')?'has-helptext':'' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $id }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <ckeditor
    ref="ckeditor_{{ $id }}"
    v-model="node.{{ $id }}"
    tag-name="textarea"
    :config="{filebrowserImageBrowseUrl: '{{ short_url('media.select') }}'}"></ckeditor>
  @if (\Arr::get($parameters, 'helptext'))
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $parameters['helptext'] }}</span>
  @endif
</el-form-item>
