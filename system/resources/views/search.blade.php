@extends('layout')

@section('h1', '"'.$keywords.'" 的搜索结果：')

@section('main_content')
  <div class="jc-table-wrapper">
    <el-table class="jc-table"
      :data="results">
      <el-table-column label="内容 ID" prop="node_id" width="100" sortable></el-table-column>
      <el-table-column label="内容标题" prop="title" width="auto" sortable>
        <template slot-scope="scope">
          <a :href="url(scope.row)" target="_blank">@{{ scope.row.node_title }}</a>
        </template>
      </el-table-column>
      <el-table-column label="字段" prop="field_label" width="200" sortable></el-table-column>
      <el-table-column label="字段真名" prop="node_field" width="200" sortable></el-table-column>
      <el-table-column label="字段类型" prop="field_type" width="150" sortable></el-table-column>
      <el-table-column label="语言版本" prop="langcode" width="150" sortable></el-table-column>
    </el-table>
  </div>
@endsection

@section('script')
<script>
  let app = new Vue({
    el: '#main_content',

    data() {
      return {
        results: @json(array_values($results), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
      };
    },

    methods: {
      url(row) {
        if (row.original_langcode === row.langcode) {
          const url = "{{ short_url('nodes.edit', 'ID') }}";
          return url.replace('ID', row.node_id);
        } else {
          const url = "{{ short_url('nodes.translate', ['ID','LANGCODE']) }}";
          return url.replace('ID', row.node_id).replace('LANGCODE', row.langcode);
        }
        return url;
      },
    },
  });
</script>
@endsection
