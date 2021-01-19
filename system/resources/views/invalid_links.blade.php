@extends('backend::layout')

@section('h1', '无效链接：')

@section('main_content')
  <div class="jc-table-wrapper">
    <el-table class="jc-table"
      :data="invalidLinks">
      <el-table-column label="内容 ID" prop="content_id" width="100" sortable>
        <template slot-scope="scope">
          <a :href="editUrl(scope.row)" target="_blank">@{{ scope.row.content_id }}</a>
        </template>
      </el-table-column>
      <el-table-column label="内容标题" prop="content_title" width="auto" sortable>
        <template slot-scope="scope">
          <a :href="scope.row.url" target="_blank">@{{ scope.row.content_title }}</a>
        </template>
      </el-table-column>
      <el-table-column label="无效链接" prop="link" width="auto" sortable></el-table-column>
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
        invalidLinks: @json(array_values($invalidLinks), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
      };
    },

    methods: {
      editUrl(row) {
        const route = "{{ short_url('contents.edit', 'CONTENT_ID') }}";
        return route.replace('CONTENT_ID', row.content_id);
      },
    },
  });
</script>
@endsection
