@extends('backend::layout')

@section('h1', '所有目录')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="{{ short_url('catalogs.create') }}" title="编辑" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建目录</div></div>
      </a>
    </div>
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table with-operators"
        :data="catalogs"
        @row-contextmenu="handleContextmenu">
        <el-table-column type="index" label="行号" width="80"></el-table-column>
        <el-table-column label="ID" prop="id" width="200" sortable></el-table-column>
        <el-table-column label="名称" prop="label" width="200" sortable></el-table-column>
        <el-table-column label="描述" prop="description" width="auto"></el-table-column>
        <el-table-column label="操作" width="200">
          <template slot-scope="scope">
            <div class="jc-operators">
              <a :href="getUrl('edit', scope.row.id)" title="修改" class="md-button md-fab md-dense md-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
              </a>
              <a :href="getUrl('sort', scope.row.id)" title="重排内容" class="md-button md-fab md-dense md-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">sort</i></div></div>
              </a>
              <button type="button" title="删除" class="md-button md-fab md-dense md-accent md-theme-default"
                onclick="deleteCatalog(scope.row)" :disabled="scope.row.is_preset">
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
        <a :href="contextmenu.sortUrl" class="md-list-item-link md-list-item-container md-button-clean">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-primary md-theme-default">sort</i>
            <span class="md-list-item-text">排序</span>
          </div>
        </a>
      </li>
      <li class="md-list-item">
        <div class="md-list-item-container md-button-clean" :disabled="!contextmenu.deletable" @click.stop="deleteCatalog(contextmenu.target)">
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
        catalogs: @json(array_values($catalogs), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        contextmenu: {
          target: null,
          editUrl: null,
          resortUrl: null,
          translateUrl: null,
          translatable: false,
          deletable: false,
        },
        editUrl: "{{ short_url('catalogs.edit', '#id#') }}",
        sortUrl: "{{ short_url('catalogs.sort', '#id#') }}",
        deleteUrl: "{{ short_url('catalogs.destroy', '#id#') }}",
      };
    },

    methods: {
      handleContextmenu(row, column, event) {
        if (column.label === '操作') {
          return;
        }

        const _tar = this.contextmenu;
        _tar.target = row;
        _tar.editUrl = this.editUrl.replace('#id#', row.id);
        _tar.sortUrl = this.sortUrl.replace('#id#', row.id);
        _tar.deletable = !row.is_preset;

        // this.contextmenuTarget = row;
        this.$refs.contextmenu.show(event);
      },

      getUrl(route, id) {
        switch (route) {
          case 'edit':
            return this.editUrl.replace('#id#', id);
          case 'sort':
            return this.sortUrl.replace('#id#', id);
        }
      },

      deleteCatalog(catalog) {
        if (catalog.is_preset) {
          return;
        }
        const id = catalog.id;
        this.$confirm(`确定要删除目录 ${id} ？`, '删除目录', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          const loading = this.$loading({
            lock: true,
            text: '正在删除 ...',
            background: 'rgba(0, 0, 0, 0.7)',
          });
          axios.delete(this.deleteUrl.replace('#id#', id)).then(function(response) {
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
