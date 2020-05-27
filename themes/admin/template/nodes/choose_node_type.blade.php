@extends('admin::layout')

@section('h1', '选择类型')

@section('main_content')
  <div id="main_list">
    <div class="jc-table-wrapper">
      <table class="jc-table">
        <colgroup>
          <col width="200px">
          <col width="200px">
          <col width="auto">
        </colgroup>
        <thead>
          <tr>
            <th>类型</th>
            <th>真名</th>
            <th>描述</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($nodeTypes as $type)
          <tr>
            <td><a href="{{ short_route('nodes.create_with', $type['truename']) }}">{{ $type['name'] }}</a></td>
            <td>{{ $type['truename'] }}</td>
            <td>{{ $type['description'] ?? '' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
