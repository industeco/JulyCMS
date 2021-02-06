@extends('layout')

@section('h1', '标签管理')

@section('main_content')
  <div id="main_tools">
    <div class="jc-btn-group">
      <button type="button" class="md-button md-dense md-raised md-primary md-theme-default"
        @click="createTag">
        <div class="md-ripple"><div class="md-button-content">新建</div></div>
      </button>
    </div>
  </div>
  <div id="main_list">
    <div class="jc-table-wrapper">
      <el-table class="jc-table with-operators"
        ref="tags"
        :data="tags"
        row-key="tag"
        default-expand-all
        :tree-props="{children: 'children'}"
        @row-contextmenu="handleRowRightClick"
        @row-click="handleRowClick">
        <el-table-column label="序号" type="index" width="80"></el-table-column>
        <el-table-column label="标签" prop="tag" width="auto" sortable></el-table-column>
        @if (config('language.multiple'))
        <el-table-column label="标签原文" prop="original_tag" width="auto" sortable></el-table-column>
        @endif
        <el-table-column label="页面可见" prop="is_show" width="150" sortable>
          <template slot-scope="scope">
            <el-switch v-model="scope.row.is_show" @click.stop="toggleTagShow(scope.row)"></el-switch>
          </template>
        </el-table-column>
        <el-table-column label="语言" prop="langcode" width="120" sortable></el-table-column>
        <el-table-column label="上次修改" prop="updated_at" width="240" sortable>
          <template slot-scope="scope">
            <span>@{{ diffForHumans(scope.row.updated_at) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200">
          <template slot-scope="scope">
            <div class="jc-operators">
              @if (config('language.multiple'))
              <button title="变更原文" class="md-button md-fab md-dense md-primary md-theme-default"
                @click.stop="changeOriginal(scope.row)">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div></div>
              </button>
              @endif
              <button type="button" title="删除" class="md-button md-fab md-dense md-accent md-theme-default"
                @click.stop="deleteTag(scope.row)">
                <div class="md-ripple"><div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">remove</i></div></div>
              </button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <div id="main_form_bottom" class="is-button-item">
      <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click="saveChange">
        <div class="md-button-content">保存更改</div>
      </button>
    </div>
    @if (config('language.multiple'))
    <el-dialog
      ref="change_tag_original"
      title="变更原文"
      top="-5vh"
      width="400px"
      :close-on-click-modal="false"
      :close-on-press-escape="false"
      :visible.sync="changeOriginalDialogVisible">
      <el-select v-model="currentOriginal" size="small" filterable style="width: 100%">
        <el-option
          v-for="tag in originalList"
          :key="tag"
          :value="tag">
        </el-option>
      </el-select>
      <span slot="footer" class="dialog-footer">
        <button type="button" class="md-button md-raised md-dense md-theme-default"
          @click="changeOriginalDialogVisible = false">
          <div class="md-ripple">
            <div class="md-button-content">取 消</div>
          </div>
        </button>
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
          @click="handleOriginalChangeConfirm">
          <div class="md-ripple">
            <div class="md-button-content">确 定</div>
          </div>
        </button>
      </span>
    </el-dialog>
    @endif
    <el-dialog
      ref="create_tag"
      title="新建标签"
      top="-5vh"
      width="400px"
      :close-on-click-modal="false"
      :close-on-press-escape="false"
      :visible.sync="createTagDialogVisible">
      <el-input
        ref="tag_input"
        v-model="newTag"
        placeholder="输入标签名"
        style="width: 100%"
        v-focus="createTagDialogVisible"
        @keyup.enter.native="handleCreateConfirm"></el-input>
      <span slot="footer" class="dialog-footer">
        <button type="button" class="md-button md-raised md-dense md-theme-default"
          @click="createTagDialogVisible = false">
          <div class="md-ripple">
            <div class="md-button-content">取 消</div>
          </div>
        </button>
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
          @click="handleCreateConfirm">
          <div class="md-ripple">
            <div class="md-button-content">确 定</div>
          </div>
        </button>
      </span>
    </el-dialog>
    <jc-contextmenu ref="contextmenu">
      @if (config('language.multiple'))
      <li class="md-list-item">
        <div class="md-list-item-container md-button-clean" @click.stop="changeOriginal(currentTag)">
          <div class="md-list-item-content md-ripple">
            <i class="md-icon md-icon-font md-primary md-theme-default">edit</i>
            <span class="md-list-item-text">变更原文</span>
          </div>
        </div>
      </li>
      @endif
      <li class="md-list-item">
        <div class="md-list-item-container md-button-clean" @click.stop="deleteTag(currentTag)">
          <div class="md-list-item-content md-ripple">
            <i class="md-icon md-icon-font md-accent md-theme-default">remove_circle</i>
            <span class="md-list-item-text">删除标签</span>
          </div>
        </div>
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
        tagsMap: {!! $tags ? json_encode($tags, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) : '{}' !!},
        tags: [],

        currentTag: null,
        currentOriginal: null,
        originalList: [],
        changeOriginalDialogVisible: false,

        newTag: null,
        createTagDialogVisible: false,
      };
    },

    created() {
      this.initial_data = clone(this.tagsMap);
      this.tags = this.getTags();
    },

    directives: {
      focus: {
        // 指令的定义
        inserted: function (el, value) {
          if (value) {
            el = el.querySelector('input');
            el.focus();
          }
        },
        update: function (el, value) {
          if (value) {
            el = el.querySelector('input');
            el.focus();
          }
        },
      }
    },

    methods: {
      getTags() {
        const tags = [];
        const _map = clone(this.tagsMap);
        for (const key in _map) {
          const tag = _map[key];
          if (! tag) {
            continue;
          }
          if (tag.tag === tag.original_tag) {
            tags.push(tag);
          } else {
            const original = _map[tag.original_tag];
            if (!original.children) {
              original.children = [];
            }
            original.children.push(tag);
          }
        }
        return tags;
      },

      handleRowClick(row, column, event) {
        if (column.property === 'tag') {
          this.$refs.tags.toggleRowExpansion(row);
        }
      },

      handleRowRightClick(row, column, event) {
        this.currentTag = row;
        this.$refs.contextmenu.show(event, this.$refs.contextmenu.$el);
      },

      diffForHumans(time) {
        return moment(time).fromNow();
      },

      toggleTagShow(tag) {
        tag.is_show = !tag.is_show;
        this.tagsMap[tag.tag].is_show = tag.is_show;
      },

      changeOriginal(tag) {
        tag = tag || this.currentTag;
        if (!tag) return;

        const tags = [];
        if (tag.tag !== tag.original_tag) {
          tags.push(tag.tag);
        }
        this.tags.forEach(t => {
          tags.push(t.tag);
        })
        this.$set(this.$data, 'originalList', tags);

        this.currentTag = tag;
        this.currentOriginal = tag.original_tag;
        this.changeOriginalDialogVisible = true;
      },

      handleOriginalChangeConfirm() {
        const _tag = this.currentTag;
        if (_tag.original_tag !== this.currentOriginal) {
          const tag = this.tagsMap[_tag.tag];
          tag.original_tag = this.currentOriginal;
          if (_tag.children) {
            _tag.children.forEach(tag => {
              this.tagsMap[tag.tag].original_tag = this.currentOriginal;
            });
          }
          this.$set(this.$data, 'tags', this.getTags());
        }

        this.changeOriginalDialogVisible = false;
      },

      handleCreateConfirm() {
        if (!this.newTag || !this.newTag.trim()) {
          this.createTagDialogVisible = false;
          return;
        }
        const newTag = this.newTag.trim();
        if (this.tagsMap[newTag]) {
          this.$message.info(`标签 ${newTag} 已存在`);
        } else {
          if (this.initial_data[newTag]) {
            this.tagsMap[newTag] = clone(this.initial_data[newTag]);
          } else {
            this.tagsMap[newTag] = {
              tag: newTag,
              is_show: true,
              original_tag: newTag,
              updated_at: Date.now(),
              langcode: '{{ $langcode }}',
            };
          }
          this.$set(this.$data, 'tags', this.getTags());
        }
        this.newTag = null;
        this.createTagDialogVisible = false;
      },

      deleteTag(tag) {
        tag = tag || this.currentTag;
        if (! tag) return;

        if (this.tagsMap[tag.tag]) {
          this.$confirm(`确定要删除 ${tag.tag} 标签吗？（注意！如果有翻译版本会被一并删除）`, '删除标签', {
            confirmButtonText: '删除',
            cancelButtonText: '取消',
            type: 'warning',
          }).then(() => {
            this.tagsMap[tag.tag] = null;
            if (tag.children) {
              tag.children.forEach(item => {
                this.tagsMap[item.tag] = null;
              });
            }
            this.$set(this.$data, 'tags', this.getTags());
          }).catch((err)=>{});
        }
      },

      createTag() {
        this.createTagDialogVisible = true;
      },

      getChanged() {
        const changed = {};
        for (const key in this.tagsMap) {
          const v1 = this.initial_data[key];
          const v2 = this.tagsMap[key];
          if (!v1 && !v2) {
            continue;
          }
          if (!v1 || !v2 || !isEqual(v1, v2)) {
            changed[key] = clone(v2);
          }
        }
        return changed;
      },

      saveChange() {
        const changed = this.getChanged();
        if (isEmptyObject(changed)) {
          this.$message.info('未作任何更改');
          return;
        }
        this.$confirm(`确定要保存对标签的修改？`, '保存', {
          confirmButtonText: '是的',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          const loading = this.$loading({
            lock: true,
            text: '正在保存 ...',
            background: 'rgba(255, 255, 255, 0.7)',
          });
          axios.post("{{ short_url('tags.update') }}", {changed:changed}).then(function(response) {
            console.log(response);
            loading.close();
            this.$message.success('保存成功');
            // window.location.reload()
          }).catch(function(error) {
            console.error(error)
            loading.close();
            this.$message.error('发生错误，可查看控制台');
          })
        }).catch(()=>{});
      },
    },
  });
</script>
@endsection
