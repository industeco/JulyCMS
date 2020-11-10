<el-form-item label="{{ $_label ?? 'ID' }}" prop="id" size="small" class="has-helptext">
  <el-input
    v-model="{{ $_model }}.id"
    name="id"
    native-size="60"
    maxlength="32"
    show-word-limit
    {{ $_readOnly ? 'disabled' : '' }}></el-input>
  <span class="jc-form-item-help">
    <i class="el-icon-info"></i> {{ $_readOnly ? '不可修改' : '只能使用小写字母、数字和下划线' }}
  </span>
</el-form-item>
