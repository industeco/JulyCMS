@extends('backend::layout')

@section('h1', '内容类型')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="addRecord()">
        <div class="md-button-content">新增</div>
      </button>
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="importRecords()">
        <div class="md-button-content">导入</div>
      </button>
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="exportRecords()">
        <div class="md-button-content">导出</div>
      </button>
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
        :disabled="!selected.length"
        @click.stop="assign.dialogVisible = true">
        <div class="md-button-content">批量赋值</div>
      </button>
    </div>
    <div class="jc-options">
      <div class="jc-option">
        <el-select v-model="filter.field" size="small" class="jc-filterby" native-size="30">
          <el-option label="-- 选择字段 --" value=""></el-option>
          @foreach ($fields as $field)
          <el-option label="{{ $field['label'] }}" value="{{ $field['field_id'] }}"></el-option>
          @endforeach
        </el-select>
      </div>
      <div class="jc-option">
        <el-select v-model="filter.method" size="small" class="jc-filterby">
          <el-option label="-- 比较方式 --" value=""></el-option>
          <el-option label="等于" value="eq"></el-option>
          <el-option label="不等于" value="not_eq"></el-option>
          <el-option label="含有" value="ctn"></el-option>
          <el-option label="不含有" value="not_ctn"></el-option>
          <el-option label="属于" value="in"></el-option>
          <el-option label="不属于" value="not_in"></el-option>
          <el-option label="大于" value="gt"></el-option>
          <el-option label="小于" value="lt"></el-option>
          <el-option label="正则" value="regexp"></el-option>
        </el-select>
      </div>
      <div class="jc-option">
        <el-input v-model="filter.value" size="small" native-size="30" @keyup.enter.native="applyFilter()"></el-input>
      </div>
      <div class="jc-option">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="applyFilter()">
          <div class="md-button-content">筛选</div>
        </button>
      </div>
    </div>
    {{-- <div class="jc-translate"></div> --}}
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table jc-dense jc-data-table with-operators"
        :data="slicedRecords"
        :border="true"
        @selection-change="handleSelectionChange"
        style="width: 100%">
        <el-table-column type="selection" width="50"></el-table-column>
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
                @click.stop="removeRecord(scope.row)">
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
    title="原始数据"
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
  <el-dialog
    id="mass_assign"
    title="批量赋值"
    top="-5vh"
    :close-on-click-modal="false"
    :visible.sync="assign.dialogVisible">
    <el-form label-width="60px" label-position="left">
      <el-form-item label="字段">
        <el-select v-model="assign.field" size="small" class="jc-filterby">
          <el-option label="-- 选择字段 --" value=""></el-option>
          @foreach ($fields as $field)
          <el-option label="{{ $field['label'] }}" value="{{ $field['field_id'] }}"></el-option>
          @endforeach
        </el-select>
      </el-form-item>
      <el-form-item label="值">
        <el-input v-model="assign.value" type="textarea" rows="1"></el-input>
      </el-form-item>
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click.stop="assign.dialogVisible = false">取 消</el-button>
      <el-button size="small" type="primary" @click.stop="massAssign()">确 定</el-button>
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

        selected: [],

        filter: {
          field: '',
          method: '',
          value: '',
          filtered: false,
        },

        assign: {
          field: '',
          value: '',
          dialogVisible: false,
        },

        total: {{ count($records) }},
        perPage: 20,
        currentPage: 1,
      };
    },

    created() {
      this.initial_records = clone(this.records);
    },

    computed: {
      slicedRecords() {
        return this.records.slice((this.currentPage-1)*this.perPage,this.currentPage*this.perPage);
      },
    },

    methods: {
      importRecords() {
        this.rawData = null;
        this.collectorVisible = true;
      },

      exportRecords() {
        let rawData = '';
        this.records.forEach(record => {
          @foreach ($fields as $field)
          rawData += record["{{ $field['field_id'] }}"].trim() + '|';
          @endforeach
          rawData += '\n';
        });
        this.rawData = rawData;
        this.collectorVisible = true;
      },

      addRecord() {
        this.records.unshift(clone(this.template));
        this.total++;
      },

      removeRecord(rec) {
        const id = rec.id;
        this.$confirm(`要删除该规格吗？`, '删除规格', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          this.records.splice(this.records.indexOf(rec), 1);
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
          this.$set(this.$data, 'records', records.concat(this.records));
        }, 100);
      },

      handleSelectionChange(selected) {
        this.$set(this.$data, 'selected', selected);
      },

      // 批量赋值
      massAssign() {
        this.assign.dialogVisible = false;
        if (! this.selected.length) {
          return;
        }

        const field = this.assign.field;
        if (! field) {
          return;
        }

        const value = this.assign.value.trim();
        this.selected.forEach(rec => {
          rec[field] = value;
        });

        this.$set(this.$data, 'selected', []);
      },

      applyFilter() {
        const recs = this.getFilteredRecords();
        if (recs) {
          this.$set(this.$data, 'selected', []);
          this.$set(this.$data, 'records', recs);
          this.total = recs.length;
        }
      },

      getFilteredRecords() {
        if (!this.filter.field || !this.filter.method) {
          if (this.filtered) {
            this.filtered = false;
            return clone(this.initial_records);
          }
        } else {
          const recs = this.filterRecords();
          if (recs) {
            this.filtered = true;
            return recs;
          }
        }
        return null;
      },

      filterRecords() {
        let value = this.filter.value.trim();
        const method = this.filter.method;
        if (!value.length && method !== 'eq' && method !== 'not_eq') {
          return null;
        }
        const field = this.filter.field;

        let filter = () => true;
        switch (this.filter.method) {
          case 'eq':
            filter = rec => rec[field] === value;
            break;
          case 'not_eq':
            filter = rec => rec[field] !== value;
            break;
          case 'ctn':
            value = value.toLowerCase();
            filter = rec => rec[field].toLowerCase().indexOf(value) >= 0;
            break;
          case 'not_ctn':
            value = value.toLowerCase();
            filter = rec => rec[field].toLowerCase().indexOf(value) < 0;
            break;
          case 'in':
            value = '|'+value.replace(/[\n\r|]+/, '|')+'|';
            filter = rec => value.indexOf('|'+rec[field]+'|') >= 0;
            break;
          case 'not_in':
            value = '|'+value.replace(/[\n\r|]+/, '|')+'|';
            filter = rec => value.indexOf('|'+rec[field]+'|') < 0;
            break;
          case 'gt':
            filter = rec => {
              const v1 = parseFloat(rec[field]);
              const v2 = parseFloat(value);
              if (v1!=NaN && v2!=NaN) {
                return v1 > v2;
              }
              return rec[field] > value;
            };
            break;
          case 'lt':
            filter = rec => {
              const v1 = parseFloat(rec[field]);
              const v2 = parseFloat(value);
              if (v1!=NaN && v2!=NaN) {
                return v1 < v2;
              }
              return rec[field] < value;
            };
            break;
          case 'regexp':
            value = new RegExp(value);
            filter = rec => value.test(rec[field]);
            break;
          default: break;
        }

        return clone(this.initial_records.filter(filter));
      },

      handlePageChange(page) {
        this.currentPage = page;
        this.$set(this.$data, 'selected', []);
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
