@props([
  'title',
  'icon' => null,
  'href' => null,
  'click' => null,
  'target' => null,
  'disabled' => null,
  'theme' => 'md-primary',
])

@if ($href)
<li class="md-list-item">
  <div v-if="{{ $disabled ?? 'false' }}" class="md-list-item-container md-button-clean" disabled>
    <div class="md-list-item-content">
      <i class="md-icon md-icon-font {{ $theme }} md-theme-default">{{ $icon }}</i>
      <span class="md-list-item-text">{{ $title }}</span>
    </div>
  </div>
  <a v-else :href="{{ $href }}" target="{{ $target }}" class="md-list-item-link md-list-item-container md-button-clean">
    <div class="md-list-item-content">
      <i class="md-icon md-icon-font {{ $theme }} md-theme-default">{{ $icon }}</i>
      <span class="md-list-item-text">{{ $title }}</span>
    </div>
  </a>
</li>
@elseif ($click)
<li class="md-list-item">
  <div class="md-list-item-container md-button-clean" @click.stop="{{ $click }}" :disabled="{{ $disabled ?? 'false' }}">
    <div class="md-list-item-content md-ripple">
      <i class="md-icon md-icon-font {{ $theme }} md-theme-default">{{ $icon }}</i>
      <span class="md-list-item-text">{{ $title }}</span>
    </div>
  </div>
</li>
@else
<li class="md-list-item">
  <div class="md-list-item-container md-button-clean" :disabled="{{ $disabled ?? 'false' }}">
    <div class="md-list-item-content md-ripple">
      <i class="md-icon md-icon-font {{ $theme }} md-theme-default">{{ $icon }}</i>
      <span class="md-list-item-text">{{ $title }}</span>
    </div>
  </div>
</li>
@endif
