<el-form ref="field_{{ $mode }}_form"
  :model="{{ $model }}"
  :rules="{{ $model }}Rules"
  label-width="108px">
  @if ($mode == 'create')
  <el-form-item label="字段类型" prop="field_type" size="small" :class="{'has-helptext': !!{{ $model }}.field_type}">
    <el-select v-model="{{ $model }}.field_type" placeholder="--选择字段类型--">
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
  @include('admin::widgets.truename', ['readOnly' => $mode=='edit', 'scope' => $model])
  <el-form-item label="标签" prop="label" size="small">
    <el-input
      v-model="{{ $model }}.label"
      native-size="60"
      maxlength="32"
      show-word-limit></el-input>
  </el-form-item>
  <el-form-item label="必填" prop="required" size="small">
    <el-switch v-model="{{ $model }}.required"></el-switch>
  </el-form-item>
  <el-form-item label="可检索" prop="is_searchable" size="small">
    <el-switch v-model="{{ $model }}.is_searchable"></el-switch>
  </el-form-item>
  <el-form-item v-if="{{ $model }}.is_searchable"
    label="索引权重" prop="is_searchable" size="small">
    <el-input-number
      v-model="{{ $model }}.index_weight"
      size="small"
      controls-position="right"
      :min="1" :step="1" :precision="0"></el-input-number>
  </el-form-item>
  <el-form-item label="帮助" prop="help" size="small">
    <el-input
      v-model="{{ $model }}.help"
      native-size="120"
      maxlength="100"
      show-word-limit></el-input>
  </el-form-item>
  <el-form-item label="描述" prop="description" size="small" class="has-helptext">
    <el-input
      v-model="{{ $model }}.description"
      type="textarea"
      rows="3"
      maxlength="200"
      show-word-limit></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 『描述』仅在字段列表中显示</span>
  </el-form-item>
  @if ($mode == 'create')
  <el-form-item label="最大字数" prop="length" size="small" class="has-helptext"
    v-if="{{ $model }}.field_type=='text'">
    <el-input-number
      v-model="{{ $model }}.length"
      size="small"
      controls-position="right"
      :min="0" :max="255"></el-input-number>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 设定后不能修改。范围：0-255，0 表示不限制。</span>
  </el-form-item>
  <el-form-item v-if="{{ $model }}.field_type=='file'" label="文件类型"
    prop="file_type" size="small" class="has-helptext">
    <el-select
      v-model="{{ $model }}.file_type"
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
  <el-form-item v-if="{{ $model }}.field_type=='text'" label="默认值 [{{ $content_value_langcode }}]" prop="default" size="small" class="has-helptext">
    <el-input
      v-model="{{ $model }}.default"
      type="textarea"
      rows="3"
      maxlength="200"
      show-word-limit></el-input>
    <span class="jc-form-item-help"><i class="el-icon-info"></i> 超过『最大字数』会被截断</span>
  </el-form-item>
  <el-form-item v-if="{{ $model }}.field_type=='text'" label="预选值 [{{ $content_value_langcode }}]" size="small" class="has-helptext">
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
          v-model="{{ $model }}.datalist"
          :animation="150"
          ghost-class="jc-drag-ghost"
          handle=".jc-drag-handle"
          tag="tbody">
          <tr v-for="(line, index) in {{ $model }}.datalist" :key="index">
            <th class="jc-drag-handle">@{{index+1}}</th>
            <td scope="row">
              <input type="text" class="jc-input-intable" v-model="line.value">
            </td>
            <td>
              <div class="jc-operators">
                <button
                  type="button"
                  class="md-button md-icon-button md-primary md-theme-default"
                  @click="addDatalist(index, '{{ $model }}')">
                  <i class="md-icon md-icon-font md-theme-default">add_circle</i>
                </button>
                <button
                  type="button"
                  class="md-button md-icon-button md-accent md-theme-default"
                  :disabled="{{ $model }}.datalist.length < 2"
                  @click="removeDatalist(index, '{{ $model }}')">
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
