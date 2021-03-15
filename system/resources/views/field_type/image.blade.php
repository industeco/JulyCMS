
<el-form-item prop="{{ $id }}" size="small" class="{{ isset($helptext) ? 'has-helptext' : '' }}"
  :rules="[{!! implode(',', $rules) !!}]">
  <el-tooltip slot="label" content="{{ $id }}" placement="right" effect="dark" popper-class="jc-twig-output">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-input v-model="model.{{ $id }}" native-size="100"></el-input>
  @if ($helptext ?? false)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $helptext }}</span>
  @endif
  <button type="button" class="md-button md-raised md-small md-primary md-theme-default"
    @click.stop="showMedias('{{ $id }}')">
    <div class="md-ripple"><div class="md-button-content">浏 览</div></div>
  </button>
</el-form-item>

