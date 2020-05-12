@extends('admin::layout')

@section('h1')
  {{ $mode=='translate'?'翻译':($mode=='edit'?'编辑':'新建') }}内容 <span id="content_locale">[ {{ langname($content_value_langcode) }} ]</span>
@endsection

{{-- @if ($mode === 'edit')
  @section('translate_btn')
    <a href="/admin/nodes/{{ $id }}/translate/zh" class="md-button md-dense md-raised md-primary md-theme-default">
      <div class="md-ripple"><div class="md-button-content">翻译</div></div>
    </a>
  @endsection
@endif --}}

@section('main_content')
  <el-form id="main_form" ref="main_form"
    :model="node"
    :rules="rules"
    label-position="top">
    <div id="main_form_left">
      @foreach ($fields as $field)
      {!! $field['element'] !!}
      @endforeach
      <div id="main_form_bottom" class="is-button-item">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click="submitMainForm">
          <div class="md-button-content">保存</div>
        </button>
      </div>
    </div>
    <div id="main_form_right">
      <h2 class="jc-form-info-item">通用非必填项</h2>
      @if ($mode !== 'translate')
      <div class="jc-form-info-item">
        <button type="button" id="addto_catalogs_btn" class="md-button md-raised md-dense md-primary md-theme-default" @click="addtoCatalogsDialogVisible = true">
          <div class="md-button-content">添加到目录</div>
        </button>
        <p>已添加：@{{ current_catalogs }}</p>
      </div>
      @endif
      <el-collapse :value="['url','meta']">
        {{-- <el-collapse-item title="标签" name="1">
          <el-form-item label="标签" size="small" class="has-helptext">
            <el-select
              v-model="node.tags"
              multiple
              filterable
              allow-create
              default-first-option
              placeholder="选择标签">
              <el-option
                v-for="tag in db.tags"
                :key="tag"
                :label="tag"
                :value="tag">
              </el-option>
            </el-select>
            <span class="jc-form-item-help"><i class="el-icon-info"></i> 选择或新建标签，新建时请注意当前语言版本</span>
          </el-form-item>
        </el-collapse-item> --}}
        <el-collapse-item title="网址和模板" name="url">
          {!! $fields_aside['url']['element'] !!}
          {!! $fields_aside['template']['element'] !!}
        </el-collapse-item>
        <el-collapse-item title="META 信息" name="meta">
          {!! $fields_aside['meta_title']['element'] !!}
          {!! $fields_aside['meta_description']['element'] !!}
          {!! $fields_aside['meta_keywords']['element'] !!}
        </el-collapse-item>
      </el-collapse>
    </div>
  </el-form>
  <el-dialog
    id="addto_catalogs_dialog"
    ref="addto_catalogs"
    title="添加到目录"
    top="-5vh"
    :visible.sync="addtoCatalogsDialogVisible">
    <el-tabs tab-position="left" style="height: 560px;">
      @foreach (array_keys($catalog_nodes) as $catalog)
      <el-tab-pane label="{{ $catalog }}">
        <div class="jc-scroll-wrapper">
          <div class="jc-scroll md-scrollbar md-theme-default">
            <el-tree
              ref="catalog_{{ $catalog }}"
              class="jc-tree"
              :data="db.catalog_nodes.{{ $catalog }}"
              :draggable="true"
              :allow-drag="isDraggable"
              :indent="20"
              @node-drop="handleDrop('{{ $catalog }}', ...arguments)"
              :default-expanded-keys="[{{ $id }}]"
              node-key="node_id">
              <span :class="{'jc-tree-node-inner':true, 'is-homeless':isHomeless(node, '{{ $catalog }}')}" slot-scope="{ node, data }">
                <svg class="jc-svg-icon jc-drag-handle" v-if="isDraggable(node)"><use xlink:href="#jcon_drag"></use></svg>
                <span class="el-tree-node__label">[@{{ data.node_id }}] @{{ nodeTitle(data.node_id) }}</span>
                <button v-if="isHomeless(node, '{{ $catalog }}')"
                  type="button" title="添加到末尾" class="md-button md-fab md-mini md-primary md-theme-default jc-theme-light"
                  @click="appendNode('{{ $catalog }}')">
                  <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">add</i></div></div>
                </button>
                <button v-if="isDraggable(node)"
                  type="button" title="从当前目录移除" class="md-button md-fab md-mini md-accent md-theme-default jc-theme-light"
                  @click="removeNode(node, '{{ $catalog }}')">
                  <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div></div>
                </button>
              </span>
            </el-tree>
          </div>
        </div>
      </el-tab-pane>
      @endforeach
    </el-tabs>
    <p>已添加到：<span v-for="(val, key) in node_positions" :key="key">@{{ describePosition(val, key) }} </span></p>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click="addtoCatalogsDialogVisible = false">取 消</el-button>
      <el-button size="small" type="primary" @click="addtoCatalogsDialogVisible = false">确 定</el-button>
    </span>
  </el-dialog>
