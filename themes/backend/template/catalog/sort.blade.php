@extends('backend::layout')

@section('h1', '排序 '.$catalog['label'].'['.$catalog['id'].']')

@section('main_content')
  <div id="main_tools">
    <div id="main_tools_left" class="jc-btn-group">
      <button type="button" class="md-button md-dense md-raised md-primary md-theme-default"
        @click="nodeListVisible = true">
        <div class="md-ripple"><div class="md-button-content">添加内容</div></div>
      </button>
    </div>
    <div id="main_tools_right" class="jc-btn-group">
    </div>
  </div>
  <div id="main_area">
    <el-tree
      ref="catalog_reorder"
      class="jc-tree"
      :data="positions"
      :draggable="true"
      :indent="20"
      node-key="id">
      <div class="jc-tree-node-inner" slot-scope="{ node, data }">
        <svg class="jc-svg-icon jc-drag-handle"><use xlink:href="#jcon_drag"></use></svg>
        <span class="el-tree-node__label">[@{{ data.id }}] @{{ getNodeInfo(data.id, 'title') }}</span>
        <span class="jc-tree-nodeinfo">类型：@{{ getNodeInfo(data.id, 'node_type_id') }}</span>
        <span class="jc-tree-nodeinfo">上次修改：@{{ getNodeInfo(data.id, 'updated_at') }}</span>
        <button
          type="button" title="从当前目录移除" class="md-button md-fab md-mini md-accent md-theme-default jc-theme-light"
          @click="remove(node)">
          <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div></div>
        </button>
      </div>
    </el-tree>
    <div id="main_form_bottom" class="is-button-item">
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click="saveOrder">
        <div class="md-button-content">保存</div>
      </button>
    </div>
    <el-dialog
      id="node_selector"
      top="-5vh"
      :show-close="false"
      :visible.sync="nodeListVisible">
      <el-table
        ref="nodes_table"
        title="选择内容"
        :data="selectableNodes"
        style="width: 100%;"
        @selection-change="handleNodesSelectionChange"
        class="jc-table jc-dense">
        <el-table-column
          type="selection"
          width="50">
        </el-table-column>
        <el-table-column
          prop="id"
          label="ID"
          width="100"
          sortable>
        </el-table-column>
        <el-table-column
          prop="title"
          label="标题"
          width="300"
          sortable>
        </el-table-column>
        <el-table-column
          prop="node_type_id"
          label="类型"
          sortable>
        </el-table-column>
        <el-table-column
          prop="updated_at"
          label="上次修改"
          sortable>
        </el-table-column>
      </el-table>
      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="nodeListVisible = false">取 消</el-button>
        <el-button size="small" type="primary" @click="handleNodeSelectorConfirm">确 定</el-button>
      </span>
    </el-dialog>
  </div>
@endsection

@section('script')
<script>

  let initialPositions = [
    @foreach ($positions as $position)
    {
      id:{{ $position['node_id'] }},
      parent_id:{{ $position['parent_id'] ?? 'null' }},
      prev_id:{{ $position['prev_id'] ?? 'null' }},
      path:"{{ $position['path'] }}",
    },
    @endforeach
  ];

  let app = new Vue({
    el: '#main_content',
    data() {
      return {
        positions: [],
        nodes: @json($nodes, JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        selectableNodes: [],
        selected: null,
        settled: {},
        nodeListVisible: false,
      }
    },

    created: function() {
      // 初始化目录数据
      this.$set(this.$data, 'positions', toTree(initialPositions));

      // 生成目录中已存在的节点的列表
      for (let i = 0; i < initialPositions.length; i++) {
        this.settled[initialPositions[i].id] = true;
      }

      this.chooseSelectableNode()

      this.initial_data = JSON.stringify(this.positions)
    },

    methods: {
      chooseSelectableNode() {
        const nodes = [];
        for (const id in this.nodes) {
          if (! this.settled[id]) {
            nodes.push(this.nodes[id])
          }
        }
        this.$set(this.$data, 'selectableNodes', nodes);
      },

      getNodeInfo(node_id, attr) {
        const node = this.nodes[node_id];
        if (node && (attr == 'updated_at' || attr == 'created_at')) {
          const info = moment(node[attr]).fromNow();
          return info.replace('minutes', 'm').replace('seconds', 's');
        }
        return node && node[attr] || '(已删除)';
      },

      remove(node) {
        if (node.childNodes && node.childNodes.length) {
          this.$confirm('注意：下级节点会被一并删除！要删除吗？', '删除节点', {
            confirmButtonText: '要',
            cancelButtonText: '不要',
            type: 'warning',
          }).then(() => {
            this.removeNode(node)
          })
        } else {
          this.removeNode(node)
        }
      },

      removeNode(node) {
        node.remove();
        this.settled[node.data.node_id] = false
        this.chooseSelectableNode()
      },

      handleNodesSelectionChange(selected) {
        this.selected = selected;
      },

      // 当按下对话框确定按钮时
      handleNodeSelectorConfirm() {
        let prev = this.positions[this.positions.length - 1];
        for (let i = 0; i < this.selected.length; i++) {
          const node = this.selected[i];
          this.positions.push({
            'id': node.id,
            'parent_id': null,
            'prev_id': prev && prev.id || null,
          })
          this.settled[node.id] = true
          prev = node;
        }
        this.chooseSelectableNode()
        this.nodeListVisible = false
      },

      saveOrder() {
        // form.$el.submit()
        const loading = app.$loading({
          lock: true,
          text: '正在更新目录 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        if (app.initial_data === JSON.stringify(app.positions)) {
          window.location.href = "{{ short_url('catalogs.index') }}";
          return;
        }

        const data = {
          'positions': toRecords(app.positions).map(function(node) {
            return {
              node_id: node.id,
              parent_id: node.parent_id,
              prev_id: node.prev_id,
              path: node.path,
            };
          }),
        };

        console.log(data);

        axios.put("{{ short_url('catalogs.updateOrders', $catalog['id']) }}", data).then(function(response) {
          // console.log(response)
          window.location.href = "{{ short_url('catalogs.index') }}";
        }).catch(function(error) {
          app.$message.error(error);
          // console.error(error)
          loading.close()
        })
      },
    }
  })
</script>
@endsection
