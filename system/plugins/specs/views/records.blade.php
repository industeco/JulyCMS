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
        :data="records.slice((currentPage-1)*perPage,currentPage*perPage)" :border="true" style="width: 100%">
        <el-table-column type="index" label="行号" width="80" fixed></el-table-column>
        @foreach ($fields as $field)
        <el-table-column label="{{ $field['label'] }}" prop="{{ $field['field_id'] }}" sortable>
          <template slot-scope="scope">
            <input type="text" class="jc-input-intable" v-model="scope.row.{{ $field['field_id'] }}">
          </template>
        </el-table-column>
        @endforeach
        <el-table-column label="操作" width="80" fixed="right">
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
    <div style="text-align: right;margin: 10px 0 -20px;">
      <el-pagination
        background
        layout="sizes, prev, pager, next"
        :total="total"
        :page-sizes="[10, 20, 50, 100, 200]"
        :page-size="perPage"
        @current-change="handlePageChange"
        @size-change="handleSizeChange">
      </el-pagination>
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
        records: @json(array_values($records), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        template: @json($template, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        collectorVisible: false,
        rawData: null,

        total: {{ count($records) }},
        perPage: 20,
        currentPage: 1,
      };
    },

    methods: {
      importSpecs() {
        this.rawData = null;
        this.collectorVisible = true;
      },

      addSpec() {
        this.records.unshift(clone(this.template));
        this.total++;
      },

      deleteSpec(spec) {
        const id = spec.id;
        this.$confirm(`要删除该规格吗？`, '删除规格', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          this.records.splice(this.records.indexOf(spec), 1);
          this.total--;
        }).catch((err) => {});
      },

      handleDataImportConfirm() {
        this.collectorVisible = false;

        const loading = this.$loading({
          lock: true,
          text: '正在导入……',
          background: 'rgba(0, 0, 0, 0.7)',
        });

        setTimeout(() => {
          const records = [];
          this.rawData.split(/[\r\n]+/).forEach(line => {
            line = line.replace(/,\s*$/, '').replace(/^\s*,/, '');
            if (line.length) {
              const record = line.split(',');
              records.unshift({
                @foreach (array_values($fields) as $index => $field)
                {{ $field['field_id'] }}: record[{{ $index }}],
                @endforeach
              });
            }
          });
          this.rawData = null;

          Vue.nextTick(() => {
            loading.close();
          });

          this.total += records.length;
          Vue.set(this.$data, 'records', records.concat(this.records));
        }, 100);
      },

      handlePageChange(page) {
        this.currentPage = page;
      },

      handleSizeChange(size) {
        this.perPage = size;
      },

      save() {
        const loading = app.$loading({
          lock: true,
          text: '正在提交数据……',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        const data = {records: this.records};

        axios.post("{{ short_url('specs.insert', $spec_id) }}", data).then(function(response) {
          window.location.href = "{{ short_url('specs.index') }}";
        }).catch(function(error) {
          loading.close();
          console.error(error);
          app.$message.error('保存失败，可能是数据格式不正确');
        });
      },
    },
  });
</script>
@endsection
