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
            <th>内容类型</th>
            <th>类型 ID</th>
            <th>类型描述</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($models as $mold)
          <tr>
            <td><a href="{{ short_url('nodes.create', $mold->id) }}">{{ $mold->label }}</a></td>
            <td>{{ $mold->id }}</td>
            <td>{{ $mold->description }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
