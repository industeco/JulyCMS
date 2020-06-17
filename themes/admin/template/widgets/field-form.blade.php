<el-form ref="field_{{ $mode }}_form"
  :model="{{ $formData }}"
  :rules="{{ $formData }}Rules"
  label-width="108px">
  @if ($mode == 'create')
  <el-form-item label="字段类型" prop="field_type" size="small" :class="{'has-helptext': !!{{ $formData }}.parameters.field_type}">
    <el-select v-model="{{ $formData }}.field_type" placeholder="--选择字段类型--">
      <el-option
        v-for="type in fieldTypes"
        :key="type.alias"
        :label="type.label"
        :value="type.alias">
      </el-option>
    </el-select>
    <span class="jc-form-item-help">@{{ fieldTypeHelp }}</span>
  </el-form-item>
  @endif
  @include('admin::widgets.truename', ['readOnly' => $mode=='edit', 'scope' => $formData])
  <el-form-item label="标签" prop="label" size="small">
    <el-input
      v-model="{{ $formData }}.label"
      native-size="60"
      maxlength="32"
      show-word-limit></el-input>
  </el-form-item>
  <el-form-item label="描述" prop="description" size="small" class="has-helptext">
    <el-input
      v-model="{{ $formData }}.description"
      type="textarea"
      rows="3"
      maxlength="255"
      show-word-limit></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 在列表或表单中显示</span>
  </el-form-item>
  <el-form-item label="帮助文本" prop="parameters.helptext" size="small" class="has-helptext">
    <el-input
      v-model="{{ $formData }}.parameters.helptext"
      type="textarea"
      rows="3"
      maxlength="255"
      show-word-limit></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 在表单中显示，如果为空，则显示『描述』</span>
  </el-form-item>
  <el-form-item label="必填" prop="required" size="small">
    <el-switch v-model="{{ $formData }}.required"></el-switch>
  </el-form-item>
  <el-form-item label="可搜索" size="small">
    <el-switch v-model="{{ $formData }}.parameters.is_searchable"></el-switch>
  </el-form-item>
  <el-form-item label="搜索权重" size="small" class="has-helptext"
    v-if="{{ $formData }}.parameters.is_searchable">
    <el-input-number
      v-model="{{ $formData }}.parameters.weight"
      size="small"
      controls-position="right"
      :min="1" :step="1" :precision="0"></el-input-number>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 『搜索权重』用于对搜索结果排序；权重越高，搜索结果越容易靠前</span>
  </el-form-item>
  @if ($mode == 'create')
  <el-form-item label="最大字数" prop="parameters.maxlength" size="small" class="has-helptext"
    v-if="{{ $formData }}.field_type=='text'">
    <el-input-number
      v-model="{{ $formData }}.parameters.maxlength"
      size="small"
      controls-position="right"
      :min="0"
      :max="255"></el-input-number>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 设定后不能修改。范围：0-255，0 表示不限制。</span>
  </el-form-item>
  <el-form-item label="文件类型" prop="parameters.field_type" size="small" class="has-helptext"
    v-if="{{ $formData }}.field_type=='file'">
    <el-select
      v-model="{{ $formData }}.parameters.file_type"
      placeholder="--选择文件类型--">
      <el-option
        v-for="(exts, name) in fileTypes"
        :key="name"
        :label="name"
        :value="name">
      </el-option>
    </el-select>
    <span class="jc-form-item-help">@{{ fileTypeHelp }}</span>
  </el-form-item>
  @endif
  <el-form-item label="默认值" size="small" class="has-helptext"
    v-if="{{ $formData }}.field_type=='text'">
    <el-input
      v-model="{{ $formData }}.parameters.default"
      type="textarea"
      rows="3"
      :maxlength="{{ $formData }}.parameters.maxlength"
      show-word-limit></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 超过『最大字数』会被截断</span>
  </el-form-item>
  <el-form-item label="预选值" size="small" class="has-helptext"
    v-if="{{ $formData }}.field_type=='text'">
    <div class="jc-table-wrapper">
      <table class="jc-table jc-dense is-editable with-operators with-line-number">
        <colgroup>
          <col width="80px">
          <col width="auto">
          <col width="120px">
        </colgroup>
        <thead>
          <tr>
            <th>行号</th>
            <th><label>预设值</label></th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody
          is="draggable"
          v-model="{{ $formData }}.parameters.datalist"
          :animation="150"
          ghost-class="jc-drag-ghost"
          handle=".jc-drag-handle"
          tag="tbody">
          <tr v-for="(item, index) in {{ $formData }}.parameters.datalist" :key="index">
            <th class="jc-drag-handle">@{{index+1}}</th>
            <td scope="row">
              <input type="text" class="jc-input-intable" v-model="item.value">
            </td>
            <td>
              <div class="jc-operators">
                <button
                  type="button"
                  class="md-button md-icon-button md-primary md-theme-default"
                  @click="addDatalist(index, '{{ $formData }}')">
                  <i class="md-icon md-icon-font md-theme-default">add_circle</i>
                </button>
                <button
                  type="button"
                  class="md-button md-icon-button md-accent md-theme-default"
                  :disabled="{{ $formData }}.parameters.datalist.length < 2"
                  @click="removeDatalist(index, '{{ $formData }}')">
                  <i class="md-icon md-icon-font md-theme-default">remove_circle</i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 超过『最大字数』会被截断</span>
  </el-form-item>
</el-form>
