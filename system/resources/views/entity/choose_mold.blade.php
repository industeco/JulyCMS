@extends('layout')

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
            <th>ID</th>
            <th>描述</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($node_types as $type)
          <tr>
            <td><a href="{{ short_url('nodes.create', $type->id) }}">{{ $type->label }}</a></td>
            <td>{{ $type->id }}</td>
            <td>{{ $type->description ?: '' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
