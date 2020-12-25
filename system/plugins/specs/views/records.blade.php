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
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click.stop="assign.dialogVisible = true">
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
        <el-select v-model="filter.method" size="small" class="jc-filterby" style="width: 130px">
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
        <el-input v-model="filter.value" size="small" native-size="25" @keyup.enter.native="applyFilter()"></el-input>
      </div>
      <div class="jc-option">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
          :disabled="!canFilter"
          @click.stop="applyFilter()">
          <div class="md-button-content">筛选</div>
        </button>
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
          :disabled="!filter.filtered"
          @click.stop="clearFilter()">
          <div class="md-button-content">清除</div>
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
        @row-contextmenu="handleContextmenu"
        @selection-change="handleSelectionChange"
        style="width: 100%">
        <el-table-column type="index" label="行号" width="60" fixed></el-table-column>
        <el-table-column type="selection" width="50"></el-table-column>
        @foreach ($fields as $field)
        <el-table-column label="{{ $field['label'] }}" prop="{{ $field['field_id'] }}" sortable></el-table-column>
        @endforeach
      </el-table>
    </div>
    <div style="text-align: right;margin: 10px 0 -20px;">
      <el-pagination
        background
        layout="sizes, prev, pager, next"
        :total="total"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="perPage"
        @current-change="handlePageChange"
        @size-change="handleSizeChange">
      </el-pagination>
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
      <el-button size="small" type="primary" @click.stop="handleImportingConfirm">确 定</el-button>
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
  <el-dialog
    title="编辑/新增记录"
    top="-5vh"
    :close-on-click-modal="false"
    :visible.sync="record.dialogVisible">
    <el-form :inline="true">
      @foreach ($fields as $field)
      <el-form-item label="{{ $field['label'] }}">
        <el-input v-model="record.data.{{ $field['field_id'] }}" type="text" size="small" native-size="40"></el-input>
      </el-form-item>
      @endforeach
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click.stop="record.dialogVisible = false">取 消</el-button>
      <el-button size="small" type="primary" @click.stop="handleRecordConfirm()">确 定</el-button>
    </span>
  </el-dialog>
  <jc-contextmenu ref="contextmenu">
    <li class="md-list-item">
      <div class="md-list-item-container md-button-clean" @click.stop="editRecord(contextmenu.target)">
        <div class="md-list-item-content md-ripple">
          <i class="md-icon md-icon-font md-primary md-theme-default">edit</i>
          <span class="md-list-item-text">编辑</span>
        </div>
      </div>
    </li>
    <li class="md-list-item">
      <div class="md-list-item-container md-button-clean" @click.stop="removeRecord(contextmenu.target)">
        <div class="md-list-item-content md-ripple">
          <i class="md-icon md-icon-font md-accent md-theme-default">remove_circle</i>
          <span class="md-list-item-text">删除</span>
        </div>
      </div>
    </li>
  </jc-contextmenu>
@endsection

