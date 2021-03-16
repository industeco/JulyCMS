
{{-- 引用型字段 --}}
<el-form-item prop="{{ $id }}" size="small" class="{{ $helptext ? 'has-helptext' : '' }}">
  <el-tooltip slot="label" content="{{ $id }}" placement="right" effect="dark" popper-class="jc-twig-output">
    <span>{{ $label }}</span>
  </el-tooltip>
  <el-select v-model="model.{{ $id }}" placeholder="--选择实体--" multiple>
    @foreach (\App\Entity\EntityManager::getEntitiesFromScope($reference_scope) as $entity)
    <el-option value="{{ $entity['id'] }}" label="{{ $entity['title'] }}"></el-option>
    @endforeach
  </el-select>
  @if ($helptext)
  <span class="jc-form-item-help"><i class="el-icon-info"></i> {{ $helptext }}</span>
  @endif
</el-form-item>
