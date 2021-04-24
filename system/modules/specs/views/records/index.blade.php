@extends('layout')

@section('h1', '内容类型')

@section('inline-style')
<style>
  #main_list td>.cell>a.is-invalid{color: #ff5200}
</style>
@endsection

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
          <el-option label="无效图片" value="invalid-image" v-if="filter.field == 'image'"></el-option>
        </el-select>
      </div>
      <div class="jc-option">
        <el-input v-model="filter.value" size="small" native-size="25" @keyup.enter.native="applyFilter()" :disabled="filter.method=='invalid-image'"></el-input>
      </div>
      <div class="jc-option">
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
          :disabled="!canFilter"
          @click.stop="applyFilter()">
          <div class="md-button-content">筛选</div>
        </button>
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
          :disabled="!filter.filtered"
          @click.stop="resetFilter()">
          <div class="md-button-content">重置</div>
        </button>
      </div>
    </div>
    {{-- <div class="jc-translate"></div> --}}
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table jc-dense jc-data-table with-operators"
        :data="pageRecords"
        :border="true"
        @row-contextmenu="handleContextmenu"
        @selection-change="handleSelectionChange"
        style="width: 100%">
        <el-table-column type="index" label="行号" width="60" fixed></el-table-column>
        <el-table-column type="selection" width="50"></el-table-column>
        @foreach ($fields as $field)
        @if ($field['field_id'] == 'image')
        <el-table-column label="{{ $field['label'] }}" prop="{{ $field['field_id'] }}" sortable>
          <template slot-scope="scope">
            <a :href="scope.row.image" target="_blank" rel="noopener noreferrer" :class="{'is-invalid':scope.row.image_invalid}">@{{ scope.row.image }}</a>
          </template>
        </el-table-column>
        @else
        <el-table-column label="{{ $field['label'] }}" prop="{{ $field['field_id'] }}" sortable></el-table-column>
        @endif
        @endforeach
      </el-table>
    </div>
    <div style="text-align: right; margin: 20px 0;">
      <el-pagination
        background
        layout="sizes, prev, pager, next"
        :page-sizes="[10, 20, 50, 100]"
        :total="pagination.total"
        :page-size="pagination.perPage"
        @current-change="handlePageChange"
        @size-change="handleSizeChange">
      </el-pagination>
    </div>
  </div>
  <el-dialog
    title="导入导出"
    top="-5vh"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    :visible.sync="port.dialogVisible">
    <el-input v-model="port.raw" type="textarea" rows="10"></el-input>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click.stop="port.dialogVisible = false">取 消</el-button>
      <el-button size="small" type="primary" @click.stop="handlePortDialogConfirm">确 定</el-button>
    </span>
  </el-dialog>
  <el-dialog
    title="批量赋值"
    top="-5vh"
    :close-on-click-modal="false"
    :visible.sync="assign.dialogVisible">
    <el-form label-width="60px" label-position="top">
      <el-form-item label="字段:">
        <el-select v-model="assign.field" size="small" class="jc-filterby">
          <el-option label="-- 选择字段 --" value=""></el-option>
          @foreach ($fields as $field)
          <el-option label="{{ $field['label'] }}" value="{{ $field['field_id'] }}"></el-option>
          @endforeach
        </el-select>
      </el-form-item>
      <el-form-item label="值:">
        <el-input v-model="assign.value" type="textarea" rows="3"></el-input>
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
    <el-form class="md-layout md-gutter" label-position="top">
      @foreach ($fields as $field)
      <el-form-item class="md-layout-item md-size-50" label="{{ $field['label'] }}:" class="el-form-item">
        <el-input v-model="record.data.{{ $field['field_id'] }}" type="text" size="small" style="width: 100%"></el-input>
      </el-form-item>
      @endforeach
    </el-form>
    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click.stop="record.dialogVisible = false">取 消</el-button>
      <el-button size="small" type="primary" @click.stop="handleRecordDialogConfirm()">确 定</el-button>
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
        allRecords: @jjson(array_values($records)),
        presentRecords: [],
        selectedRecords: [],

        record: {
          data: @jjson($template),
          dialogVisible: false,
        },
        port: {
          raw: '',
          import: false,
          dialogVisible: false,
          delimiter: '|',
        },
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
        pagination: {
          total: 0,
          perPage: 20,
          currentPage: 1,
        },
        contextmenu: {
          target: null,
        },
      };
    },

    created() {
      this.allRecordsMap = this.mapRecords(this.allRecords);
      this.resetFilter();
      this.pagination.total = this.presentRecords.length;
      this._templateMd5 = this.getRecordMd5(@jjson($template));
    },

    computed: {
      // 当前页分展示的记录
      pageRecords() {
        const start = (this.pagination.currentPage-1)*this.pagination.perPage;
        return this.presentRecords.slice(start, start + this.pagination.perPage);
      },

      // 筛选按钮是否可用
      canFilter() {
        const f = this.filter;
        return f.field && f.method && (f.value.length || f.method === 'eq' || f.method === 'not_eq' || f.method === 'invalid-image');
      },
    },

    methods: {
      // 打开数据导入界面
      importRecords() {
        this.port.raw = null;
        this.port.import = true;
        this.port.dialogVisible = true;
      },

      // 生成导出数据，并打开数据导出界面
      exportRecords() {
        this.port.raw = this.recordsToText();
        this.port.import = false;
        this.port.dialogVisible = true;
      },

      // 导入数据
      handlePortDialogConfirm() {
        this.port.dialogVisible = false;
        if (! this.port.import) return;

        // 更新数据
        this.upsertRecords(this.textToRecords(this.port.raw, this.port.delimiter), '正在导入……');
      },

      // 删除指定记录
      removeRecord(row) {
        this.$confirm(`要删除选定规格吗？`, '删除规格', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          const loading = this.$loading({
            lock: true,
            text: '正在删除……',
            background: 'rgba(255, 255, 255, 0.7)',
          });

          let records = [row];
          if (this.selectedRecords.length) {
            records = this.selectedRecords.slice();
            this.selectedRecords = [];
          }

          axios.delete("{{ short_url('manage.specs.records.destroy', $spec_id) }}", {
              data: {records: records.map(rec => rec.id)}
            }).then(response => {
            records.forEach(rec => {
              this.presentRecords.splice(this.presentRecords.indexOf(rec), 1);
              this.allRecords.splice(this.allRecords.indexOf(rec), 1);
              this.allRecordsMap[rec.md5 || this.getRecordMd5(rec)] = null;
            });

            const p = this.pagination;
            p.total = this.presentRecords.length;
            if (p.currentPage > Math.ceil(p.total/p.perPage)) {
              p.currentPage = Math.ceil(p.total/p.perPage);
            }

            loading.close();
            this.$message.success('已删除 '+records.length+' 条记录');
          }).catch(error => {
            loading.close();
            console.error(error);
            this.$message.error('删除失败，请查看后台日志');
          });

        }).catch((err) => {});
      },

      // 批量赋值
      massAssign() {
        this.assign.dialogVisible = false;

        const field = this.assign.field;
        if (!this.selectedRecords.length || !field) return;

        const value = this.assign.value.trim();
        const records = _.cloneDeep(this.selectedRecords);
        this.selectedRecords = [];

        const _map = {};
        for (let i = 0, len = records.length; i < len; i++) {
          records[i][field] = value;
          const newMd5 = this.getRecordMd5(records[i]);
          if (newMd5 === this._templateMd5) {
            this.$message.error('批量赋值无法完成，会产生空记录');
            return;
          }
          if ((newMd5 !== records[i].md5 && this.allRecordsMap[newMd5]) || _map[newMd5]) {
            this.$message.error('批量赋值无法完成，会产生重复记录');
            return;
          }
          _map[newMd5] = true;
        }

        this.upsertRecords(records, '正在批量赋值……');
      },

      // 打开新增记录界面
      addRecord() {
        this.$set(this.$data.record, 'data', @json($template, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        this.record.dialogVisible = true;
      },

      // 打开编辑记录界面
      editRecord(row) {
        if (row.md5 == null) {
          row.md5 = this.getRecordMd5(row);
        }
        this.$set(this.$data.record, 'data', _.cloneDeep(row));
        this.record.dialogVisible = true;
      },

      // 保存新增或编辑后的记录
      handleRecordDialogConfirm() {
        // 关闭编辑面板
        this.record.dialogVisible = false;

        // 判断记录格式是否合法
        if (! this.isValidRecord(this.record.data)) {
          this.$message.warning('空数据，或已存在');
          return;
        }

        // 更新
        this.upsertRecords([this.record.data]);
      },

      // 更新或插入记录数据到后台
      upsertRecords(records, msg) {
        const loading = app.$loading({
          lock: true,
          text: msg || '正在保存数据到数据库……',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        if (!records.length) {
          loading.close();
          this.$message.success('成功更新 0 个记录');
          return;
        }

        return axios.post("{{ short_url('manage.specs.records.upsert', $spec_id) }}", {
          records: records,
        }).then(response => {
          this.syncRecords(records, this.mapRecords(Array.isArray(response.data) ? response.data : []));
          loading.close();
          this.$message.success('成功更新 '+records.length+' 个记录');
        }).catch(error => {
          loading.close();
          console.error(error);
          this.$message.error('失败，请查看后台日志');
        });
      },

      // 更新本地数据
      syncRecords(records, responseRecordsMap) {
        responseRecordsMap = responseRecordsMap || {};
        records.forEach(record => {
          const oldMd5 = record.md5;
          record.md5 = this.getRecordMd5(record);
          record.id = responseRecordsMap[record.md5] && responseRecordsMap[record.md5].id || null;

          const _record = this.allRecordsMap[oldMd5];
          // 更新现有记录
          if (_record) {
            @foreach (array_keys($fields) as $field_id)
            _record["{{ $field_id }}"] = record["{{ $field_id }}"];
            @endforeach
            _record.md5 = record.md5;
            _record.id = record.id || _record.id;
            this.allRecordsMap[oldMd5] = null;
            this.allRecordsMap[_record.md5] = _record;
          }

          // 插入新记录
          else {
            this.allRecordsMap[record.md5] = record;
            this.allRecords.unshift(record);
            if (! this.filter.filtered) {
              this.presentRecords.unshift(record);
            }
          }
        });
        this.pagination.total = this.presentRecords.length;

        if (this.filter.filtered) {
          this.applyFilter();
        }
      },

      // 从记录数据生成文本
      recordsToText() {
        let text = '';
        const records = this.selectedRecords.length ? this.selectedRecords : this.allRecords;
        records.forEach(record => {
          @foreach (array_keys($fields) as $field_id)
          text += (record["{{ $field_id }}"] == null ? '' : record["{{ $field_id }}"]) + '|';
          @endforeach
          text += '\n';
        });
        return text;
      },

      // 从文本数据批量生成记录
      textToRecords(text, delimiter) {
        delimiter = delimiter || '|';
        if (delimiter !== '|') {
          text = text.replaceAll(delimiter, '|');
        }

        const records = {};
        text.split(/[\n\r]+/).forEach(line => {
          line = line.replace(/^\s*\|/, '').replace(/\|\s*$/, '');
          if (line.length) {
            const data = line.split('|');
            const record = {
              @foreach (array_keys($fields) as $index => $field_id)
              {{ $field_id }}: data[{{ $index }}],
              @endforeach
            };
            if (this.isValidRecord(record)) {
              records[this.getRecordMd5(record)] = record;
            }
          }
        });

        return Object.values(records);
      },

      // 为记录集生成 MD5 键
      mapRecords(records) {
        const _map = {};
        records.forEach(record => {
          record.md5 = this.getRecordMd5(record);
          _map[record.md5] = record;
        });
        return _map;
      },

      // 展示右键菜单
      handleContextmenu(row, column, event) {
        this.contextmenu.target = row;
        this.$refs.contextmenu.show(event, this.$refs.contextmenu.$el);
      },

      // 响应列选择改变
      handleSelectionChange(selected) {
        this.selectedRecords = selected;
      },

      // 清除筛选
      resetFilter() {
        this.filter.field = '';
        this.filter.method = '';
        this.filter.value = '';

        if (this.filter.filtered) {
          this.selectedRecords = [];
          this.pagination.currentPage = 1;
          this.filter.filtered = false;
          this.$set(this.$data, 'presentRecords', this.allRecords.slice());
          this.pagination.total = this.allRecords.length;
        }
      },

      // 执行筛选动作
      applyFilter() {
        if (!this.canFilter) return;

        // 清空已选择的记录
        this.selectedRecords = [];
        this.pagination.currentPage = 1;

        // 筛选记录
        const records = this.filterRecords(this.filter.field, this.filter.method, this.filter.value);
        if (records.length === this.allRecords.length) {
          this.resetFilter();
          return;
        }
        this.filter.filtered = true;
        this.$set(this.$data, 'presentRecords', records);
        this.pagination.total = this.presentRecords.length;
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
          case 'invalid-image':
            filter = rec => rec.image_invalid;
            break;
          default: break;
        }

        return this.allRecords.filter(filter);
      },

      // 当前页改变
      handlePageChange(page) {
        this.pagination.currentPage = page;
        this.selectedRecords = [];
      },

      // 每页显示量改变
      handleSizeChange(size) {
        this.pagination.perPage = size;
      },

      // 获取一条记录的 md5 值作为唯一键
      getRecordMd5(rec) {
        let key = '';
        @foreach (array_keys($fields) as $field_id)
        key += "{{ $field_id }}:" + (rec["{{ $field_id }}"] == null ? '' : rec["{{ $field_id }}"]) + ',';
        @endforeach
        return md5(key);
      },

      // 判断一条记录是否有效：
      //  1. 不为空
      //  2. 不存在
      isValidRecord(rec) {
        const md5 = this.getRecordMd5(rec);
        return md5 !== this._templateMd5 && this.allRecordsMap[md5] == null;
      },
    },
  });
</script>
@endsection
