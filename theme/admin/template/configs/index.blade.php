@extends('admin::layout')

@section('h1', '设置')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="/admin/configs/create" title="编辑" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建设置</div></div>
      </a>
    </div>
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <table class="jc-table with-operators">
        <colgroup>
          <col width="240px">
          <col width="auto">
          <col width="auto">
          <col width="200px">
        </colgroup>
        <thead>
          <tr>
            <th>名称 [真名]</th>
            <th>值</th>
            <th>描述</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($entries as $entry)
          <tr>
            <td>{{ $entry['label'] ?? '' }} [ {{ $entry['truename'] }} ]</td>
            <td>{{ $entry['value'] }}</td>
            <td>{{ $entry['description'] ?? '' }}</td>
            <td>
              <div class="jc-operators">
                <a href="/admin/configs/{{ $entry['truename'] }}/edit" title="修改" class="md-button md-fab md-mini md-primary md-theme-default">
                  <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
                </a>
                <a href="/admin/configs/{{ $entry['truename'] }}/translate" title="翻译" class="md-button md-fab md-mini md-theme-default" disabled>
                  <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">translate</i></div></div>
                </a>
                <button type="button" title="删除" class="md-button md-fab md-mini md-accent md-theme-default"
                  onclick="deleteConfigEntry('{{ $entry['truename'] }}')" {{ $option['is_preset']?'disabled':'' }}>
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
  function deleteConfigEntry(truename) {
    _vue.$confirm(`确定要删除设置项 ${truename} ？`, '删除设置项', {
      confirmButtonText: '删除',
      cancelButtonText: '取消',
      type: 'warning'
    }).then(() => {
      const loading = _vue.$loading({
        lock: true,
        text: '正在删除 ...',
        background: 'rgba(0, 0, 0, 0.7)',
      });
      axios.delete('/admin/configs/'+truename).then(function(response) {
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
