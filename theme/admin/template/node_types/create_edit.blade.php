@extends('admin::layout')

@section('h1')
  {{ $truename?'编辑内容类型':'新建内容类型' }} <span id="content_locale">[ {{ langname($interface_value_langcode) }} ]</span>
@endsection

@section('main_content')
  <el-form id="main_form" ref="main_form"
    :model="nodeType"
    :rules="nodeTypeRules"
    label-position="top">
    <div id="main_form_left">
      @include('admin::widgets.truename', ['readOnly' => $truename, 'scope' => 'nodeType'])
      <el-form-item label="名称" prop="name" size="small">
        <el-input
          v-model="nodeType.name"
          native-size="60"
          maxlength="32"
          show-word-limit></el-input>
      </el-form-item>
      <el-form-item label="描述" prop="description" size="small">
        <el-input
          type="textarea"
          rows="5"
          v-model="nodeType.description"
          maxlength="200"
          show-word-limit></el-input>
      </el-form-item>
      <div class="el-form-item el-form-item--small has-helptext jc-embeded-field">
        <div class="el-form-item__content">
          <div class="jc-embeded-field__header">
            <label class="el-form-item__label">已选字段：</label>
            <div class="jc-embeded-field__buttons">
              <button type="button" title="选择或新建字段"
                class="md-button md-icon-button md-dense md-accent md-theme-default"
                @click="fieldSelectorVisible = true">
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
                <col width="120px">
              </colgroup>
              <thead>
                <tr>
                  <th></th>
                  <th>真名</th>
                  <th>标签</th>
                  <th>描述</th>
                  <th>类型</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td></td>
                  <td><span>@{{ nodeType.field__title.truename }}</span></td>
                  <td><span :class="{'jc-label':true,'is-required':nodeType.field__title.required}">@{{ nodeType.field__title.label }}</span></td>
                  <td><span>@{{ nodeType.field__title.description }}</span></td>
                  <td><span>@{{ nodeType.field__title.field_type }}</span></td>
                  <td>
                    <div class="jc-operators">
                      <button type="button" title="编辑" class="md-button md-icon-button md-primary md-theme-default"
                        @click="editField(nodeType.field__title)">
                        <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_edit_circle"></use></svg>
                        {{-- <i class="md-icon md-icon-font md-theme-default">edit</i> --}}
                      </button>
                      <button type="button" title="删除" disabled class="md-button md-icon-button md-accent md-theme-default">
                        <i class="md-icon md-icon-font md-theme-default">remove_circle</i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tbody
                is="draggable"
                v-model="nodeType.fields"
                :animation="150"
                ghost-class="jc-drag-ghost"
                handle=".jc-drag-handle"
                tag="tbody">
                <tr v-for="field in nodeType.fields" :key="field.truename">
                  <td><i class="md-icon md-icon-font md-theme-default jc-drag-handle">swap_vert</i></td>
                  <td><span>@{{ field.truename }}</span></td>
                  <td><span :class="{'jc-label':true,'is-required':field.required}">@{{ field.label }}</span></td>
                  <td><span>@{{ field.description }}</span></td>
                  <td><span>@{{ field.field_type }}</span></td>
                  <td>
                    <div class="jc-operators">
                      <button
                        type="button"
                        class="md-button md-icon-button md-primary md-theme-default"
                        title="编辑"
                        @click="editField(field)">
                        <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_edit_circle"></use></svg>
                        {{-- <i class="md-icon md-icon-font md-theme-default">edit</i> --}}
                      </button>
                      <button
                        type="button"
                        class="md-button md-icon-button md-accent md-theme-default"
                        title="删除"
                        @click="removeField(field)">
                        <i class="md-icon md-icon-font md-theme-default">remove_circle</i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <span class="jc-form-item-help"><i class="el-icon-info"></i> 选择、排序字段</span>
        </div>
      </div>
      <div id="main_form_bottom" class="is-button-item">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click="submitMainForm">
          <div class="md-button-content">保存</div>
        </button>
      </div>
    </div>
    <div id="main_form_right">
      <h2 class="jc-form-info-item">通用非必填项</h2>
    </div>
  </el-form>
  <el-dialog
    id="field_selector"
    top="-5vh"
    :show-close="false"
    :visible.sync="fieldSelectorVisible"
    @open="syncTypeFieldsToSelected">
    <el-tabs v-model="currentTab" type="card" class="jc-tabs-mini">
      <el-tab-pane label="选择字段" name="select" class="md-scrollbar md-theme-default">
        <el-table
          ref="fields_table"
          :data="nodeFields"
          style="width: 100%;"
          class="jc-table jc-dense"
          @selection-change="handleSelectionChange"
          @hook:mounted="syncTypeFieldsToSelected">
          <el-table-column
            type="selection"
            width="50">
          </el-table-column>
          <el-table-column
            prop="label"
            label="标签"
            width="160"
            sortable>
          </el-table-column>
          <el-table-column
            prop="truename"
            label="真名"
            width="180"
            sortable>
          </el-table-column>
          <el-table-column
            prop="description"
            label="描述">
          </el-table-column>
          <el-table-column
            prop="field_type"
            label="类型"
            width="160"
            sortable>
          </el-table-column>
        </el-table>
      </el-tab-pane>
      <el-tab-pane label="新建字段" name="create" class="md-scrollbar md-theme-default">
        @include('admin::widgets.field-form', ['mode' => 'create', 'model' => 'nodeField'])
      </el-tab-pane>
    </el-tabs>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click="fieldSelectorVisible = false">取 消</el-button>
      <el-button size="small" type="primary" @click="handleFieldSelectorConfirm">确 定</el-button>
    </span>
  </el-dialog>
  <el-dialog
    id="field_editor"
    title="编辑字段"
    top="-5vh"
    :visible.sync="fieldEditorVisible">
    @include('admin::widgets.field-form', ['mode' => 'edit', 'model' => 'editingField'])
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click="fieldEditorVisible = false">取 消</el-button>
      <el-button size="small" type="primary" @click="handleFieldEditorConfirm">确 定</el-button>
    </span>
  </el-dialog>
