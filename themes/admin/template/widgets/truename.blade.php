<el-form-item label="真名" prop="truename" size="small" class="has-helptext">
  @if ($readOnly)
  <el-input
    v-model="{{ $scope }}.truename"
    name="truename"
    native-size="60"
    disabled></el-input>
  <span class="jc-form-item-help"><i class="el-icon-info"></i> 不可修改</span>
  @else
  <el-input
    v-model="{{ $scope }}.truename"
    name="truename"
    native-size="60"
    maxlength="50"
    show-word-limit></el-input>
  <span class="jc-form-item-help"><i class="el-icon-info"></i> 只能使用小写字母、数字和下划线</span>
  @endif
</el-form-item>
