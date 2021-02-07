@extends('layout')

@section('h1', '内容类型')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="{{ short_url('manage.specs.create') }}" title="新建" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建规格</div></div>
      </a>
    </div>
    {{-- <div class="jc-translate"></div> --}}
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table with-operators"
        :data="specs"
        @row-contextmenu="handleContextmenu">
        <el-table-column type="index" label="行号" width="80"></el-table-column>
        <el-table-column label="ID" prop="id" width="200" sortable></el-table-column>
        <el-table-column label="名称" prop="label" width="200" sortable></el-table-column>
        <el-table-column label="描述" prop="description" width="auto"></el-table-column>
        <el-table-column label="操作" width="200">
          <template slot-scope="scope">
            <div class="jc-operators">
              <a :href="getUrl('edit', scope.row.id)" title="编辑" class="md-button md-fab md-dense md-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
              </a>
              <a :href="getUrl('insert', scope.row.id)" title="数据录入" class="md-button md-fab md-dense md-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">storage</i></div></div>
              </a>
              <button type="button" title="删除" class="md-button md-fab md-dense md-accent md-theme-default"
                @click.stop="deleteSpec(scope.row)">
                <div class="md-ripple">
                  <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">remove</i></div>
                </div>
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
        <a :href="contextmenu.recordsUrl" class="md-list-item-link md-list-item-container md-button-clean">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-primary md-theme-default">storage</i>
            <span class="md-list-item-text">数据录入</span>
          </div>
        </a>
      </li>
      <li class="md-list-item">
        <div class="md-list-item-container md-button-clean" @click.stop="deleteSpec(contextmenu.target)">
          <div class="md-list-item-content">
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
        specs: @jjson(array_values($specs)),
        contextmenu: {
          target: null,
          editUrl: null,
          recordsUrl: null,
        },

        editUrl: "{{ short_url('manage.specs.edit', '_ID_') }}",
        deleteUrl: "{{ short_url('manage.specs.destroy', '_ID_') }}",
        recordsUrl: "{{ short_url('manage.specs.records.index', '_ID_') }}",
      };
    },

    methods: {
      handleContextmenu(row, column, event) {
        if (column.label === '操作') {
          return;
        }

        const _tar = this.contextmenu;
        _tar.target = row;
        _tar.editUrl = this.editUrl.replace('_ID_', row.id);
        _tar.recordsUrl = this.recordsUrl.replace('_ID_', row.id);

        // this.contextmenuTarget = row;
        this.$refs.contextmenu.show(event, this.$refs.contextmenu.$el);
      },

      getUrl(route, key) {
        switch (route) {
          case 'edit':
            return this.editUrl.replace('_ID_', key);
          case 'insert':
            return this.recordsUrl.replace('_ID_', key);
        }
      },

      deleteSpec(spec) {
        const id = spec.id;
        this.$confirm(`确定要删除 ${id} 规格类型？注意：该类型下规格数据会被清空！`, '删除规格类型', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          const loading = this.$loading({
            lock: true,
            text: '正在删除 ...',
            background: 'rgba(0, 0, 0, 0.7)',
          });
          axios.delete(this.deleteUrl.replace('_ID_', id)).then(function(response) {
            // console.log(response)
            loading.spinner = 'el-icon-success';
            loading.text = '已删除';
            window.location.reload();
          }).catch(function(error) {
            console.error(error);
          })
        }).catch((err) => {});
      },
    },
  });
</script>
@endsection
