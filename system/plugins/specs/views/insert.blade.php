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
        <el-table-column label="操作" width="80">
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
    <div id="main_form_bottom" class="is-button-item">
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="save">
        <div class="md-button-content">保存</div>
      </button>
    </div>
  </div>
  <el-dialog
    id="data_collector"
    title="粘贴数据"
    top="-5vh"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    :visible.sync="collectorVisible">
    <el-input v-model="rawData" type="textarea" rows="9"></el-input>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click.stop="collectorVisible = false">取 消</el-button>
      <el-button size="small" type="primary" @click.stop="handleDataImportConfirm">确 定</el-button>
    </span>
  </el-dialog>
@endsection

@section('script')
<script>
  const app = new Vue({
    el: '#main_content',

    data() {
      return {
        specs: @json(array_values($specs), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        template: @json($template, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        collectorVisible: false,
        rawData: null,
      };
    },

    methods: {
      importSpecs() {
        this.rawData = null;
        this.collectorVisible = true;
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

      handleDataImportConfirm() {
        this.collectorVisible = false;

        const content = this.rawData.replace(/[\n\r]+/g, '\n');
        this.rawData = null;

        content.split('\n').forEach(line => {
          line = line.trim().replace(/[\s,]*$/, '').replace(/^[\s,]*/, '');
          if (line.length) {
            const record = line.split(',');
            this.specs.unshift({
              @foreach (array_values($fields) as $index => $field)
              {{ $field['field_id'] }}: record[{{ $index }}],
              @endforeach
            });
          }
        });
      },

      save() {
        //
      },
    },
  });
</script>
@endsection
