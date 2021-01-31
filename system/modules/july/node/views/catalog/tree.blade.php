@extends('layout')

@section('h1', "排序 {$catalog['label']}[{$catalog['id']}]")

@section('main_content')
  <div id="main_tools">
    <div id="main_tools_left" class="jc-btn-group"></div>
    <div id="main_tools_right" class="jc-btn-group"></div>
  </div>
  <div id="main_area">
    <el-tree
      ref="catalog_tree"
      class="jc-tree catalog-tree"
      :data="tree"
      :indent="20"
      draggable
      @node-drop="handleDrop"
      :allow-drop="allowDrop"
      :allow-drag="allowDrag"
      :node-class="getNodeClass"
      :default-expanded-keys="['recycle']"
      node-key="id">
      <div class="jc-tree-node-inner" slot-scope="{ node, data }">
        <svg class="jc-svg-icon jc-drag-handle" v-if="!isRecycle(node)"><use xlink:href="#jcon_drag"></use></svg>
        <span class="jc-tree-node__id" v-if="!isRecycle(node)">[@{{ data.id }}]</span>
        <span class="el-tree-node__label"><span :title="getNodeAttr(data.id, 'title')">@{{ getNodeAttr(data.id, 'title') }}</span></span>
        <span class="jc-tree-node__type" v-if="!isRecycle(node)">类型：@{{ getNodeAttr(data.id, 'mold_id') }}</span>
        <span class="jc-tree-node__updated" v-if="!isRecycle(node)">更新：@{{ getNodeAttr(data.id, 'updated_at') }}</span>
        <button
          type="button" v-if="!isRecycle(node)" :title="!settled[data.id] ? '移出回收站' : '移入回收站'"
          class="md-button md-fab md-mini md-accent md-theme-default jc-theme-light"
          @click.stop="remove(node)">
          <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div></div>
        </button>
        <select v-if="isRecycle(node)" v-model="recycle.orderBy" @click.stop @change="sortRecycledNodes">
          <option value="default">- 默认排序 -</option>
          <option value="id,asc">id 升序</option>
          <option value="id,desc">id 降序</option>
          <option value="title,asc">标题 升序</option>
          <option value="title,desc">标题 降序</option>
          <option value="mold_id,asc">类型 升序</option>
          <option value="mold_id,desc">类型 降序</option>
          <option value="updated_at,desc">更新时间 最近</option>
          <option value="updated_at,asc">更新时间 最远</option>
        </select>
        <button
          type="button" v-if="isRecycle(node)" title="切换位置" class="md-button md-mini md-primary md-theme-default"
          @click.stop="moveRecycle(node)">
          <div class="md-ripple">
            <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">push_pin</i></div>
          </div>
        </button>
      </div>
    </el-tree>
    <div id="main_form_bottom" class="is-button-item">
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="submit">
        <div class="md-button-content">保存</div>
      </button>
    </div>
  </div>
@endsection

