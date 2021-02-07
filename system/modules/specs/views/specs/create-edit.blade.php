@extends('layout')

@section('h1')
  {{ $spec['id'] ? '编辑规格类型' : '新建规格类型' }}
@endsection

@section('main_content')
  <el-form id="main_form" ref="main_form"
    :model="spec"
    :rules="specRules"
    label-position="top">
    <div id="main_form_left">
      @include('widgets.id', ['_model' => 'spec', '_readOnly' => (bool) $spec['id']])
      @include('widgets.label', ['_model' => 'spec', '_label'=>'名称'])
      @include('widgets.description', ['_model' => 'spec'])
      <div class="el-form-item el-form-item--small has-helptext jc-embeded-field">
        <div class="el-form-item__content">
          <div class="jc-embeded-field__header">
            <label class="el-form-item__label">规格字段：</label>
            <div class="jc-embeded-field__buttons">
              <button type="button" title="新建字段"
                class="md-button md-icon-button md-dense md-accent md-theme-default"
                @click.stop="createField()">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">add</i></div></div>
              </button>
            </div>
          </div>
          <div class="jc-table-wrapper">
            <table class="jc-table jc-dense is-draggable with-drag-handle with-operators">
              <colgroup>
                <col width="50px">
                <col width="120px">
                <col width="120px">
                <col width="auto">
                <col width="100px">
                <col width="100px">
                <col width="100px">
                <col width="100px">
              </colgroup>
              <thead>
                <tr>
                  <th></th>
                  <th>ID</th>
                  <th>标题</th>
                  <th>描述</th>
                  <th>可分组</th>
                  <th>可搜索</th>
                  <th>类型</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody
                is="draggable"
                v-model="fields"
                :animation="150"
                ghost-class="jc-drag-ghost"
                handle=".jc-drag-handle"
                tag="tbody">
                <tr v-for="field in fields" :key="field.field_id">
                  <td><i class="md-icon md-icon-font md-theme-default jc-drag-handle">swap_vert</i></td>
                  <td><span>@{{ field.field_id }}</span></td>
                  <td><span>@{{ field.label }}</span></td>
                  <td><span>@{{ field.description }}</span></td>
                  <td><el-switch v-model="field['is_groupable']"></td>
                  <td><el-switch v-model="field['is_searchable']"></td>
                  <td><span>@{{ getFieldTypeLabel(field.field_type_id) }}</span></td>
                  <td>
                    <div class="jc-operators">
                      <button
                        type="button"
                        class="md-button md-icon-button md-primary md-theme-default"
                        title="编辑"
                        @click.stop="editField(field)">
                        <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_edit_circle"></use></svg>
                      </button>
                      <button
                        type="button"
                        class="md-button md-icon-button md-accent md-theme-default"
                        title="删除"
                        @click.stop="removeField(field)">
                        <i class="md-icon md-icon-font md-theme-default">remove_circle</i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <span class="jc-form-item-help"><i class="el-icon-info"></i> 新建、排序字段</span>
        </div>
      </div>
      <div id="main_form_bottom" class="is-button-item">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="submitMainForm">
          <div class="md-button-content">保存</div>
        </button>
      </div>
    </div>
  </el-form>
  <el-dialog
    id="field_editor"
    title="新建/编辑规格字段"
    top="-5vh"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    :visible.sync="fieldEditorVisible">
    <el-form ref="field_creat_edit_form"
      :model="currentField"
      :rules="fieldRules"
      label-width="108px">
      <el-form-item label="ID" prop="field_id" size="small" class="has-helptext">
        <el-input
          v-model="currentField.field_id"
          name="field_id"
          native-size="60"
          maxlength="32"
          show-word-limit
          :disabled="!isEditable"></el-input>
        <span class="jc-form-item-help"><i class="el-icon-info"></i> 只能使用小写字母、数字和下划线</span>
      </el-form-item>
      @include('widgets.label', ['_model'=> 'currentField'])
      @include('widgets.description', ['_model'=> 'currentField', '_rows'=>3])
      <el-form-item label="字段类型" prop="field_type_id" size="small" class="has-helptext">
        <el-select v-model="currentField.field_type_id" placeholder="--选择字段类型--" :disabled="!isEditable || fieldEditing">
          <el-option
            v-for="type in fieldTypes"
            :key="type.id"
            :label="type.label"
            :value="type.id">
          </el-option>
        </el-select>
        <span class="jc-form-item-help">@{{ fieldTypeHelp }}</span>
      </el-form-item>
      <el-form-item label="小数位" prop="places" size="small" class="has-helptext"
        v-if="currentField.field_type_id=='float'">
        <el-input-number
          v-model="currentField.places"
          size="small"
          controls-position="right"
          :min="0"
          :max="6"
          :disabled="!isEditable"></el-input-number>
        <span class="jc-form-item-help"><i class="el-icon-info"></i> 范围：0-6；0 表示不限制</span>
      </el-form-item>
      <el-form-item label="默认值" size="small" class="has-helptext">
        <el-input
          v-model="currentField.default"
          :maxlength="255"
          native-size="100"
          show-word-limit></el-input>
        <span class="jc-form-item-help"><i class="el-icon-info"></i> 默认值</span>
      </el-form-item>
      <el-form-item label="可分组" size="small">
        <el-switch v-model="currentField.is_groupable"></el-switch>
      </el-form-item>
      <el-form-item label="可检索" size="small">
        <el-switch v-model="currentField.is_searchable"></el-switch>
      </el-form-item>
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click.stop="fieldEditorVisible = false">取 消</el-button>
      <el-button size="small" type="primary" @click.stop="handleFieldEditorConfirm">确 定</el-button>
    </span>
  </el-dialog>
@endsection