@endsection

@section('script')

{{-- 通过 script:template 保存 html 内容 --}}
@foreach (array_merge($fields, $fields_aside) as $field)
  @if ($field['type']=='html')
  <script type="text/tmplate" id="field_value__{{ $field['truename'] }}">
    {!! $field["value"] !!}
  </script>
  @endif
@endforeach

<script>
  window.showMediasWindow = function() {
    let mediaWindow = null;

    return function showMediasWindow() {
      const screenWidth = window.screen.availWidth;
      const screenHeight = window.screen.availHeight;

      const width = screenWidth*.8;
      const height = screenHeight*.8 - 60;
      const left = screenWidth*.1;
      const top = screenHeight*.15;

      if (!mediaWindow || mediaWindow.closed) {
        mediaWindow = window.open(
          '/admin/medias/select',
          'chooseMedia',
          `resizable,scrollbars,status,top=${top},left=${left},width=${width},height=${height}`
        );
      } else {
        mediaWindow.focus()
      }
    }
  }();

  function recieveMediaUrl(url) {
    app.recieveMediaUrl(url)
  }

  let initial_positions = {!! $positions ? json_encode($positions, JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) : '{}' !!};
  for (const key in initial_positions) {
    if (initial_positions.hasOwnProperty(key)) {
      initial_positions[key] = initial_positions[key][0];
    }
  }

  let catalog_nodes = @json($catalog_nodes, JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  for (const catalog in catalog_nodes) {
    if (catalog_nodes.hasOwnProperty(catalog)) {
      catalog_nodes[catalog] = toTree(catalog_nodes[catalog]);
      if (initial_positions[catalog] == null) {
        catalog_nodes[catalog].push({
          node_id: {{ $id }},
        })
      }
    }
  }

  function getHtmlFieldValue(fieldName) {
    return document.getElementById('field_value__'+fieldName).innerHTML;
  }

  let app = new Vue({
    el: '#main_content',
    data() {

      var isUniqueUrl = function(rule, value, callback) {
        if (!value || !value.length) {
          callback();
        } else {
          axios.post('/admin/checkunique/node__url', {
            content_value_langcode: '{{ $content_value_langcode }}',
            url: value,
            id: {{ $id }},
          }).then(function(response) {
            if (response.data.exists) {
              callback(new Error('url 已存在'));
            } else {
              callback();
            }
          }).catch(function(error) {
            console.error(error);
          })
        }
      };

      return {
        node: {
          content_value_langcode: '{{ $content_value_langcode }}',
          interface_value_langcode: '{{ $interface_value_langcode }}',
          id: {{ $id }},
          node_type: '{{ $node_type }}',
          @foreach (array_merge($fields, $fields_aside) as $field)
          @if ($field['type']=='html')
          {{ $field['truename'] }}: getHtmlFieldValue("{{ $field['truename'] }}"),
          @else
          {{ $field['truename'] }}: `{!! $field["value"] !!}`,
          @endif
          @endforeach
          tags: [],
        },

        node_positions: clone(initial_positions),

        rules: {
          @foreach (array_merge($fields, $fields_aside) as $field)
          @if($field['rules'])
          {{ $field['truename'] }}: [
            @foreach ($field['rules'] as $rule)
            {!! $rule !!},
            @endforeach
            @if ($field['truename'] == 'url')
            { validator: isUniqueUrl, trigger: 'blur' }
            @endif
          ],
          @endif
          @endforeach
        },

        ckeditorConfig: @json(config('jc.editor_config.ckeditor'), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),

        db: {
          nodes: @json($all_nodes, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
          tags: @json($all_tags, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
          catalog_nodes: catalog_nodes,
        },

        addtoCatalogsDialogVisible: false,

        // catalogTrees: {},
      }
    },

    computed: {
      current_catalogs() {
        return Object.keys(this.node_positions)
      },
    },

    created: function() {
      this.initial_data = clone(this.node)
    },

    methods: {
      onEditorReady() {
        if (! this.editorInited) {
          // 不清除空的 i 标签和 span 标签
          CKEDITOR.dtd.$removeEmpty.i = 0;
          CKEDITOR.dtd.$removeEmpty.span = 0;
          CKEDITOR.dtd.$removeEmpty.a = 0;

          CKEDITOR.dtd['a']['div'] = 1;
          CKEDITOR.dtd['a']['p'] = 1;
          CKEDITOR.dtd['a']['ul'] = 1;
          CKEDITOR.dtd['a']['ol'] = 1;

          this.editorInited = true;
        }
      },

      nodeTitle(node_id) {
        if (node_id == this.node.id) {
          return this.node.title
        }
        if (this.db.nodes[node_id]) {
          return this.db.nodes[node_id].title
        }
        return '';
      },

      isDraggable(node) {
        return node.data.node_id == this.node.id
      },

      isHomeless(node, catalog) {
        return this.isDraggable(node) && this.node_positions[catalog] == null;
      },

      removeNode(node, catalog) {
        if (node.childNodes && node.childNodes.length) {
          this.$confirm('下级节点会被一并删除，且不可恢复！确定要删除吗？', '删除节点', {
            confirmButtonText: '删除',
            cancelButtonText: '取消',
            type: 'warning',
          }).then(() => {
            node.remove();
            Vue.delete(this.node_positions, catalog);
            this.addHomelessNode(catalog)
          })
        } else {
          node.remove();
          Vue.delete(this.node_positions, catalog);
          this.addHomelessNode(catalog)
        }
      },

      appendNode(catalog) {
        // node.data.isSettled = true;
        const nodes = this.db.catalog_nodes[catalog];
        Vue.set(this.node_positions, catalog, {
          parent_id: null,
          prev_id: nodes.length > 1 ? nodes[nodes.length-2].node_id : null,
        })
      },

      addHomelessNode(catalog) {
        // console.log(this.db.catalog_nodes[catalog])
        this.db.catalog_nodes[catalog].push({
            node_id: this.node.id,
        })
      },

      handleDrop(catalog, draggingNode, dropNode, dropType) {
        // console.log(arguments)
        // const catalog = dropNode.treeNode.tree.name;

        const parent = dropType === 'inner' ? dropNode : dropNode.parent;
        let prev = null;
        switch (dropType) {
          case 'after':
            prev = dropNode;
            break;
          case 'inner':
            if (dropNode.childNodes.length > 1) {
              prev = dropNode.childNodes[dropNode.childNodes.length - 2];
            }
            break;
          case 'before':
            const index = dropNode.parent.childNodes.indexOf(dropNode);
            if (index > 1) {
              prev = dropNode.parent.childNodes[index - 2];
            }
            break;
        }

        this.$set(this.node_positions, catalog, {
          parent_id: parent.level > 0 ? parent.data.node_id : null,
          prev_id: prev ? prev.data.node_id : null,
        })
      },

      describePosition(pos, catalogName) {
        const map = {before:'之前',after:'之后',inner:'之内'};
        let desc = `[ ${catalogName} ] 目录的 `;
        if (!pos.parent_id && !pos.prev_id) {
          desc += '第一个；';
        } else {
          if (pos.prev_id) {
            desc += `[ ${pos.prev_id}: ${this.nodeTitle(pos.prev_id)} ] 之后；`;
          } else {
            desc += `[ ${pos.parent_id}: ${this.nodeTitle(pos.parent_id)} ] 之内；`;
          }
        }
        return desc
      },

      isDifferentPosition(pos1, pos2) {
        return !pos1 || !pos2 || pos1.parent_id != pos2.parent_id || pos1.prev_id != pos2.prev_id
      },

      getChangedPositions() {
        const changed = [];
        const catalogs = _.uniq(Object.keys(this.node_positions).concat(Object.keys(initial_positions)));

        for (let i = 0, len = catalogs.length; i < len; i++) {
          const catalog = catalogs[i];
          const position = this.node_positions[catalog];
          if (this.isDifferentPosition(position, initial_positions[catalog])) {
            changed.push({
              catalog: catalog,
              parent_id: position.parent_id,
              prev_id: position.prev_id,
            })
          }
        }

        return changed;
      },

      getChangedValues() {
        const changed = [];
        for (const key in this.node) {
          if (this.node.hasOwnProperty(key)) {
            if (! isEqual(this.node[key], this.initial_data[key])) {
              changed.push(key)
            }
          }
        }
        return changed
      },

      showMedias(field) {
        this.recieveMediaUrlFor = field;
        showMediasWindow();
      },

      recieveMediaUrl(url) {
        if (this.recieveMediaUrlFor) {
          this.node[this.recieveMediaUrlFor] = url;
        }
      },

      submitMainForm() {

        let form = this.$refs.main_form;

        const loading = this.$loading({
          lock: true,
          text: '正在保存内容 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        form.validate().then(function() {
          const changed_values = app.getChangedValues();
          const changed_positions = app.getChangedPositions();
          @if ($id)
            if (!changed_values.length && !changed_positions.length) {
              window.location.href = "/admin/nodes";
              return;
            }
          @endif

          const node = clone(app.node);

          node.changed_values = changed_values;
          node.changed_positions = changed_positions;

          // console.log(node)

          axios.{{ $id ? 'put' : 'post' }}('/admin/nodes{{ $id ? "/".$id : "" }}', node)
            .then(function(response) {
              // console.log(response)
              // loading.close()
              window.location.href = "/admin/nodes";
            })
            .catch(function(error) {
              loading.close()
              app.$message.error(error);
            })
        }).catch(function(error) {
          loading.close();
          console.error(error);
        })
      },
    }
  })
</script>
@endsection
