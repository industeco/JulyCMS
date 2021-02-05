<el-form-item label="{{ $_label ?? '描述' }}" prop="description" size="small" class="{{ isset($_help) ? 'has-helptext' : '' }}">
  <el-input
    name="description"
    v-model="{{ $_model }}.description"
    type="textarea"
    rows="{{ $_rows ?? 5 }}"
    maxlength="255"
    show-word-limit></el-input>
    @if (isset($_help))
    <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $_help }}</span>
    @endif
</el-form-item>
