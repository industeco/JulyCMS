<el-form-item label="{{ $_label ?? '标签' }}" prop="label" size="small" class="{{ isset($_help) ? 'has-helptext' : '' }}">
  <el-input
    name="label"
    v-model="{{ $_model }}.label"
    native-size="60"
    maxlength="64"
    show-word-limit></el-input>
    @if (isset($_help))
    <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $_help }}</span>
    @endif
</el-form-item>
