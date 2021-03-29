@props([
  'model',
  'sortable' => true,
  'deletable' => false,
  'caption' => null,
  'deleteFieldMethod' => 'removeField',
  'editFieldMethod' => 'editField',
])

@if ($caption)
<tbody>
  <tr>
    <th colspan="7">{{ $caption }}</th>
  </tr>
</tbody>
@endif
<tbody
  @if ($sortable)
  is="draggable"
  v-model="{{ $model }}"
  :animation="150"
  ghost-class="jc-drag-ghost"
  handle=".jc-drag-handle"
  tag="tbody"
  @endif>
  <tr v-for="field in {{ $model }}" :key="field.id">
    <td>
      @if ($sortable)
      <i class="md-icon md-icon-font md-theme-default jc-drag-handle">swap_vert</i>
      @endif
    </td>
    <td><span>@{{ field.id }}</span></td>
    <td><span :class="{'jc-label':true,'is-required':field.required}">@{{ field.label }}</span></td>
    <td><span>@{{ field.description }}</span></td>
    <td><span>@{{ field.rules }}</span></td>
    <td><span>@{{ field.field_type }}</span></td>
    <td>
      <div class="jc-operators">
        <button
          type="button"
          class="md-button md-icon-button md-primary md-theme-default"
          title="编辑"
          @click.stop="{{ $editFieldMethod }}(field)">
          <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_edit_circle"></use></svg>
          {{-- <i class="md-icon md-icon-font md-theme-default">edit</i> --}}
        </button>
        <button type="button" class="md-button md-icon-button md-accent md-theme-default" title="删除"
          @click.stop="{{ $deleteFieldMethod }}({{ $model }}, field)" {{ $deletable ? '' : 'disabled' }}>
          <i class="md-icon md-icon-font md-theme-default">remove_circle</i>
        </button>
      </div>
    </td>
  </tr>
</tbody>
