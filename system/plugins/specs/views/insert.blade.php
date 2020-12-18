@extends('backend::layout')

@section('h1', '内容类型')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="addSpec()">
        <div class="md-button-content">新增规格</div>
      </button>
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="importSpecs()">
        <div class="md-button-content">批量导入</div>
      </button>
    </div>
    {{-- <div class="jc-translate"></div> --}}
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table jc-dense jc-data-table with-operators"
        :data="specs">
        <el-table-column type="index" label="行号" width="80"></el-table-column>
        @foreach ($fields as $field)
        <el-table-column label="{{ $field['label'] }}" prop="{{ $field['field_id'] }}" sortable>
          <template slot-scope="scope">
            <input type="text" class="jc-input-intable" v-model="scope.row.{{ $field['field_id'] }}">
          </template>
        </el-table-column>
        @endforeach
        <el-table-column label="操作" width="200">
          <template slot-scope="scope">
            <div class="jc-operators">
              <button type="button" title="删除" class="md-button md-fab md-dense md-accent md-theme-default"
                @click.stop="deleteSpec(scope.row)">
                <div class="md-ripple">
                  <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">remove</i></div>
                </div>
              </button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
  </div>
@endsection

@section('script')
<script>
  const app = new Vue({
    el: '#main_content',

    data() {
      return {
        specs: @json(array_values($specs), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        template: @json($template, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        contextmenu: {
          target: null,
        },
      };
    },

    methods: {
      importSpecs() {
        //
      },

      addSpec() {
        this.specs.unshift(clone(this.template));
      },

      deleteSpec(spec) {
        const id = spec.id;
        this.$confirm(`要删除该规格吗？`, '删除规格', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          this.specs.splice(this.specs.indexOf(spec), 1);
        }).catch((err) => {});
      },
    },
  });
</script>
@endsection