@endsection

@section('script')
<script>
  function clone(obj) {
    return JSON.parse(JSON.stringify(obj))
  }

  let mode = "{{ $truename ? 'edit' : 'create' }}";

  let currentFields = @json(array_values($fields), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  let titleField = {
    truename: 'title',
    field_type: 'text',
    is_preset: true,
    langcode: 'zh',
    label: '标题',
    description: '内容类型预设字段，不可删除，不可排序',
  };
  for (let i = 0, len = currentFields.length; i < len; i++) {
    if (currentFields[i].truename == 'title') {
      titleField = currentFields[i];
      currentFields.splice(i, 1);
      break;
    }
  }

  let app = new Vue({
    el: '#main_content',
    data() {
      var isUniqueType = function(rule, value, callback) {
        if (!value || !value.length) {
          callback();
        } else {
          axios.get('/admin/checkunique/node_types/'+value).then(function(response) {
            if (response.data.exists) {
              callback(new Error('『真名』已存在'));
            } else {
              callback();
            }
          }).catch(function(error) {
            console.error(error);
          });
        }
      };

      var isUniqueField = function(rule, value, callback) {
        if (!value || !value.length) {
          callback();
        } else {
          axios.get('/admin/checkunique/node_fields/'+value).then(function(response) {
            if (response.data.exists) {
              callback(new Error('『真名』已存在'));
            } else {
              callback();
            }
          }).catch(function(error) {
            console.error(error);
          })
        }
      };

      return {
        nodeType: {
          interface_value_langcode: '{{ $interface_value_langcode }}',
          content_value_langcode: '{{ $content_value_langcode }}',
          truename: '{{ $truename }}',
          name: '{{ $name }}',
          description: '{{ $description }}',
          field__title: titleField,
          fields: currentFields,
        },

        nodeTypeRules: {
          @if (!$truename)
          truename: [
            { required: true, message: '『真名』不能为空', trigger: 'submit' },
            { max: 32, message: '最多 32 个字符', trigger: 'change' },
            { pattern: /^[a-z0-9_]+$/, message: '真名只能包含小写字母、数字和下划线', trigger: 'change' },
            { validator: isUniqueType, trigger: 'blur' }
          ],
          @endif
          name: [
            { required: true, message: '『名称』不能为空', trigger: 'submit' },
            { max: 32, message: '最多 32 个字符（或 16 个汉字）', trigger: 'change' }
          ],
          description: [
            { max: 200, message: '最多 200 个字符（或 100 个汉字）', trigger: 'change' }
          ],
        },

        currentField: null,
        editingField: {},

        editingFieldRules: {
          label: [
            { required: true, message: '『标签』不能为空', trigger: 'submit' },
          ],
        },

        selectedFields: [],

        nodeField: {
          content_value_langcode: '{{ $content_value_langcode }}',
          interface_value_langcode: '{{ $interface_value_langcode }}',
          field_type: null,
          truename: '',
          length: 0,
          required: false,
          is_searchable: true,
          index_weight: 1,
          file_type: null,
          label: '',
          description: '',
          help: '',
          default: null,
          datalist: [{value:''}],
        },

        nodeFieldRules: {
          field_type: [
            { required: true, message: '『字段类型』不能为空', trigger: 'submit' },
          ],
          truename: [
            { required: true, message: '『真名』不能为空', trigger: 'submit' },
            { max: 32, message: '最多 32 个字符', trigger: 'change' },
            { pattern: /^[a-z0-9_]+$/, message: '真名只能包含小写字母、数字和下划线', trigger: 'change' },
            { validator: isUniqueField, trigger: 'blur' },
          ],
          label: [
            { required: true, message: '『标签』不能为空', trigger: 'submit' },
          ],
          file_type: [
            { required: true, message: '『文件类型』不能为空', trigger: 'submit' },
          ],
        },

        nodeFields: @json(array_values($availableFields), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),

        fieldSelectorVisible: false,
        fieldEditorVisible: false,
        currentTab: 'select',

        fieldTypes: @json(\App\FieldTypes\FieldType::getTypes(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),

        fileTypes: @json(config('jc.rules.file_type'), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
      }
    },

    computed: {
      fieldTypeHelp() {
        let current_type = this.nodeField.field_type;
        if (current_type && this.fieldTypes[current_type]) {
          return this.fieldTypes[current_type].description
        }
        return ''
      },
      fileTypeHelp() {
        let file_type = this.nodeField.file_type;
        if (file_type && this.fileTypes[file_type]) {
          return '允许的扩展名：' + this.fileTypes[file_type].join(', ')
        }
        return ''
      },
    },

    created: function() {
      this.initial_data = JSON.stringify(this.nodeType)

      function transformDatalist(field) {
        if (field.field_type == 'text') {
          if (field.datalist && field.datalist.length) {
            field.datalist = field.datalist.map(item => {value:item})
          } else {
            field.datalist = [{value:''}]
          }
        }
        return field
      }
      this.nodeType.fields = this.nodeType.fields.map(transformDatalist)
      this.nodeFields = this.nodeFields.map(transformDatalist)
    },

    methods: {
      removeField(field) {
        let fields = this.nodeType.fields;
        for (let i = 0, len = fields.length; i < len; i++) {
          if (fields[i].truename === field.truename) {
            fields.splice(i, 1)
            return
          }
        }
      },

      editField(field) {
        this.currentField = field;
        // let data = Object.assign({}, field);
        Vue.set(this.$data, 'editingField', clone(field))
        this.fieldEditorVisible = true
      },

      syncTypeFieldsToSelected() {
        this.selectedFields = this.nodeType.fields.slice();
        // console.log(this.selectedFields.slice())

        let selected = this.nodeType.fields.map(field => field.truename);
        // console.log(selected.slice())

        let table = this.$refs.fields_table;
        if (table) {
          // table.clearSelection()
          this.nodeFields.forEach(row => {
            table.toggleRowSelection(row, selected.indexOf(row.truename) >= 0)
          })
        }
      },

      handleSelectionChange(selected) {
        this.selectedFields = selected;
      },

      // 当按下对话框确定按钮时
      handleFieldSelectorConfirm() {
        let form = this.$refs.field_create_form;

        if (this.currentTab == 'create') {
          form.validate().then(function() {

            const loading = app.$loading({
              lock: true,
              text: '正在新建字段 ...',
              background: 'rgba(0, 0, 0, 0.7)',
            });

            let field = clone(app.nodeField);
            field.datalist = field.datalist.map(item => item.value);

            axios.post('/admin/node_fields', field).then(function(response) {
              // console.log(response)
              app.nodeType.fields.push(clone(app.nodeField))
              app.nodeFields.push(clone(app.nodeField))
              form.resetFields()
              loading.close()
              app.fieldSelectorVisible = false
            }).catch(function(error) {
              loading.close()
              console.error(error)
            })
          }).catch(function(error) {
            //...
          })
        } else {
          this.$set(this.nodeType, 'fields', clone(this.selectedFields))
          form.resetFields()
          this.fieldSelectorVisible = false
        }
      },

      handleFieldEditorConfirm() {
        let form = this.$refs.field_edit_form;
        form.validate((valid) => {
          if (valid) {
            Object.assign(this.currentField, this.editingField)
            this.fieldEditorVisible = false
          }
        })
      },

      addDatalist(index, field) {
        this.$data[field].datalist.splice(index + 1, 0, {value:''})
      },

      removeDatalist(index, field) {
        if (this.$data[field].datalist.length > 1) {
          this.$data[field].datalist.splice(index, 1)
        }
      },

      submitMainForm() {
        let form = this.$refs.main_form;

        const loading = app.$loading({
          lock: true,
          text: mode=='create'?'正在新建类型 ...':'正在保存修改 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        form.validate().then(function() {

          let nodeType = clone(app.nodeType);
          nodeType.fields.unshift(nodeType.field__title);
          delete nodeType.field__title;
          nodeType.fields = nodeType.fields.map(function(field) {
            if (field.field_type == 'text' && field.datalist) {
              const datalist = field.datalist;
              field.datalist = [];
              for (let i = 0, len = field.datalist.length; i < len; i++) {
                if (datalist[i].value) {
                  field.datalist.push(datalist[i].value)
                }
              }
            }
            return field
          });

          @if($truename)
            if (app.initial_data === JSON.stringify(nodeType)) {
              window.location.href = "/admin/node_types";
              return
            }
          @endif

          console.log(nodeType);

          const method = '{{ $truename ? "put" : "post" }}';
          const action = '/admin/node_types' + '{{ $truename ? "/".$truename : "" }}';

          axios[method](action, nodeType).then(function(response) {
            // loading.close()
            window.location.href = "/admin/node_types";
          }).catch(function(error) {
            app.$message.error(error);
            // console.error(error)
            loading.close()
          })
        }).catch(function(error) {
          loading.close();
          // console.error(error);
        })
      },
    }
  })
</script>
@endsection
