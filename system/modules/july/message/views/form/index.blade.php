@extends('layout')

@section('h1', '联系表单')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="{{ short_url('message_form.create') }}" title="编辑" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建表单</div></div>
      </a>
    </div>
    {{-- <div class="jc-translate"></div> --}}
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table with-operators"
        :data="models"
        @row-contextmenu="handleContextmenu">
        <el-table-column type="index" label="行号" width="80"></el-table-column>
        <el-table-column label="ID" prop="id" width="200" sortable></el-table-column>
        <el-table-column label="名称" prop="label" width="200" sortable></el-table-column>
        <el-table-column label="描述" prop="description" width="auto"></el-table-column>
        <el-table-column label="操作" width="200">
          <template slot-scope="scope">
            <div class="jc-operators">
              <a :href="getUrl('edit', scope.row.id)" title="编辑" class="md-button md-fab md-dense md-primary md-theme-default"
                :disabled="scope.row.id === 'basic'">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
              </a>
              {{-- @if (config('language.multiple'))
              <a :href="getUrl('translate', scope.row.id)" title="翻译" class="md-button md-fab md-dense md-primary md-theme-default" disabled>
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">translate</i></div></div>
              </a>
              @endif --}}
              <button type="button" title="删除" class="md-button md-fab md-dense md-accent md-theme-default"
                @click.stop="deleteMold(scope.row)" :disabled="scope.row.referenced>0 || scope.row.is_reserved">
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
        <a v-if="contextmenu.editable" :href="contextmenu.editUrl" class="md-list-item-link md-list-item-container md-button-clean">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-primary md-theme-default">edit</i>
            <span class="md-list-item-text">编辑</span>
          </div>
        </a>
        <div v-else class="md-list-item-container md-button-clean" disabled>
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-accent md-theme-default">edit</i>
            <span class="md-list-item-text">编辑</span>
          </div>
        </div>
      </li>
      {{-- @if (config('language.multiple'))
      <li class="md-list-item">
        <a :href="contextmenu.translateUrl" class="md-list-item-link md-list-item-container md-button-clean" :disabled="!contextmenu.translatable">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-primary md-theme-default">translate</i>
            <span class="md-list-item-text">翻译</span>
          </div>
        </a>
      </li>
      @endif --}}
      <li class="md-list-item">
        <div class="md-list-item-container md-button-clean" :disabled="!contextmenu.deletable" @click.stop="deleteMold(contextmenu.target)">
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
        models: @jjson(array_values($models), JSON_PRETTY_PRINT),
        contextmenu: {
          target: null,
          editUrl: null,
          // translateUrl: null,
          editable: false,
          translatable: false,
          deletable: false,
        },

        urlTemplates: {
          editUrl: "{{ short_url('message_forms.edit', '_ID_') }}",
          deleteUrl: "{{ short_url('message_forms.destroy', '_ID_') }}",
          // {{-- translateUrl: "{{ short_url('message_forms.translate', '_ID_') }}", --}}
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
        _tar.editUrl = this.urlTemplates.editUrl.replace('_ID_', row.id);
        _tar.editable = !row.is_reserved;
        _tar.deletable = row.referenced <= 0 && !row.is_reserved;

        // this.contextmenuTarget = row;
        this.$refs.contextmenu.show(event);
      },

      getUrl(route, key) {
        switch (route) {
          case 'edit':
            return this.urlTemplates.editUrl.replace('_ID_', key)
          // case 'translate':
          //   return this.urlTemplates.translateUrl.replace('_ID_', key)
        }
      },

      deleteMold(mold) {
        if (mold.referenced > 0 || mold.is_reserved) {
          return;
        }
        const id = mold.id;
        this.$confirm(`确定要删除 ${id} 类型？`, '删除内容类型', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          const loading = this.$loading({
            lock: true,
            text: '正在删除 ...',
            background: 'rgba(0, 0, 0, 0.7)',
          });
          axios.delete(this.urlTemplates.deleteUrl.replace('_ID_', id))
            .then((response) => {
              // console.log(response)
              loading.spinner = 'el-icon-success';
              loading.text = '已删除';
              window.location.reload();
            })
            .catch((error) => {
              console.error(error);
            });
        }).catch((err) => {});
      },
    },
  });
</script>
@endsection
