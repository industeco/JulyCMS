@extends('layout')

@section('h1', '所有内容')

@section('main_content')
  <div id="main_tools">
    <div class="jc-options">
      <div class="jc-option" id="contents_filter">
        <label>筛选：</label>
        <el-select v-model="filterBy" size="small" class="jc-filterby" @change="handleFilterByChange">
          <el-option label="-- 显示全部 --" value=""></el-option>
          <el-option label="按主题" value="subject"></el-option>
          <el-option label="按类型" value="mold"></el-option>
          @if (config('lang.multiple'))
          <el-option label="按语言" value="langcode"></el-option>
          @endif
        </el-select>
        <el-input
          v-if="filterBy=='subject'"
          v-model="filterValues.subject"
          size="small"
          native-size="20"
          placeholder="消息主题"
          @input="filterModels"></el-input>
        <el-select v-if="filterBy=='mold'" v-model="filterValues.mold" size="small" placeholder="选择内容类型" @change="filterModels">
          <el-option
            v-for="(label, id) in molds"
            :key="id"
            :label="label"
            :value="id">
          </el-option>
        </el-select>
        @if (config('lang.multiple'))
        <el-select size="small"
          v-if="filterBy=='langcode'"
          v-model="filterValues.langcode"
          @change="filterModels">
          <el-option v-for="(langname, langcode) in languages" :key="langcode" :value="langcode">@{{ langname }}</el-option>
        </el-select>
        @endif
      </div>
    </div>
    {{-- <div class="jc-translate"></div> --}}
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table with-operators" :data="models" @row-contextmenu="handleContextmenu">
        <el-table-column label="ID" prop="id" width="100" sortable></el-table-column>
        <el-table-column label="主题" prop="subject" width="auto" sortable>
          <template slot-scope="scope">
            <a :href="getUrl('show', scope.row.id)" target="_blank">@{{ scope.row.subject }}</a>
          </template>
        </el-table-column>
        <el-table-column label="类型" prop="mold_id" width="120" sortable>
          <template slot-scope="scope">
            <span>@{{ molds[scope.row.mold_id] }}</span>
          </template>
        </el-table-column>
        <el-table-column label="发送状态" prop="mold_id" width="200" sortable>
          <template slot-scope="scope">
            <span>@{{ scope.row.is_sent ? '已发送' : '未发送' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" prop="created_at" width="240" sortable>
          <template slot-scope="scope">
            <span>@{{ diffForHumans(scope.row.created_at) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200">
          <template slot-scope="scope">
            <div class="jc-operators">
              <a :href="getUrl('show', scope.row.id)" title="查看" class="md-button md-fab md-dense md-primary md-theme-default">
                <div class="md-ripple">
                  <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">visibility</i></div>
                </div>
              </a>
              <button type="button" title="删除" class="md-button md-fab md-dense md-accent md-theme-default"
                @click.stop="deleteModel(scope.row)">
                <div class="md-ripple">
                  <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">remove</i></div>
                </div>
              </button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <jc-contextmenu ref="contextmenu">
      <x-menu-item title="查看" icon="visibility" target="_blank" href="contextmenu.url" />
      <x-menu-item title="删除" icon="remove_circle" theme="md-accent" click="deleteModel(contextmenu.target)" />
    </jc-contextmenu>
  </div>
@endsection

@section('script')
<script>
  let app = new Vue({
    el: '#main_content',

    data() {
      return {
        models: @jjson($models->values()->all()),
        molds: @jjson($context['molds']),
        contextmenu: {
          target: null,
          showUrl: null,
        },

        filterBy: '',
        filterValues: {
          subject: null,
          mold: null,
          langcode: "{{ $context['langcode'] }}",
        },

        // {{-- tags: @json($tags, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), --}}
        languages: @jjson($context['languages']),

        showUrl: "{{ short_url('messages.show', '_ID_') }}",
        deleteUrl: "{{ short_url('messages.destroy', '_ID_') }}",
      };
    },

    created() {
      this.original_models = this.models.slice();
    },

    methods: {
      diffForHumans(time) {
        return moment(time).fromNow();
      },

      getUrl(route, id) {
        switch (route) {
          case 'show':
            return this.showUrl.replace('_ID_', id);
        }
      },

      deleteModel(model) {
        if (! model) return;

        // console.log(this.deleteUrl.replace('_ID_', model.id));
        this.$confirm(`确定要删除内容？`, '删除内容', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning',
        }).then(() => {
          const loading = app.$loading({
            lock: true,
            text: '正在删除 ...',
            background: 'rgba(255, 255, 255, 0.7)',
          });
          axios.delete(this.deleteUrl.replace('_ID_', model.id)).then(function(response) {
            // console.log(response)
            loading.spinner = 'el-icon-success';
            loading.text = '已删除';
            window.location.reload();
          }).catch(function(error) {
            console.error(error);
          });
        }).catch(()=>{});
      },

      handleContextmenu(row, column, event) {
        if (event.target.tagName==='A' || column.label==='操作') {
          return;
        }

        // this.targetNode = row;
        const menu = this.contextmenu;
        menu.target = row;
        menu.url = row.url;
        menu.showUrl = this.showUrl.replace('_ID_', row.id);

        this.$refs.contextmenu.show(event, this.$refs.contextmenu.$el);
      },

      handleFilterByChange(value) {
        if (value) {
          this.filterValues[value] = null;
        }
        this.$set(this.$data, 'models', this.original_models.slice());
      },

      filterModels(value) {
        let models = null;
        switch (this.filterBy) {
          case 'subject':
            models = this.filterBySubject(value);
          break;
          case 'mold':
            models = this.filterByMold(value);
          break;
          case 'langcode':
            models = this.filterByLangcode(value);
          break;
        }
        this.$set(this.$data, 'models', models || this.original_models.slice());
      },

      filterBySubject(value) {
        if (!value || !value.trim()) {
          return this.original_models.slice();
        }

        const models = [];
        value = value.trim().toLowerCase();
        this.original_models.forEach(model => {
          if (model.subject.toLowerCase().indexOf(value) >= 0) {
            models.push(model);
          }
        });

        return models;
      },

      filterByMold(value) {
        if (!value) {
          return this.original_models.slice();
        }

        const models = [];
        this.original_models.forEach(model => {
          if (model.mold_id === value) {
            models.push(model);
          }
        });

        return models;
      },

      filterByLangcode(langcode) {
        if (!value) {
          return this.original_models.slice();
        }

        const models = [];
        this.original_models.forEach(model => {
          if (model.langcode === langcode) {
            models.push(model);
          }
        });

        return models;
      },
    },
  });
</script>
@endsection