@section('script')
<script>
  // {{--
  // let initialPositions = [
  //   @foreach ($positions as $position)
  //   {
  //     id:{{ $position['node_id'] }},
  //     parent_id:{{ $position['parent_id'] ?? 'null' }},
  //     prev_id:{{ $position['prev_id'] ?? 'null' }},
  //   },
  //   @endforeach
  // ];
  // --}}

  const _positions = @jjson($positions, JSON_NUMERIC_CHECK);

  // 折叠位置数据
  function getChildNodes(nodes, parent_id) {
    const children = [];
    nodes[parent_id].children.forEach(id => {
      const node = nodes[id];
      children.push(node);
      node.children = getChildNodes(nodes, node.id);
    });
    return children;
  }

  // 嵌套的节点数据转为平行的位置数据
  function toPositions(nodes, parent) {
    const positions = [];
    let prev = null;
    nodes.forEach(node => {
      positions.push({
        id: node.id,
        parent_id: parent || null,
        prev_id: prev,
      });
      if (node.children && node.children.length) {
        positions = positions.concat(toPositions(node.children, node.id));
      }
      prev = node.id;
    });
    return positions
  }

  let app = new Vue({
    el: '#main_content',
    data() {
      return {
        tree: [],
        nodes: @jjson($context['nodes']),
        settled: @jjson(array_fill_keys(array_keys($positions), true)),
        breadcrumbs: [],
        recycle: {
          place: 'center',
          orderBy: 'default',
          defaultOrders: null,
        },
      }
    },

    created: function() {
      const root = _positions[0];
      this.breadcrumbs.push(root);

      const rootChildren = getChildNodes(_positions, 0);
      root.children = rootChildren;

      // 初始化目录数据
      // this.$set(this.$data, 'tree', rootChildren);
      this.tree = rootChildren;

      // 保存初始化的位置数据
      this.original_tree = _.cloneDeep(this.tree);

      // 添加回收站
      const first = this.tree[0];
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
      this.tree.unshift(recycle);

      // 初始化回收站
      let last = null;
      let current = null;
      for (const id in this.nodes) {
        if (this.settled[id]) {
          continue;
        }
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
          return ['catalog-tree__recycle', this.recycle.place];
        }
        return '';
      },

      getRecycle() {
        return this.tree[0];
      },

      isRecycle(node) {
        return node.data.id === 'recycle';
      },

      recycleNode(id) {
        this.settled[id] = false;
        this.prependToTree(this.getRecycle().children, id, 'recycle');
        this.sortRecycledNodes();

        this.syncToRecycleDefaultOrders(id);
      },

      appendNode(id) {
        this.settled[id] = true;
        this.appendToTree(this.tree, id);

        this.syncToRecycleDefaultOrders(id);
      },

      appendToTree(tree, id, parent_id) {
        const last = tree[tree.length - 1];
        if (last) {
          last.next_id = id;
        }
        tree.push({
          id: id,
          parent_id: parent_id || null,
          prev_id: last ? last.id : null,
          next_id: null,
        });
      },

      prependToTree(tree, id, parent_id) {
        const first = tree[0];
        if (first) {
          first.prev_id = id;
        }
        tree.unshift({
          id: id,
          parent_id: parent_id || null,
          prev_id: null,
          next_id: first ? first.id : null,
        });
      },

      getNodeAttr(id, attr) {
        if (id === 'recycle') {
          return attr === 'title' ? '节点回收站' : '(未定义)';
        }
        const node = this.nodes[id];
        if (!node || !node[attr]) {
          return '(已删除)';
        }
        if (attr == 'updated_at' || attr == 'created_at') {
          const t = moment(node[attr]).fromNow();
          return t.replace('minutes', 'm').replace('seconds', 's');
        }
        return node[attr];
      },

      allowDrag(draggingNode) {
        return draggingNode.data.id !== 'recycle';
      },

      allowDrop(draggingNode, dropNode, type) {
        return this.settled[dropNode.data.id];
      },

      handleDrop(draggingNode, dropNode, dropType, ev) {
        this.settled[draggingNode.data.id] = true;
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

      syncToRecycleDefaultOrders(id) {
        if (this.recycle.defaultOrders == null) {
          return;
        }

        const orders = this.recycle.defaultOrders;
        if (this.settled[id]) {
          for (let i = 0, len = orders.length; i < len; i++) {
            if (orders[i].id === id) {
              orders.splice(i, 1);
              break;
            }
          }
        } else {
          this.prependToTree(orders, id, 'recycle');
        }
      },

      sortRecycledNodes() {
        const recycle = this.getRecycle();

        if (this.recycle.orderBy === 'default') {
          if (this.recycle.defaultOrders) {
            recycle.children.splice(0, recycle.children.length, ...this.recycle.defaultOrders);
            this.recycle.defaultOrders = null;
          }
          return;
        }

        if (this.recycle.defaultOrders == null) {
          this.recycle.defaultOrders = recycle.children.slice();
        }

        if (recycle.children.length <= 1) return;

        const params = this.recycle.orderBy.split(',');
        const attr = params[0];
        const asc = params[1] === 'asc' ? 1 : -1;

        const children = _.cloneDeep(recycle.children);
        children.sort((a, b) => {
          return this.nodes[a.id][attr] > this.nodes[b.id][attr] ? asc : -asc;
        });

        children[0].prev_id = null;
        children[children.length - 1].next_id = null

        let prev = null;
        children.forEach(node => {
          if (prev) {
            node.prev_id = prev.id;
            prev.next_id = node.id;
          }
          prev = node;
        });

        recycle.children.splice(0, children.length, ...children);
      },

      moveRecycle(recycleNode) {
        const places = ['top', 'center', 'bottom'];
        const newPlace = places[(places.indexOf(this.recycle.place) + 1)%3];

        const el = recycleNode.treeNode.$el;
        el.classList.remove(this.recycle.place);
        el.classList.add(newPlace);
        this.recycle.place = newPlace;
      },

      getTreeNodes() {
        const nodes = _.cloneDeep(this.tree);
        nodes.shift();
        nodes[0].prev_id = null;
        return nodes;
      },

      submit() {
        // form.$el.submit()
        const loading = app.$loading({
          lock: true,
          text: '正在更新目录 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        const nodes = this.getTreeNodes();
        if (_.isEqual(nodes, this.original_tree)) {
          window.location.href = "{{ short_url('catalogs.index') }}";
          return;
        }

        const data = {
          'positions': toPositions(nodes)
        };

        axios.put("{{ short_url('catalogs.sort', $catalog['id']) }}", data)
          .then((response) => {
            window.location.href = "{{ short_url('catalogs.index') }}";
          }).catch((error) => {
            this.$message.error(error);
            loading.close();
          });
      },
    }
  })
</script>
@endsection
