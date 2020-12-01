@extends('backend::layout')

@section('h1', '排序 '.$catalog['label'].'['.$catalog['id'].']')

@section('main_content')
  <div id="main_tools">
    <div id="main_tools_left" class="jc-btn-group">
    </div>
    <div id="main_tools_right" class="jc-btn-group">
    </div>
  </div>
  <div id="main_area">
    <el-tree
      ref="catalog_reorder"
      class="jc-tree catalog-tree"
      :data="positions"
      :indent="20"
      draggable
      @node-drop="handleDrop"
      :allow-drop="allowDrop"
      :allow-drag="allowDrag"
      :node-class="getNodeClass"
      node-key="id">
      <div class="jc-tree-node-inner" slot-scope="{ node, data }">
        <svg class="jc-svg-icon jc-drag-handle" v-if="data.id !== 'recycle'"><use xlink:href="#jcon_drag"></use></svg>
        <span class="jc-tree-node__id" v-if="data.id !== 'recycle'">[@{{ data.id }}]</span>
        <span class="el-tree-node__label"><span :title="getNodeAttr(data.id, 'title')">@{{ getNodeAttr(data.id, 'title') }}</span></span>
        <span class="jc-tree-node__type" v-if="data.id !== 'recycle'">类型：@{{ getNodeAttr(data.id, 'node_type_id') }}</span>
        <span class="jc-tree-node__updated" v-if="data.id !== 'recycle'">更新：@{{ getNodeAttr(data.id, 'updated_at') }}</span>
        <button
          type="button" v-if="data.id !== 'recycle'" :title="recycled[data.id] ? '移出回收站' : '移入回收站'"
          class="md-button md-fab md-mini md-accent md-theme-default jc-theme-light"
          @click.stop="remove(node)">
          <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div></div>
        </button>
        <button
          type="button" v-else title="切换位置" class="md-button md-mini md-primary md-theme-default"
          @click.stop="switchRecyclePosition(node)">
          <div class="md-ripple">
            <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">swap_vertical_circle</i></div>
          </div>
        </button>
      </div>
    </el-tree>
    <div id="main_form_bottom" class="is-button-item">
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="saveOrder">
        <div class="md-button-content">保存</div>
      </button>
    </div>
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
    },
    @endforeach
  ];

  let app = new Vue({
    el: '#main_content',
    data() {
      return {
        positions: [],
        nodes: @json($nodes, JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        settled: {},
        recycled: {},
        currentRecyclePosition: 'center',
      }
    },

    created: function() {

      // 生成目录中已存在的节点的列表
      initialPositions.forEach(position => {
        this.switchToCatalog(position.id);
      });

      // 初始化目录数据
      this.$set(this.$data, 'positions', toTree(initialPositions));

      // 保存初始化的位置数据
      this.initial_data = JSON.stringify(this.positions);

      // 添加回收站
      const first = this.positions[0];
      if (first) {
        first.prev_id = 'recycle';
      }
      const recycle = {
        id: 'recycle',
        parent_id: null,
        prev_id: null,
        next_id: first ? first.id : null,
        children: [],
      };
      this.positions.unshift(recycle);
      this.switchToRecycle('recycle');

      // 初始化回收站
      let last = null;
      let current = null;
      for (const id in this.nodes) {
        if (this.settled[id]) {
          continue;
        }
        this.switchToRecycle(id);
        current = {
          id: id,
          parent_id: 'recycle',
          prev_id: null,
          next_id: last ? last.id : null,
        };
        recycle.children.unshift(current);
        if (last) {
          last.prev_id = id;
        }
        last = current;
      }
    },

    methods: {
      getNodeClass(node, tree) {
        if (node.data.id === 'recycle') {
          return ['catalog-tree__recycle', this.currentRecyclePosition];
        }
        return '';
      },

      getRecycle() {
        for (let i = 0; i < this.positions.length; i++) {
          const position = this.positions[i];
          if (position.id === 'recycle') {
            return position;
          }
        }
        return null;
      },

      switchToRecycle(id) {
        this.settled[id] = false;
        this.recycled[id] = true;
      },

      switchToCatalog(id) {
        this.recycled[id] = false;
        this.settled[id] = true;
      },

      recycleNode(id) {
        this.switchToRecycle(id);
        const recycle = this.getRecycle();
        const last = recycle.children[0];
        recycle.children.unshift({
          id: id,
          parent_id: 'recycle',
          prev_id: null,
          next_id: last ? last.id : null,
        });
      },

      appendNode(id) {
        this.switchToCatalog(id);
        const last = this.positions[this.positions.length - 1];
        if (last) {
          last.next_id = id;
        }
        this.positions.push({
          id: id,
          parent_id: null,
          prev_id: last ? last.id : null,
          next_id: null,
        });
      },

      getNodeAttr(id, attr) {
        if (id === 'recycle') {
          return attr === 'title' ? '节点回收站' : '(未定义)';
        }

        const node = this.nodes[id];
        if (node && (attr == 'updated_at' || attr == 'created_at')) {
          const t = moment(node[attr]).fromNow();
          return t.replace('minutes', 'm').replace('seconds', 's');
        }
        return (node && node[attr] || '(已删除)');
      },

      allowDrag(draggingNode) {
        return draggingNode.data.id !== 'recycle';
      },

      allowDrop(draggingNode, dropNode, type) {
        return !this.recycled[dropNode.data.id];
      },

      handleDrop(draggingNode, dropNode, dropType, ev) {
        this.switchToCatalog(draggingNode.data.id);
      },

      remove(node) {
        if (node.childNodes && node.childNodes.length) {
          this.$confirm('注意：下级结构会被一并移除！确定要这么做吗？', '移除节点', {
            confirmButtonText: '确定',
            cancelButtonText: '不确定',
            type: 'warning',
          }).then(() => {
            node.remove();
            this.removeNode(node);
          });
        } else {
          node.remove();
          this.removeNode(node)
        }
      },

      removeNode(node) {
        const id = node.data.id;
        if (this.settled[id]) {
          this.recycleNode(id);
          if (node.childNodes && node.childNodes.length) {
            node.childNodes.forEach(child => {
              this.removeNode(child);
            });
          }
        } else {
          this.appendNode(id);
        }
      },

      switchRecyclePosition(node) {
        const positions = ['top', 'center', 'bottom'];
        const pos = positions.indexOf(this.currentRecyclePosition);
        const newPosition = positions[(pos+1)%3];

        const el = node.treeNode.$el;
        el.classList.remove(this.currentRecyclePosition);
        el.classList.add(newPosition);
        this.currentRecyclePosition = newPosition;
      },

      getPositionsWithoutRecycle() {
        const positions = clone(this.positions);
        positions.shift();
        positions[0].prev_id = null;
        return positions;
      },

      saveOrder() {
        // form.$el.submit()
        const loading = app.$loading({
          lock: true,
          text: '正在更新目录 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        const positions = this.getPositionsWithoutRecycle();
        if (app.initial_data === JSON.stringify(positions)) {
          window.location.href = "{{ short_url('catalogs.index') }}";
          return;
        }

        const data = {
          'positions': toRecords(positions).map(function(node) {
            return {
              node_id: node.id,
              parent_id: node.parent_id,
              prev_id: node.prev_id,
              path: node.path,
            };
          }),
        };

        axios.put("{{ short_url('catalogs.updateOrders', $catalog['id']) }}", data).then(function(response) {
          window.location.href = "{{ short_url('catalogs.index') }}";
        }).catch(function(error) {
          app.$message.error(error);
          loading.close();
        });
      },
    }
  })
</script>
@endsection
