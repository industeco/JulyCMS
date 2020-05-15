@extends('admin::layout')

@section('h1', '内容类型')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="/admin/node_types/create" title="编辑" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建类型</div></div>
      </a>
    </div>
    {{-- <div class="jc-translate"></div> --}}
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table with-operators"
        :data="nodeTypes"
        @row-contextmenu="handleContextmenu">
        <el-table-column type="index" label="行号" width="80"></el-table-column>
        <el-table-column label="真名" prop="truename" width="200" sortable></el-table-column>
        <el-table-column label="名称" prop="name" width="200" sortable></el-table-column>
        <el-table-column label="描述" prop="description" width="auto"></el-table-column>
        <el-table-column label="操作" width="200">
          <template slot-scope="scope">
            <div class="jc-operators">
              <a :href="'/admin/node_types/'+scope.row.truename+'/edit'" title="编辑" class="md-button md-fab md-dense md-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
              </a>
              <a :href="'/admin/node_types/'+scope.row.truename+'/translate'" title="翻译" class="md-button md-fab md-dense md-primary md-theme-default" disabled>
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">translate</i></div></div>
              </a>
              <button type="button" title="删除" class="md-button md-fab md-dense md-accent md-theme-default"
                @click="deleteType(scope.row)" :disabled="scope.row.nodes>0">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">remove</i></div></div>
              </button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <jc-contextmenu ref="contextmenu">
      <li class="md-list-item">
        <a :href="contextmenu.editUrl" class="md-list-item-link md-list-item-container md-button-clean">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-primary md-theme-default">edit</i>
            <span class="md-list-item-text">编辑</span>
          </div>
        </a>
      </li>
      <li class="md-list-item">
        <a :href="contextmenu.translateUrl" class="md-list-item-link md-list-item-container md-button-clean" :disabled="!contextmenu.translatable">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-primary md-theme-default">translate</i>
            <span class="md-list-item-text">翻译</span>
          </div>
        </a>
      </li>
      <li class="md-list-item">
        <div class="md-list-item-container md-button-clean" :disabled="!contextmenu.deletable" @click.stop="deleteType(contextmenu.target)">
          <div class="md-list-item-content md-ripple">
            <i class="md-icon md-icon-font md-accent md-theme-default">remove_circle</i>
            <span class="md-list-item-text">删除</span>
          </div>
        </div>
      </li>
    </jc-contextmenu>
  </div>
@endsection

@section('script')
<script>
  const app = new Vue({
    el: '#main_content',

    data() {
      return {
        nodeTypes: @json(array_values($nodeTypes), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        contextmenu: {
          target: null,
          editUrl: null,
          translateUrl: null,
          editable: true,
          translatable: false,
          deletable: false,
        },
      };
    },

    methods: {
      handleContextmenu(row, column, event) {
        if (column.label === '操作') {
          return;
        }

        const _tar = this.contextmenu;
        _tar.target = row;
        _tar.editUrl = '/admin/node_types/'+row.truename+'/edit';
        _tar.deletable = row.nodes <= 0;

        // this.contextmenuTarget = row;
        this.$refs.contextmenu.show(event);
      },

      deleteType(nodeType) {
        if (nodeType.nodes > 0) {
          return;
        }
        const truename = nodeType.truename;
        this.$confirm(`确定要删除 ${truename} 类型？`, '删除内容类型', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          const loading = _vue.$loading({
            lock: true,
            text: '正在删除 ...',
            background: 'rgba(0, 0, 0, 0.7)',
          });
          axios.delete('/admin/node_types/'+truename).then(function(response) {
            // console.log(response)
            loading.spinner = 'el-icon-success'
            loading.text = '已删除'
            window.location.reload()
          }).catch(function(error) {
            console.error(error)
          })
        }).catch((err) => {});
      },
    },
  });
</script>
@endsection
