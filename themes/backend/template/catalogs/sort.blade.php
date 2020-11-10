@extends('backend::layout')

@section('h1', '排序 '.$label.'['.$truename.'] 目录')

@section('main_content')
  <div id="main_tools">
    <div id="main_tools_left" class="jc-btn-group">
      <button type="button" class="md-button md-dense md-raised md-primary md-theme-default"
        @click="nodeSelectorVisible = true">
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
      :data="catalogNodes"
      :draggable="true"
      :indent="20"
      node-key="node_id">
      <div class="jc-tree-node-inner" slot-scope="{ node, data }">
        <svg class="jc-svg-icon jc-drag-handle"><use xlink:href="#jcon_drag"></use></svg>
        <span class="el-tree-node__label">[@{{ data.node_id }}] @{{ getNodeInfo(data.node_id, 'title') }}</span>
        <span class="jc-tree-nodeinfo">类型：@{{ getNodeInfo(data.node_id, 'node_type') }}</span>
        <span class="jc-tree-nodeinfo">上次修改：@{{ getNodeInfo(data.node_id, 'updated_at') }}</span>
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
      :visible.sync="nodeSelectorVisible">
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
          prop="node_type"
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
        <el-button size="small" @click="nodeSelectorVisible = false">取 消</el-button>
        <el-button size="small" type="primary" @click="handleNodeSelectorConfirm">确 定</el-button>
      </span>
    </el-dialog>
  </div>
@endsection

@section('script')
<script>

  let catalog_nodes = @json($catalog_nodes, JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

  let app = new Vue({
    el: '#main_content',
    data() {
      return {
        catalogNodes: [],
        nodes: @json($all_nodes, JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        selectableNodes: [],
        selectedNodes: null,
        nodesInCatalog: {},
        nodeSelectorVisible: false,
      }
    },

    created: function() {
      // 初始化目录数据
      this.$set(this.$data, 'catalogNodes', toTree(catalog_nodes));

      // 生成目录中已存在的节点的列表
      for (let i = 0; i < catalog_nodes.length; i++) {
        this.nodesInCatalog[catalog_nodes[i].node_id] = true;
      }

      this.chooseSelectableNode()

      this.initial_data = JSON.stringify(this.catalogNodes)
    },

    methods: {
      chooseSelectableNode() {
        const nodes = [];
        for (const id in this.nodes) {
          if (! this.nodesInCatalog[id]) {
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
          this.$confirm('下级节点会被一并删除，且不可恢复！确定要删除吗？', '删除节点', {
            confirmButtonText: '删除',
            cancelButtonText: '取消',
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
        this.nodesInCatalog[node.data.node_id] = false
        this.chooseSelectableNode()
      },

      handleNodesSelectionChange(selected) {
        this.selectedNodes = selected;
      },

      // 当按下对话框确定按钮时
      handleNodeSelectorConfirm() {
        let prev = this.catalogNodes[this.catalogNodes.length - 1];
        for (let i = 0; i < this.selectedNodes.length; i++) {
          const node = this.selectedNodes[i];
          this.catalogNodes.push({
            'node_id': node.id,
            'parent_id': null,
            'prev_id': node && node.node_id || null,
          })
          this.nodesInCatalog[node.id] = true
          prev = node;
        }
        this.chooseSelectableNode()
        this.nodeSelectorVisible = false
      },

      saveOrder() {
        // form.$el.submit()
        const loading = app.$loading({
          lock: true,
          text: '正在更新目录 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        if (app.initial_data === JSON.stringify(app.catalogNodes)) {
          window.location.href = "{{ short_url('catalogs.index') }}";
          return;
        }

        const data = {
          'content_value_langcode': '{{ $langcode }}',
          'catalog_nodes': toRecords(app.catalogNodes),
        };

        axios.put("{{ short_url('catalogs.updateOrders', $truename) }}", data).then(function(response) {
          // console.log(response)
          // loading.close()
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
