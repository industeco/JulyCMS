@extends('admin::layout')

@section('h1', '目录')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="/admin/catalogs/create" title="编辑" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建目录</div></div>
      </a>
    </div>
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <table class="jc-table with-operators">
        <colgroup>
          <col width="200px">
          <col width="200px">
          <col width="auto">
          <col width="200px">
        </colgroup>
        <thead>
          <tr>
            <th>真名</th>
            <th>名称</th>
            <th>描述</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($catalogs as $catalog)
          <tr>
            <td>{{ $catalog['truename'] }}</td>
            <td>{{ $catalog['name'] }}</td>
            <td>{{ $catalog['description'] ?? '' }}</td>
            <td>
              <div class="jc-operators">
                <a href="/admin/catalogs/{{ $catalog['truename'] }}/edit" title="修改" class="md-button md-fab md-mini md-primary md-theme-default">
                  <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
                </a>
                <a href="/admin/catalogs/{{ $catalog['truename'] }}/reorder" title="重排内容" class="md-button md-fab md-mini md-primary md-theme-default">
                  <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">sort</i></div></div>
                </a>
                <a href="/admin/catalogs/{{ $catalog['truename'] }}/translate" title="翻译" class="md-button md-fab md-mini md-theme-default" disabled>
                  <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">translate</i></div></div>
                </a>
                <button type="button" title="删除" class="md-button md-fab md-mini md-accent md-theme-default"
                  onclick="deleteCatalog('{{ $catalog['truename'] }}')" {{ $catalog['is_preset']?'disabled':'' }}>
                  <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div></div>
                </button>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection

@section('script')
<script>
  // const app = new Vue();
  const _vue = Vue.prototype;
  function deleteCatalog(truename) {
    _vue.$confirm(`确定要删除目录 ${truename} ？`, '删除目录', {
      confirmButtonText: '删除',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      const loading = _vue.$loading({
        lock: true,
        text: '正在删除 ...',
        background: 'rgba(0, 0, 0, 0.7)',
      });
      axios.delete('/admin/catalogs/'+truename).then(function(response) {
        // console.log(response)
        loading.spinner = 'el-icon-success'
        loading.text = '已删除'
        window.location.reload()
      }).catch(function(error) {
        console.error(error)
      })
    }).catch();
  }
</script>
@endsection
