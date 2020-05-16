<el-form-item prop="{{ $truename }}" size="small" class="{{ $help?'has-helptext':'' }}">
  <el-tooltip slot="label" popper-class="jc-twig-output" effect="dark" content="{{ $truename }}" placement="right">
    <span>{{ $label }}</span>
  </el-tooltip>
  <ckeditor
    v-model="node.{{ $truename }}"
    tag-name="textarea"
    :config="ckeditorConfig"
    @ready="onEditorReady"></ckeditor>
  @if ($help)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $help }}</span>
  @endif
</el-form-item>

{{-- <div class="el-form-item el-form-item--small jc-embeded-field {{ $help?'has-helptext':'' }}">
  <div class="el-form-item__content">
    <div class="jc-embeded-field__header">
      <label class="el-form-item__label">{{ $label }}：</label>
      <div class="jc-embeded-field__buttons">
        <button type="button" title="选择或新建字段"
          class="md-button md-icon-button md-dense md-accent md-theme-default"
          @click="fieldSelectorVisible = true">
          <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">add</i></div></div>
        </button>
      </div>
    </div>
    <ckeditor
      v-model="node.{{ $truename }}"
      tag-name="textarea"
      :config="ckeditorConfig"
      @ready="onEditorReady"></ckeditor>
    @if ($help)
    <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $help }}</span>
    @endif
  </div>
</div> --}}
