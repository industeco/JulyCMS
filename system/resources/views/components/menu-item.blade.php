@props([
  'title',
  'icon' => null,
  'href' => null,
  'click' => null,
  'target' => null,
  'disabled' => null,
])

@if ($href)
<li class="md-list-item">
  <a class="md-list-item-link md-list-item-container md-button-clean"
    :href="{{ $href }}" target="{{ $target }}" :disabled="{{ $disabled ?? 'false' }}">
    <div class="md-list-item-content">
      <i class="md-icon md-icon-font md-theme-default">{{ $icon }}</i>
      <span class="md-list-item-text">{{ $title }}</span>
    </div>
  </a>
</li>
@elseif ($click)
<li class="md-list-item">
  <div class="md-list-item-container md-button-clean" @click.stop="{{ $click }}" :disabled="{{ $disabled ?? 'false' }}">
    <div class="md-list-item-content md-ripple">
      <i class="md-icon md-icon-font md-theme-default">{{ $icon }}</i>
      <span class="md-list-item-text">{{ $title }}</span>
    </div>
  </div>
</li>
@else
<li class="md-list-item">
  <div class="md-list-item-container md-button-clean" :disabled="{{ $disabled ?? 'false' }}">
    <div class="md-list-item-content md-ripple">
      <i class="md-icon md-icon-font md-theme-default">{{ $icon }}</i>
      <span class="md-list-item-text">{{ $title }}</span>
    </div>
  </div>
</li>
@endif