@section('script')
<script>
  function clone(obj) {
    return JSON.parse(JSON.stringify(obj))
  }

  let app = new Vue({
    el: '#main_content',
    data() {
      return {
        spec: @jjson($spec),
        fields: @jjson($fields),
        fieldTypes: @jjson($fieldTypes),

        currentField: @jjson($emptyField),

        specRules: {
          @if (!$spec['id'])
          "id": [
            { required: true, message: '『ID』不能为空', trigger: 'submit' },
            { max: 32, message: '最多 32 个字符', trigger: 'change' },
            { pattern: /^[a-z0-9_]+$/, message: '『ID』只能包含小写字母、数字和下划线', trigger: 'change' },
            {
              validator: (rule, value, callback) => {
                if (!value || !value.length) {
                  callback();
                } else {
                  const action = "{{ short_url('manage.specs.exists', '_ID_') }}".replace('_ID_', value);
                  axios.get(action).then(function(response) {
                    if (response.data.exists) {
                      callback(new Error('类型 id 已存在'));
                    } else {
                      callback();
                    }
                  }).catch(function(error) {
                    console.error(error);
                  });
                }
              },
              trigger: 'blur',
            }
          ],
          @endif
          "label": [
            { required: true, message: '『标题』不能为空', trigger: 'submit' },
            { max: 64, message: '最多 64 个字符', trigger: 'change' }
          ],
          "description": [
            { max: 255, message: '最多 255 个字符', trigger: 'change' }
          ],
        },

        fieldRules: {
          "field_type_id": [
            { required: true, message: '『字段类型』不能为空', trigger: 'submit' },
          ],
          "field_id": [
            { required: true, message: '『ID』不能为空', trigger: 'submit' },
            { max: 32, message: '『ID』最多 32 个字符', trigger: 'change' },
            { pattern: /^[a-z_][a-z0-9_]*$/, message: '『ID』只能包含小写字母、数字和下划线，且必须以字母或下划线开头', trigger: 'change' },
            {
              validator: (rule, value, callback) => {
                if (value && value.length) {
                  if (['id', 'updated_at', 'created_at'].indexOf(value) >= 0) {
                    callback(new Error('字段 id 已存在'));
                    return;
                  }
                  for (let i = 0, len = this.fields.length; i < len; i++) {
                    const field = this.fields[i];
                    if (field.field_id === value && field !== this.currentField) {
                      callback(new Error('字段 id 已存在'));
                      return;
                    }
                  }
                }
                callback();
              },
              trigger: 'blur',
            },
          ],
          "label": [
            { required: true, message: '『标签』不能为空', trigger: 'submit' },
          ],
        },

        fieldEditorVisible: false,
        fieldEditing: true,
      }
    },

    computed: {
      fieldTypeHelp() {
        let current_type = this.currentField.field_type_id;
        if (current_type && this.fieldTypes[current_type]) {
          return this.fieldTypes[current_type].description;
        }
        return '';
      },

      isEditable() {
        return !this.currentField.id;
      },
    },

    created: function() {
      this.fields.forEach(field => {
        field.old_field_id = field.field_id;
      });
      this.currentField.old_field_id = null;

      this.initial_spec = JSON.stringify(this.spec);
      this.initial_fields = JSON.stringify(this.fields);
    },

    methods: {
      getFieldTypeLabel(fieldTypeId) {
        if (fieldTypeId && this.fieldTypes[fieldTypeId]) {
          return this.fieldTypes[fieldTypeId].label;
        }
        return '';
      },

      removeField(field) {
        for (let i = 0, len = this.fields.length; i < len; i++) {
          if (this.fields[i].field_id === field.field_id) {
            this.fields.splice(i, 1);
            return;
          }
        }
      },

      createField() {
        Vue.set(this.$data, 'currentField', @jjson($emptyField));
        this.currentField.old_field_id = null;

        this.fieldEditing = false;
        this.fieldEditorVisible = true;
      },

      editField(field) {
        Vue.set(this.$data, 'currentField', field);
        this.fieldEditing = true;
        this.fieldEditorVisible = true;
      },

      handleFieldEditorConfirm() {
        const form = this.$refs.field_creat_edit_form;
        form.validate((valid) => {
          if (valid) {
            app.fieldEditorVisible = false;
            if (!app.fieldEditing) {
              app.fields.push(app.currentField);
            }
          }
        });
      },

      submitMainForm() {
        let form = this.$refs.main_form;

        const loading = app.$loading({
          lock: true,
          text: '{{ $spec['id'] ? "正在保存修改 ..." : "正在新建规格 ..." }}',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        form.validate().then(function() {

          @if ($spec['id'])
          if (app.initial_spec === JSON.stringify(app.spec) && app.initial_fields === JSON.stringify(app.fields)) {
            window.location.href = "{{ short_url('manage.specs.index') }}";
            return;
          }
          @endif

          const spec = clone(app.spec);

          let delta = 0;
          spec.fields = clone(app.fields).map(field => {
            field.spec_id = spec.id;
            field.delta = delta++;
            if (field.options.length) {
              field.options = field.options
                .map(option => option.value)
                .filter(option => option != null && option.length > 0);
            }
            return field;
          });

          @if ($spec['id'])
          const action = "{{ short_url('manage.specs.update', $spec['id']) }}";
          @else
          const action = "{{ short_url('manage.specs.store') }}";
          @endif

          axios.{{ $spec['id'] ? 'put' : 'post' }}(action, spec).then(function(response) {
            window.location.href = "{{ short_url('manage.specs.index') }}";
          }).catch(function(error) {
            loading.close();
            console.error(error);
            app.$message.error('发生错误，可查看控制台');
          });
        }).catch(function(error) {
          loading.close();
          // console.error(error);
        })
      },
    }
  })
</script>
@endsection
