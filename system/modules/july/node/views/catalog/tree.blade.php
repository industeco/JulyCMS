@extends('layout')

@section('h1', "排序 {$model['label']}[{$model['id']}]")

@section('main_content')
  <div id="main_tools">
    <div id="main_tools_left" class="jc-btn-group"></div>
    <div id="main_tools_right" class="jc-btn-group"></div>
  </div>
  <div id="main_area">
    <el-breadcrumb style="padding: 10px;margin-bottom:20px;background:#f1f1f1">
      <el-breadcrumb-item v-for="item,index in breadcrumbs" :key="item.id">
        <a v-if="index < breadcrumbs.length-1" @click.prevent="changeRoot(item)">@{{ item.id ? getNodeAttr(item.id, 'title') : '全部' }}</a>
        <span v-else>@{{ item.id ? getNodeAttr(item.id, 'title') : '全部' }}</span>
      </el-breadcrumb-item>
    </el-breadcrumb>
    <el-tree
      ref="catalog_tree"
      class="jc-tree catalog-tree"
      :data="tree"
      :indent="20"
      draggable
      @node-drop="handleDrop"
      @node-contextmenu="handleContextmenu"
      :allow-drop="allowDrop"
      :allow-drag="allowDrag"
      :node-class="getNodeClass"
      :default-expanded-keys="expanded"
      node-key="id">
      <div class="jc-tree-node-inner" slot-scope="{ node, data }" @dblclick.stop="changeRoot(data)">
        {{-- <svg class="jc-svg-icon jc-drag-handle" v-if="!isRecycle(node)"><use xlink:href="#jcon_drag"></use></svg> --}}
        <span class="jc-tree-node__id" v-if="!isRecycle(node)">[@{{ data.id }}]</span>
        <span class="el-tree-node__label"><span :title="getNodeAttr(data.id, 'title')">@{{ getNodeAttr(data.id, 'title') }}</span></span>
        <span class="jc-tree-node__type" v-if="!isRecycle(node)">类型：@{{ getNodeAttr(data.id, 'mold_id') }}</span>
        <span class="jc-tree-node__updated" v-if="!isRecycle(node)">更新：@{{ getNodeAttr(data.id, 'updated_at') }}</span>
        <button
          type="button" v-if="!isRecycle(node)" title="移出/移入回收站"
          class="md-button md-fab md-mini md-accent md-theme-default jc-theme-light"
          @click.stop="removeNode(data)">
          <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div></div>
        </button>
        <select v-if="isRecycle(node)" v-model="recycle.orderBy" @click.stop @change="sortRecycle">
          <option value="default">默认排序</option>
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
  //   @foreach ($context['positions'] as $position)
  //   {
  //     id:{{ $position['node_id'] }},
  //     parent_id:{{ $position['parent_id'] ?? 'null' }},
  //     prev_id:{{ $position['prev_id'] ?? 'null' }},
  //   },
  //   @endforeach
  // ];
  // --}}

  // 折叠位置数据
  function toTree(nodes, parent_id) {
    const children = [];
    nodes[parent_id].children.forEach(id => {
      const node = nodes[id];
      children.push(node);
      node.children = toTree(nodes, node.id);
    });
    return children;
  }

  // 嵌套的节点数据转为平行的位置数据
  function toPositions(nodes, parent) {
    let positions = [];
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

  // 节点详情，标题、更新时间等
  const _details = @jjson($context['nodes']);

  // 由位置数据转换成的节点数据
  const _nodes = @jjson($context['positions'], JSON_NUMERIC_CHECK);

  // 回收站节点
  _nodes.recycle = {
    id: 'recycle',
    parent_id: null,
    prev_id: null,
    next_id: null,
    children: [],
  };

  let app = new Vue({
    el: '#main_content',
    data() {
      return {
        tree: [],
        breadcrumbs: [],
        recycle: {
          place: 'bottom',
          orderBy: 'default',
          defaultOrders: [],
        },
        root: _nodes[0],
        expanded: ['recycle'],
      }
    },

    created: function() {
      // 指定初始根节点
      const root = _nodes[0];

      // 将原始位置数据转换为嵌套的节点数据，并保存到根节点的子节点数组
      root.children = toTree(_nodes, 0);

      // 将根节点添加到面包屑
      this.breadcrumbs.push(root);

      // 标记根节点
      this.root = root;

      // 初始化目录数据
      // this.$set(this.$data, 'tree', rootChildren);
      this.tree = root.children.slice();

      // 保存初始化的位置数据
      this.original_tree = _.cloneDeep(this.tree);

      // 初始化回收站
      this.initRecycle();
    },

    methods: {
      // 获取树节点类
      getNodeClass(node, tree) {
        if (node.data.id === 'recycle') {
          return ['catalog-tree__recycle', this.recycle.place];
        }
        return '';
      },

      // 将树节点同步到当前根节点的 children 数组
      syncTreeToRoot() {
        this.root.children = this.tree.slice(1);
      },

      // 右键点击
      handleContextmenu(event, data, node, treeNode) {
        this.changeRoot(data);
      },

      // 获取节点数据对应的树节点
      getTreeNode(node) {
        node = _nodes[node.id];
        return this.$refs.catalog_tree.getNode(node);
      },

      // 获取上级节点
      getParentNode(node) {
        const treeNode = this.getTreeNode(_nodes[node.id]);
        if (treeNode.parent.level === 0) {
          return this.root;
        }
        return _nodes[treeNode.parent.data.id];
      },

      // 变更根节点
      changeRoot(node) {
        if (node.id === 'recycle') {
          return;
        }
        this.syncTreeToRoot();
        node = _nodes[node.id];
        const index = this.breadcrumbs.indexOf(node);

        // 如果指定根节点不在面包屑中
        if (index < 0) {
          const path = [];
          let parent = node;
          while (parent !== this.root) {
            path.unshift(parent);
            parent = this.getParentNode(parent);
          }
          this.breadcrumbs.splice(this.breadcrumbs.length, 0, ...path);
        }

        // 如果指定根节点在面包屑中
        else {
          const rest = this.breadcrumbs.splice(index+1);
          this.$set(this.$data, 'expanded', ['recycle'].concat(rest.map(node=>node.id)))
          // this.expanded.splice(1, this.expanded.length-1, ...rest.map(node=>node.id));
        }

        // 强制重新渲染树
        this.renderTree(node.children)

        // 变更根节点
        this.root = node;
      },

      // 强制渲染树
      renderTree(nodes) {
        nodes = nodes.slice();
        nodes.unshift(this.getRecycle());
        this.$set(this.$data, 'tree', nodes);
      },

      // 移除节点（从树里或回收站里）
      removeNode(node) {
        if (_nodes[node.id]) {
          if (node.children && node.children.length) {
            this.$confirm('注意：下级结构会被一并移除！确定要这么做吗？', '移除节点', {
              confirmButtonText: '确定',
              cancelButtonText: '不确定',
              type: 'warning',
            }).then(() => {
              this.removeNodeFromTree(node);
            });
          } else {
            this.removeNodeFromTree(node);
          }
        } else {
          this.removeNodeFromRecycle(node);
        }
      },

      // 从树里移除节点
      removeNodeFromTree(node) {
        const parent = this.getParentNode(node);

        // 将节点转移到回收站
        this.transferNodeToRecycle(parent, node);

        // 如果移除的节点位于树的最顶级，则重新渲染树
        if (parent.id === this.root.id) {
          this.renderTree(parent.children);
        }

        // 因为回收站加入了新节点，排序回收站
        if (this.recycle.orderBy !== 'default') {
          this.sortRecycle();
        }
      },

      // 从回收站中移除节点
      removeNodeFromRecycle(node) {
        // 从回收站中移除
        const recycle = this.getRecycle();
        for (let i = 0, len = recycle.children.length; i < len; i++) {
          if (recycle.children[i].id === node.id) {
            recycle.children.splice(i, 1);
            break;
          }
        }

        // 从回收站默认次序中移除
        const orders = this.recycle.defaultOrders;
        for (let i = 0, len = orders.length; i < len; i++) {
          if (orders[i].id === node.id) {
            orders.splice(i, 1);
            break;
          }
        }

        // 将节点添加到节点树
        this.transferNodeToTree(node);
      },

      // 从目录转移节点到回收站
      transferNodeToRecycle(parent, nodeInTree) {
        // 从上级节点移除
        for (let i = 0, len = parent.children.length; i < len; i++) {
          if (parent.children[i].id === nodeInTree.id) {
            parent.children.splice(i, 1);
            break;
          }
        }

        _nodes[nodeInTree.id] = null;

        const recycle = this.getRecycle();
        const nodeInRecycle = {
          id: nodeInTree.id,
          parent_id: recycle.id,
          prev_id: null,
          next_id: null,
        };

        const first = recycle.children[0];
        if (first) {
          first.prev_id = nodeInTree.id;
          nodeInRecycle.next_id = first.id;
        }

        // 添加到回收站
        recycle.children.unshift(nodeInRecycle);

        // 添加到回收站默认次序
        this.recycle.defaultOrders.unshift(nodeInRecycle);

        if (nodeInTree.children && nodeInTree.children.length) {
          children = nodeInTree.children.slice();
          children.forEach(child => this.transferNodeToRecycle(nodeInTree, child));
        }
      },

      // 从回收站转移节点到目录
      transferNodeToTree(nodeInRecycle) {
        const nodeInTree = {
          id: nodeInRecycle.id,
          parent_id: this.root.id,
          prev_id: null,
          next_id: null,
          children: [],
        };
        _nodes[nodeInRecycle.id] = nodeInTree;

        const last = this.tree[this.tree.length - 1];
        if (last) {
          last.next_id = nodeInRecycle.id;
          nodeInTree.prev_id = last.id;
        }

        this.renderTree(this.tree.slice(1).concat([nodeInTree]));
      },

      // 获取节点属性，保存在 _details 中
      getNodeAttr(id, attr) {
        if (id === 'recycle') {
          return attr === 'title' ? '节点回收站' : '(未定义)';
        }
        const info = _details[id];
        if (!info || !info[attr]) {
          return '(已删除)';
        }
        if (attr == 'updated_at' || attr == 'created_at') {
          const t = moment(info[attr]).fromNow();
          return t.replace('minutes', 'm').replace('seconds', 's');
        }
        return info[attr];
      },

      // 是否允许拖动
      allowDrag(draggingNode) {
        return draggingNode.data.id !== 'recycle';
      },

      // 是否允许拖放
      allowDrop(draggingNode, dropNode, type) {
        return _nodes[dropNode.data.id] != null;
      },

      // 拖放事件
      handleDrop(draggingNode, dropNode, dropType, ev) {
        if (! _nodes[draggingNode.data.id]) {
          const node = draggingNode.data;
          node.children = [];
          _nodes[node.id] = node;
        }
      },

      // 初始化回收站
      initRecycle() {
        // 添加回收站
        const recycle = _nodes.recycle;
        const index = this.tree.indexOf(recycle);
        if (index >= 0) {
          this.tree.splice(index, 1);
        }

        recycle.id = 'recycle';
        recycle.parent_id = null;
        recycle.prev_id = null;
        recycle.next_id = null;
        recycle.children = [];

        const first = this.tree[0];
        if (first) {
          first.prev_id = 'recycle';
          recycle.next_id = first.id;
        }
        this.tree.unshift(recycle);

        // 初始化回收站
        let last = null;
        let current = null;
        _.forEach(_details, (info, id) => {
          if (_nodes[id]) {
            return true;
          }
          current = {
            id: id,
            parent_id: 'recycle',
            prev_id: null,
            next_id: last ? last.id : null,
            children: [],
          };
          recycle.children.unshift(current);
          if (last) {
            last.prev_id = id;
          }
          last = current;
        });
      },

      // 获取回收站
      getRecycle() {
        return _nodes['recycle'];
      },

      // 判断是否回收站
      isRecycle(treeNode) {
        return treeNode.data.id === 'recycle';
      },

      // 排序回收站
      sortRecycle() {
        const recycle = this.getRecycle();

        if (recycle.children.length <= 1) return;

        if (this.recycle.orderBy === 'default') {
          recycle.children.splice(0, recycle.children.length, ...this.recycle.defaultOrders);
          return;
        }

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

      // 移动回收站
      moveRecycle(recycleNode) {
        const places = ['top', 'center', 'bottom'];
        const newPlace = places[(places.indexOf(this.recycle.place) + 1)%3];

        const el = recycleNode.treeNode.$el;
        el.classList.remove(this.recycle.place);
        el.classList.add(newPlace);
        this.recycle.place = newPlace;
      },

      // 提交更改
      submit() {
        // form.$el.submit()
        const loading = this.$loading({
          lock: true,
          text: '正在更新目录 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        this.syncTreeToRoot();
        const tree = _nodes[0].children;
        if (_.isEqual(tree, this.original_tree)) {
          window.location.href = "{{ short_url('catalogs.index') }}";
          return;
        }

        const data = {
          'positions': toPositions(tree)
        };

        axios.put("{{ short_url('catalogs.sort', $model['id']) }}", data)
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
