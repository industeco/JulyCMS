@extends('admin::layout')

@section('h1', '所有内容')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <a href="{{ short_route('nodes.nodetypes') }}" class="md-button md-dense md-raised md-primary md-theme-default">
        <div class="md-ripple"><div class="md-button-content">新建内容</div></div>
      </a>
      <button type="button" class="md-button md-dense md-raised md-primary md-theme-default"
        :disabled="!selected.length"
        @click="render()">
        <div class="md-ripple"><div class="md-button-content">生成 HTML</div></div>
      </button>
    </div>
    <div class="jc-options">
      <div class="jc-option" id="contents_filter">
        <label>筛选：</label>
        <el-select v-model="filterBy" size="small" class="jc-filterby" @change="handleFilterByChange">
          <el-option label="-- 不筛选 --" value=""></el-option>
          <el-option label="按标题" value="title"></el-option>
          <el-option label="按内容类型" value="node_type"></el-option>
          <el-option label="按网址" value="url"></el-option>
          <el-option label="按标签" value="tags"></el-option>
          @if (config('jc.multi_language'))
          <el-option label="按语言" value="langcode"></el-option>
          @endif
        </el-select>
        <el-input
          v-if="filterBy=='title'"
          v-model="filterValues.title"
          size="small"
          native-size="20"
          placeholder="内容标题"
          @input="filterContents"></el-input>
        <el-select v-if="filterBy=='node_type'" v-model="filterValues.node_type" size="small" placeholder="选择内容类型" @change="filterContents">
          <el-option
            v-for="(name, truename) in nodeTypes"
            :key="truename"
            :label="name"
            :value="truename">
          </el-option>
        </el-select>
        <el-select v-if="filterBy=='url'" v-model="filterValues.url" size="small" @change="filterContents">
          <el-option label="有 URL" :value="true"></el-option>
          <el-option label="没有 URL" :value="false"></el-option>
        </el-select>
        <el-select size="small"
          v-if="filterBy=='tags'"
          v-model="filterValues.tags"
          multiple
          @change="filterContents">
          <el-option v-for="tag in tags" :key="tag" :value="tag">@{{ tag }}</el-option>
        </el-select>
        @if (config('jc.multi_language'))
        <el-select size="small"
          v-if="filterBy=='langcode'"
          v-model="filterValues.langcode"
          @change="filterContents">
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
            @foreach ($catalogs as $truename => $name)
            <option value="{{ $truename }}">{{ $name }}</option>
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
        <el-table-column label="标签" prop="tags" width="auto">
          <template slot-scope="scope">
            <el-tag type="primary" size="small" effect="dark" v-for="tag in scope.row.tags" :key="tag">@{{ tag }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="建议模板" prop="templates" width="auto" v-if="showSuggestedTemplates">
          <template slot-scope="scope">
            <span class="jc-suggested-template" v-for="template in scope.row.templates" :key="template">@{{ template }}</span>
          </template>
        </el-table-column>
        <el-table-column label="类型" prop="node_type" width="120" sortable>
          <template slot-scope="scope">
            <span>@{{ nodeTypes[scope.row.node_type] }}</span>
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
              @if (config('jc.multi_language'))
              <a :href="getUrl('translate', scope.row.id)" title="翻译" class="md-button md-fab md-dense md-primary md-theme-default">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">translate</i></div></div>
              </a>
              @endif
              <button type="button" title="删除" class="md-button md-fab md-dense md-accent md-theme-default"
                @click="deleteNode(scope.row)">
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
      @if (config('jc.multi_language'))
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
        nodeTypes: @json($nodeTypes, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
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
          tags: [],
        },

        tags: @json($all_tags, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
        languages: @json($languages, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),

        editUrl: "{{ short_route('nodes.edit', '#id#') }}",
        deleteUrl: "{{ short_route('nodes.destroy', '#id#') }}",
        translateUrl: "{{ short_route('nodes.languages', '#id#') }}",
      };
    },

    created() {
      this.initial_data = clone(this.nodes);
    },

    methods: {
      diffForHumans(time) {
        return moment(time).fromNow();
      },

      getUrl(route, id) {
        switch (route) {
          case 'edit':
            return this.editUrl.replace('#id#', id)
          case 'translate':
            return this.translateUrl.replace('#id#', id)
        }
      },

      deleteNode(node) {
        if (! node) return;

        this.$confirm(`确定要删除内容？`, '删除内容', {
          confirmButtonText: '删除',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          const loading = app.$loading({
            lock: true,
            text: '正在删除 ...',
            background: 'rgba(255, 255, 255, 0.7)',
          });
          axios.delete(this.deleteUrl.replace('#id#', node.id)).then(function(response) {
            // console.log(response)
            loading.spinner = 'el-icon-success'
            loading.text = '已删除'
            window.location.reload()
          }).catch(function(error) {
            console.error(error)
          })
        }).catch();
      },

      handleContextmenu(row, column, event) {
        if (event.target.tagName==='A' || column.label==='操作') {
          return;
        }

        // this.targetNode = row;
        const menu = this.contextmenu;
        menu.target = row;
        menu.url = row.url;
        menu.editUrl = this.editUrl.replace('#id#', row.id);
        menu.translateUrl = this.translateUrl.replace('#id#', row.id);

        this.$refs.contextmenu.show(event);
      },

      handleSelectionChange(selected) {
        this.$set(this.$data, 'selected', selected);
      },

      handleFilterByChange(value) {
        if (! value) {
          this.$set(this.$data, 'nodes', this.initial_data);
          return;
        }

        switch (value) {
          case 'title':
          case 'node_type':
            this.filterValues[value] = null;
            this.$set(this.$data, 'nodes', this.initial_data);
            break;

          case 'url':
            this.filterValues.url = true;
            this.$set(this.$data, 'nodes', this.filterByUrl(true));
            break;
        }
      },

      filterContents(value) {
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
          case 'tags':
            nodes = this.filterByTags(value);
            break;
          case 'langcode':
            nodes = this.filterByLangcode(value);
            break;
        }
        this.$set(this.$data, 'nodes', nodes || clone(this.initial_data));
      },

      filterByTitle(value) {
        if (!value || !value.trim()) {
          return clone(this.initial_data);
        }

        const nodes = [];
        value = value.trim().toLowerCase();
        this.initial_data.forEach(node => {
          if (node.title.toLowerCase().indexOf(value) >= 0) {
            nodes.push(clone(node));
          }
        })

        return nodes;
      },

      filterByNodeType(value) {
        if (!value) {
          return clone(this.initial_data);
        }

        const nodes = [];
        this.initial_data.forEach(node => {
          if (node.node_type === value) {
            nodes.push(clone(node));
          }
        });

        return nodes;
      },

      filterByUrl(value) {
        const nodes = [];
        this.initial_data.forEach(node => {
          if ((value && node.url) || (!value && !node.url)) {
            nodes.push(clone(node));
          }
        });

        return nodes;
      },

      filterByTags() {
        const tags = this.filterValues.tags;
        if (! tags.length) {
          return clone(this.initial_data);
        }

        const nodes = [];
        this.initial_data.forEach(node => {
          if (node.tags.length) {
            for (let i = 0; i < tags.length; i++) {
              if (node.tags.indexOf(tags[i]) >= 0) {
                nodes.push(clone(node));
                break;
              }
            }
          }
        });

        return nodes;
      },

      filterByLangcode(langcode) {
        if (!value) {
          return clone(this.initial_data);
        }

        const nodes = [];
        this.initial_data.forEach(node => {
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

        axios.post("{{ short_route('nodes.render') }}", {nodes: nodes}).then((response) => {
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
