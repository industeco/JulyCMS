@extends('admin::layout')

@section('h1', '所有内容')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="/admin/nodes/create" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建</div></div>
      </a>
      <button type="button" class="md-button md-dense md-raised md-primary md-theme-default"
        :disabled="!selected.length"
        @click="render">
        <div class="md-ripple"><div class="md-button-content">生成</div></div>
      </button>
    </div>
    <div class="jc-options">
      <div class="jc-option">
        <label>显示『建议模板』：</label>
        <el-switch v-model="showSuggestedTemplates"></el-switch>
      </div>
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
      <el-table class="jc-table with-operators"
        :data="nodes"
        @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="50"></el-table-column>
        <el-table-column label="ID" prop="id" width="100" sortable></el-table-column>
        <el-table-column label="标题" prop="title" width="auto" sortable>
          <template slot-scope="scope">
            <a v-if="scope.row.url" :href="scope.row.url" target="_blank">@{{ scope.row.title }}</a>
            <span v-else>@{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="建议模板" prop="templates" width="auto" v-if="showSuggestedTemplates">
          <template slot-scope="scope">
            <span class="jc-suggested-template" v-for="template in scope.row.templates" :key="template">@{{ template }}</span>
          </template>
        </el-table-column>
        <el-table-column label="类型" prop="node_type" width="120" sortable></el-table-column>
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
              <a :href="url(scope.row.id, 'translate')" title="翻译" class="md-button md-fab md-mini md-primary md-theme-default">
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
        selected: [],
        showSuggestedTemplates: false,
      };
    },

    methods: {
      diffForHumans(time) {
        return moment(time).fromNow();
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

      handleSelectionChange(selected) {
        this.$set(this.$data, 'selected', selected);
      },

      render() {
        const loading = this.$loading({
          lock: true,
          text: '正在生成 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        const nodes = [];
        this.selected.forEach(element => {
          nodes.push(element.id);
        });

        axios.post('/admin/nodes/render', {nodes: nodes}).then((response) => {
          // console.log(response)
          loading.close();
          this.$message.success('生成完成');
        }).catch(err => {
          loading.close();
          console.error(err);
          this.$message.error('发生错误');
        });
      },
    },
  });
  // function deleteNode(id) {
  //   alert(id)
  // }
</script>
@endsection
