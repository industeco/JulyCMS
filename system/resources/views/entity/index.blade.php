@extends('layout')

@section('h1', '所有内容')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="{{ short_url('nodes.choose_node_type') }}" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建内容</div></div>
      </a>
      <button type="button" class="md-button md-dense md-raised md-primary md-theme-default"
        :disabled="!selected.length"
        @click.stop="render()">
        <div class="md-ripple"><div class="md-button-content">生成 HTML</div></div>
      </button>
    </div>
    <div class="jc-options">
      <div class="jc-option" id="contents_filter">
        <label>过滤：</label>
        <el-select v-model="filterBy" size="small" class="jc-filterby" @change="handleFilterByChange">
          <el-option label="-- 显示全部 --" value=""></el-option>
          <el-option label="按标题" value="title"></el-option>
          <el-option label="按类型" value="node_type"></el-option>
          <el-option label="按网址" value="url"></el-option>
          <el-option label="按颜色" value="color"></el-option>
          @if (config('language.multiple'))
          <el-option label="按语言" value="langcode"></el-option>
          @endif
        </el-select>
        <el-input
          v-if="filterBy=='title'"
          v-model="filterValues.title"
          size="small"
          native-size="20"
          placeholder="内容标题"
          @input="filterNodes"></el-input>
        <el-select v-if="filterBy=='node_type'" v-model="filterValues.node_type" size="small" placeholder="选择内容类型" @change="filterNodes">
          <el-option
            v-for="(label, id) in nodeTypes"
            :key="id"
            :label="label"
            :value="id">
          </el-option>
        </el-select>
        <el-select v-if="filterBy=='url'" v-model="filterValues.url" size="small" @change="filterNodes">
          <el-option label="有 URL" :value="true"></el-option>
          <el-option label="没有 URL" :value="false"></el-option>
        </el-select>
        <el-select size="small"
          v-if="filterBy=='color'"
          v-model="filterValues.color"
          @change="filterNodes">
          <el-option value="is_red">红</el-option>
          <el-option value="is_green">绿</el-option>
          <el-option value="is_blue">蓝</el-option>
        </el-select>
        @if (config('language.multiple'))
        <el-select size="small"
          v-if="filterBy=='langcode'"
          v-model="filterValues.langcode"
          @change="filterNodes">
          <el-option v-for="(langname, langcode) in languages" :key="langcode" :value="langcode">@{{ langname }}</el-option>
        </el-select>
        @endif
      </div>
      <div class="jc-option">
        <label>显示『建议模板』：</label>
        <el-switch v-model="showSuggestedTemplates"></el-switch>
      </div>
      <div class="jc-option">
        <label for="nodes_view">呈现方式：</label>
        <select id="nodes_view" class="jc-select">
          <option value="" selected>列表</option>
          <optgroup label="------- 目录 -------">
            @foreach ($catalogs as $id => $label)
            <option value="{{ $id }}">{{ $label }}</option>
            @endforeach
          </optgroup>
        </select>
      </div>
    </div>
    {{-- <div class="jc-translate"></div> --}}
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table with-operators"
        :data="nodes"
        @row-contextmenu="handleContextmenu"
        @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="50"></el-table-column>
        <el-table-column label="ID" prop="id" width="100" sortable></el-table-column>
        <el-table-column label="标题" prop="title" width="auto" sortable>
          <template slot-scope="scope">
            <a v-if="scope.row.url" :href="scope.row.url" target="_blank">@{{ scope.row.title }}</a>
            <span v-else>@{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="颜色" prop="color" width="240">
          <template slot-scope="scope">
            <el-switch style="margin-right: 1em" v-model="scope.row.is_red" active-color="#F44336" inactive-color="#FFCDD2"></el-switch>
            <el-switch style="margin-right: 1em" v-model="scope.row.is_green" active-color="#4caf50" inactive-color="#C8E6C9"></el-switch>
            <el-switch style="margin-right: 1em" v-model="scope.row.is_blue" active-color="#2196F3" inactive-color="#BBDEFB"></el-switch>
          </template>
        </el-table-column>
        <el-table-column label="建议模板" prop="templates" width="auto" v-if="showSuggestedTemplates">
          <template slot-scope="scope">
            <span class="jc-suggested-template" v-for="template in scope.row.templates" :key="template">@{{ template }}</span>
          </template>
        </el-table-column>
        <el-table-column label="类型" prop="node_type_id" width="120" sortable>
          <template slot-scope="scope">
            <span>@{{ nodeTypes[scope.row.node_type_id] }}</span>
          </template>
        </el-table-column>
        </el-table-column>
        <el-table-column label="上次修改" prop="updated_at" width="240" sortable>
          <template slot-scope="scope">
            <span>@{{ diffForHumans(scope.row.updated_at) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200">
          <template slot-scope="scope">
            <div class="jc-operators">
              <a :href="getUrl('edit', scope.row.id)" title="编辑" class="md-button md-fab md-dense md-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
              </a>
              @if (config('language.multiple'))
              <a :href="getUrl('translate', scope.row.id)" title="翻译" class="md-button md-fab md-dense md-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">translate</i></div></div>
              </a>
              @endif
              <button type="button" title="删除" class="md-button md-fab md-dense md-accent md-theme-default"
                @click.stop="deleteNode(scope.row)">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">remove</i></div></div>
              </button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <jc-contextmenu ref="contextmenu">
      <li class="md-list-item">
        <a :href="contextmenu.editUrl" class="md-list-item-link md-list-item-container md-button-clean">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-primary md-theme-default">edit</i>
            <span class="md-list-item-text">编辑</span>
          </div>
        </a>
      </li>
      @if (config('language.multiple'))
      <li class="md-list-item">
        <a :href="contextmenu.translateUrl" class="md-list-item-link md-list-item-container md-button-clean">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-primary md-theme-default">translate</i>
            <span class="md-list-item-text">翻译</span>
          </div>
        </a>
      </li>
      @endif
      <li class="md-list-item">
        <div class="md-list-item-container md-button-clean" @click.stop="deleteNode(contextmenu.target)">
          <div class="md-list-item-content md-ripple">
            <i class="md-icon md-icon-font md-accent md-theme-default">remove_circle</i>
            <span class="md-list-item-text">删除</span>
          </div>
        </div>
      </li>
      <li class="md-list-item">
        <div class="md-list-item-container md-button-clean" @click.stop="render(contextmenu.target)">
          <div class="md-list-item-content md-ripple">
            <i class="md-icon md-icon-font md-theme-default">description</i>
            <span class="md-list-item-text">生成 HTML</span>
          </div>
        </div>
      </li>
      <li class="md-list-item">
        <a :href="contextmenu.url" target="_blank" class="md-list-item-link md-list-item-container md-button-clean">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-theme-default">visibility</i>
            <span class="md-list-item-text">查看页面</span>
          </div>
        </a>
      </li>
    </jc-contextmenu>
  </div>
@endsection

@section('script')
<script>
  let app = new Vue({
    el: '#main_content',

    data() {
      return {
        nodes: @json(array_values($nodes), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        nodeTypes: @json($node_types, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        selected: [],
        showSuggestedTemplates: false,
        contextmenu: {
          target: null,
          url: null,
          editUrl: null,
          translateUrl: null,
        },

        filterBy: '',
        filterValues: {
          title: null,
          node_type: null,
          url: true,
          langcode: "{{ langcode('content') }}",
          color: '',
        },

        // {{-- tags: @json($tags, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), --}}
        languages: @json($languages, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),

        editUrl: "{{ short_url('nodes.edit', '%id%') }}",
        deleteUrl: "{{ short_url('nodes.destroy', '%id%') }}",
        translateUrl: "{{ short_url('nodes.languages', '%id%') }}",
      };
    },

    created() {
      this.initialData = clone(this.nodes);
    },

    methods: {
      diffForHumans(time) {
        return moment(time).fromNow();
      },

      getUrl(route, id) {
        switch (route) {
          case 'edit':
            return this.editUrl.replace('%id%', id);
          case 'translate':
            return this.translateUrl.replace('%id%', id);
        }
      },

      deleteNode(node) {
        if (! node) return;

        // console.log(this.deleteUrl.replace('%id%', node.id));
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
          axios.delete(this.deleteUrl.replace('%id%', node.id)).then(function(response) {
            // console.log(response)
            loading.spinner = 'el-icon-success';
            loading.text = '已删除';
            window.location.reload();
          }).catch(function(error) {
            console.error(error);
          })
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
        menu.editUrl = this.editUrl.replace('%id%', row.id);
        menu.translateUrl = this.translateUrl.replace('%id%', row.id);

        this.$refs.contextmenu.show(event, this.$refs.contextmenu.$el);
      },

      handleSelectionChange(selected) {
        this.$set(this.$data, 'selected', selected);
      },

      handleFilterByChange(value) {
        if (value === 'url') {
          this.filterValues.url = true;
          this.$set(this.$data, 'nodes', this.filterByUrl(true));
        } else {
          if (value) {
            this.filterValues[value] = null;
          }
          this.$set(this.$data, 'nodes', clone(this.initialData));
        }
      },

      filterNodes(value) {
        let nodes = null;
        switch (this.filterBy) {
          case 'title':
            nodes = this.filterByTitle(value);
          break;
          case 'node_type':
            nodes = this.filterByNodeType(value);
          break;
          case 'url':
            nodes = this.filterByUrl(value);
          break;
          case 'color':
            nodes = this.filterByColor(value);
          break;
          case 'langcode':
            nodes = this.filterByLangcode(value);
          break;
        }
        this.$set(this.$data, 'nodes', nodes || clone(this.initialData));
      },

      filterByTitle(value) {
        if (!value || !value.trim()) {
          return clone(this.initialData);
        }

        const nodes = [];
        value = value.trim().toLowerCase();
        this.initialData.forEach(node => {
          if (node.title.toLowerCase().indexOf(value) >= 0) {
            nodes.push(clone(node));
          }
        });

        return nodes;
      },

      filterByNodeType(value) {
        if (!value) {
          return clone(this.initialData);
        }

        const nodes = [];
        this.initialData.forEach(node => {
          if (node.node_type_id === value) {
            nodes.push(clone(node));
          }
        });

        return nodes;
      },

      filterByUrl(value) {
        const nodes = [];
        this.initialData.forEach(node => {
          if ((value && node.url) || (!value && !node.url)) {
            nodes.push(clone(node));
          }
        });

        return nodes;
      },

      filterByColor(value) {
        const nodes = [];
        this.initialData.forEach(node => {
          if (node[value]) {
            nodes.push(clone(node));
          }
        });

        return nodes;
      },

      filterByLangcode(langcode) {
        if (!value) {
          return clone(this.initialData);
        }

        const nodes = [];
        this.initialData.forEach(node => {
          if (node.langcode === langcode) {
            nodes.push(clone(node));
          }
        });

        return nodes;
      },

      render(node) {
        const nodes = [];
        if (node) {
          nodes.push(node.id);
        } else {
          this.selected.forEach(element => {
            nodes.push(element.id);
          });
        }

        if (! nodes.length) {
          this.$message.info('未选中任何内容');
          return;
        }

        const loading = this.$loading({
          lock: true,
          text: '正在生成 ...',
          background: 'rgba(255, 255, 255, 0.7)',
        });

        axios.post("{{ short_url('nodes.render') }}", {nodes: nodes}).then((response) => {
          // console.log(response)
          loading.close();
          this.$message.success('生成完成');
        }).catch(err => {
          loading.close();
          console.error(err);
          this.$message.error('发生错误');
        });
      },
    },
  });
</script>
@endsection
