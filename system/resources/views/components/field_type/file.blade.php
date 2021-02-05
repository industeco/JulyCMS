@props([
  'field',
  'model' => 'model',
  'value' => null,
])

<el-form-item prop="{{ $field['id'] }}" size="small" class="{{ $field['helpertext'] ? 'has-helptext' : '' }}"
  :rules="[]">
  <el-tooltip slot="label" content="{{ $field['id'] }}" placement="right" effect="dark" popper-class="jc-twig-output">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input v-model="{{ $model }}.{{ $field['id'] }}" native-size="100"></el-input>
  @if ($field['helpertext'])
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $field['helpertext'] }}</span>
  @endif
  <button type="button" class="md-button md-raised md-small md-primary md-theme-default"
    @click.stop="showMedias('{{ $field['id'] }}')">
    <div class="md-ripple">
      <div class="md-button-content">浏 览</div>
    </div>
  </button>
</el-form-item>

