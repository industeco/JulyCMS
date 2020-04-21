@extends('admin::layout')

@section('h1', '所有内容')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="/admin/nodes/create" title="编辑" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建内容</div></div>
      </a>
    </div>
    <div class="jc-options">
      <div class="jc-option">
        <label for="nodes_view">呈现方式：</label>
        <select id="nodes_view" class="jc-select">
          <option value="" selected>列表</option>
          <optgroup label="------- 目录 -------">
            @foreach ($catalogs as $catalog)
            <option value="{{ $catalog['truename'] }}">{{ $catalog['name'] }}</option>
            @endforeach
          </optgroup>
        </select>
      </div>
    </div>
    {{-- <div class="jc-translate"></div> --}}
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table
        :data="nodes"
        class="jc-table with-operators">
        <el-table-column label="标题" prop="title" width="300" sortable></el-table-column>
        <el-table-column label="类型" prop="node_type" width="120" sortable></el-table-column>
        <el-table-column label="标签" prop="tags" width="auto">
          <template slot-scope="scope">
            <el-tag v-for="tag in scope.row.tags" size="small" :key="tag">@{{tag}}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="上次修改" prop="updated_at" width="240" sortable>
          <template slot-scope="scope">
            <span>@{{ diffForHumans(scope.row.updated_at) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200">
          <template slot-scope="scope">
            <div class="jc-operators">
              <a :href="url(scope.row.id, 'edit')" title="编辑" class="md-button md-fab md-mini md-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
              </a>
              <a :href="url(scope.row.id, 'translate')" title="翻译" class="md-button md-fab md-mini md-theme-default" disabled>
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">translate</i></div></div>
              </a>
              <button type="button" title="删除" class="md-button md-fab md-mini md-accent md-theme-default"
                @click="deleteNode(scope.row.id)">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div></div>
              </button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
  </div>
@endsection

@section('script')
<script>
  let app = new Vue({
    el: '#main_content',

    data() {
      return {
        nodes: @json(array_values($nodes), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
      }
    },

    methods: {
      diffForHumans(time) {
        return moment(time).fromNow()
      },

      deleteNode(id) {
        this.$confirm(`确定要删除内容？`, '删除内容', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          const loading = app.$loading({
            lock: true,
            text: '正在删除 ...',
            background: 'rgba(255, 255, 255, 0.7)',
          });
          axios.delete('/admin/nodes/'+id).then(function(response) {
            // console.log(response)
            loading.spinner = 'el-icon-success'
            loading.text = '已删除'
            window.location.reload()
          }).catch(function(error) {
            console.error(error)
          })
        }).catch();
      },

      url(id, mode) {
        return `/admin/nodes/${id}/${mode}`;
      },
    },
  });
  // function deleteNode(id) {
  //   alert(id)
  // }
</script>
@endsection