@section('script')
<script>
  const app = new Vue({
    el: '#main_content',

    data() {
      return {
        records: @json(array_values($records), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        filteredRecords: [],

        template: @json($template, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        collectorVisible: false,
        rawData: null,

        selected: [],

        assign: {
          field: '',
          value: '',
          dialogVisible: false,
        },

        filter: {
          field: '',
          method: '',
          value: '',
          filtered: true,
        },

        record: {
          data: @json($template, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
          dialogVisible: false,
        },

        contextmenu: {
          target: null,
        },

        total: {{ count($records) }},
        perPage: 20,
        currentPage: 1,
      };
    },

    created() {
      this._map = {};
      this.records.forEach(rec => {
        this.addRecordToMap(rec);
      });
      this.clearFilter();
      this._template_md5 = this.getRecordMd5(@json($template, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    },

    computed: {
      slicedRecords() {
        return this.filteredRecords.slice((this.currentPage-1)*this.perPage,this.currentPage*this.perPage);
      },
      canFilter() {
        const f = this.filter;
        return f.field && f.method && (f.value.length || f.method === 'eq' || f.method === 'not_eq');
      },
    },

    methods: {
      // 打开数据导入界面
      importRecords() {
        this.rawData = null;
        this.collectorVisible = true;
      },

      // 生成导出数据，并打开数据导出界面
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

      // 打开新增记录界面
      addRecord() {
        this.record = {
          @foreach ($fields as $field)
          {{ $field['field_id'] }}: null,
          @endforeach
        };

        // this.records.unshift(clone(this.template));
        // this.total++;
      },

      // 删除指定记录
      removeRecord(rec) {
        const rec_id = rec.id;
        console.log(rec_id)
        this.$confirm(`要删除该规格吗？`, '删除规格', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          const loading = this.$loading({
            lock: true,
            text: '正在删除……',
            background: 'rgba(255, 255, 255, 0.7)',
          });

          const action = "{{ short_url('specs.records.remove', [$spec_id, '_ID_']) }}".replace('_ID_', rec_id);
          console.log(action)
          axios.delete(action).then(response => {
            this.filteredRecords.splice(this.filteredRecords.indexOf(rec), 1);
            this.records.splice(this.records.indexOf(rec), 1);
            const md5key = rec.md5 || this.getRecordMd5(rec);
            this._map[md5key] = null;
            this.total--;
            loading.close();
            this.$message.success('已删除');
          }).catch(error => {
            loading.close();
            console.error(error);
            this.$message.error('删除失败，请查看后台日志');
          });

        }).catch((err) => {});
      },

      // 编辑指定记录
      editRecord(row) {
        if (row.md5 == null) {
          row.md5 = this.getRecordMd5(row);
        }

        this.$set(this.$data.record, 'data', clone(row));
        this.record.dialogVisible = true;
      },

      // 导入数据
      handleImportingConfirm() {
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

      // 展示右键菜单
      handleContextmenu(row, column, event) {
        this.contextmenu.target = row;
        this.$refs.contextmenu.show(event);
      },

      // 列选择改变
      handleSelectionChange(selected) {
        this.selected = selected;
      },

      // 批量赋值
      massAssign() {
        this.assign.dialogVisible = false;

        const field = this.assign.field;
        if (!this.selected.length || !field) {
          return;
        }

        const value = this.assign.value.trim();
        this.selected.forEach(rec => {
          rec[field] = value;
        });

        this.selected = [];
      },

      // 清除筛选
      clearFilter() {
        if (this.filter.filtered) {
          this.filter.filtered = false;
          this.selected = [];
          this.$set(this.$data, 'filteredRecords', this.records);
          this.total = this.filteredRecords.length;
        }
      },

      // 应用筛选
      applyFilter() {
        if (!this.canFilter) return;

        this.selected = [];

        const recs = this.filterRecords(this.filter.field, this.filter.method, this.filter.value);
        if (recs.length !== this.filteredRecords.length) {
          if (rec.length === this.records.length) {
            this.clearFilter();
            return;
          }
          this.filter.filtered = true;
          this.$set(this.$data, 'filteredRecords', clone(recs));
          this.total = this.filteredRecords.length;
        }
      },

      // 筛选记录集
      filterRecords(field, method, value) {
        let filter = () => true;
        switch (method) {
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

        return this.records.filter(filter);
      },

      // 当前页改变
      handlePageChange(page) {
        this.currentPage = page;
        this.selected = [];
      },

      // 每页显示量改变
      handleSizeChange(size) {
        this.perPage = size;
      },

      // 将指定记录加入到 _map
      addRecordToMap(rec) {
        if (rec.md5 != null) {
          this._map[rec.md5] = null;
        }
        const md5key = this.getRecordMd5(rec);
        rec.md5 = md5key;
        this._map[md5key] = rec;
      },

      // 获取一条记录的 md5 值作为唯一键
      getRecordMd5(rec) {
        let key = '';
        @foreach ($fields as $field)
        key += "{{ $field['field_id'] }}:" + (rec["{{ $field['field_id'] }}"] == null ? '' : rec["{{ $field['field_id'] }}"]) + ',';
        @endforeach
        return md5(key);
      },

      // 判断一条记录是否有效：
      //  1. 不为空
      //  2. 不存在
      isValidRecord(rec) {
        const key = this.getRecordMd5(rec);
        return key !== this._template_md5 && this._map[key] == null;
      },

      // 保存新增或编辑后的记录
      handleRecordConfirm() {
        // 关闭编辑面板
        this.record.dialogVisible = false;

        // 更新
        this.upsertRecords([this.record.data]);
      },

      syncRecord(rec) {
        if (rec.md5) {
          const _rec = this._map[rec.md5];
          if (_rec) {
            @foreach ($fields as $field)
            _rec["{{ $field['field_id'] }}"] = rec["{{ $field['field_id'] }}"];
            @endforeach
          }
        }
      },

      // 更新或新建记录集
      upsertRecords(records) {
        const validRecords = [];
        records.forEach(rec => {
          if (this.isValidRecord(rec)) {
            this.syncRecord(rec);
            validRecords.push(rec);
          }
        });

        if (! validRecords.length) {
          this.$message.warning('没有要更新的数据');
          return;
        }

        const loading = app.$loading({
          lock: true,
          text: '正在保存……',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        axios.post("{{ short_url('specs.records.upsert', $spec_id) }}", {
          records: validRecords,
        }).then(response => {
          loading.close();
          this.$message.success('保存成功');
        }).catch(error => {
          loading.close();
          console.error(error);
          this.$message.error('保存失败，可能是数据格式不正确');
        });
      },
    },
  });
</script>
@endsection
