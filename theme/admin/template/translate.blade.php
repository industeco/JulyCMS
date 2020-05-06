@extends('admin::layout')

@section('h1', '选择语言')

@section('main_content')
  <div class="jc-table-wrapper">
    <table class="jc-table">
      <colgroup>
        <col width="80px">
        <col width="auto">
        <col width="200px">
      </colgroup>
      <thead>
        <tr>
          <th>语言码</th>
          <th>语言</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ $original_langcode }}</td>
          <td>{{ $langs[$original_langcode] }} (源语言)</td>
          <td>
            <div class="jc-operaters">
              <a href="{{ $base_url }}/edit" title="编辑" class="md-button md-fab md-mini md-light-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
              </a>
            </div>
          </td>
        </tr>
        @foreach ($langs as $langcode => $lang)
        @if ($langcode !== $original_langcode)
        <tr>
          <td>{{ $lang }}</td>
          <td>{{ $langcode }}</td>
          <td>
            <div class="jc-operaters">
              <a href="{{ $base_url }}/translate/{{ $langcode }}" title="翻译" class="md-button md-fab md-mini md-light-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">translate</i></div></div>
              </a>
            </div>
          </td>
        </tr>
        @endif
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
