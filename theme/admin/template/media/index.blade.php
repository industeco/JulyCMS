@extends('admin::layout')

@section('h1', '文件管理')

@section('main_content')
  <div id="media-manager">
    <div id="media-manager__folders-tree" class="md-elevation-2">
      <h2>目录：</h2>
      <el-tree
        ref="folders"
        :data="folders"
        :props="treeProps"
        node-key="path"
        :node-class="getNodeClass"
        @node-click="changeCurrentPath"
        default-expand-all
        :expand-on-click-node="false"></el-tree>
    </div>
    <div id="media-manager__main">
      <div id="media-manager__toolbar">
        <div id="media-manager__filter" class="media-manager__tool">
          <label>筛选：</label>
          <el-select
            id="media-manager__filter-by"
            v-model="filterBy"
            size="small"
            @change="handleFilterTypeChange"
            :disabled="!currentPath">
            <el-option label="文件名" value="name"></el-option>
            <el-option label="文件大于" value="size_ge" disabled></el-option>
            <el-option label="文件小于" value="size_le" disabled></el-option>
            <el-option label="宽高比" value="ratio"></el-option>
            <el-option label="宽度大于" value="width_ge" disabled></el-option>
            <el-option label="宽度小于" value="width_le" disabled></el-option>
          </el-select>
          <el-input
            v-if="filterBy=='name'"
            v-model="filterValues.name"
            size="small"
            native-size="24"
            placeholder="输入文件名"
            @input="filterByName"
            :disabled="!currentPath"></el-input>
          <el-autocomplete
            v-if="filterBy=='ratio'"
            v-model="filterValues.ratio"
            size="small"
            native-size="24"
            :fetch-suggestions="getDefaultRatios"
            placeholder="仅对图片有效"
            @input="handleRatioInput"
            @select="handleRatioSelect"
            :disabled="!currentPath">
            <template slot-scope="{ item }">
              <span>@{{ item.label }} (@{{ item.value }})</span>
            </template>
          </el-autocomplete>
        </div>
        <div id="media-manager__sort" class="media-manager__tool" v-if="displayMode != 'table'" :disabled="!currentPath">
          <label for="">排序：</label>
          <el-select v-model="orderby" size="small" @change="sortFiles">
            <el-option label="a - z" value="name.asc"></el-option>
            <el-option label="z - a" value="name.desc"></el-option>
            <el-option label="小 - 大" value="size.asc"></el-option>
            <el-option label="大 - 小" value="size.desc"></el-option>
            <el-option label="新 - 旧" value="modified.desc"></el-option>
            <el-option label="旧 - 新" value="modified.asc"></el-option>
          </el-select>
        </div>
        <div id="media-manager__display" class="media-manager__tool">
          <label for="">呈现方式：</label>
          <el-select v-model="displayMode" size="small" :disabled="!currentPath">
            <el-option label="列表" value="table"></el-option>
            <el-option label="卡片" value="card"></el-option>
            <el-option label="网格" value="grid"></el-option>
          </el-select>
        </div>
        <button type="button" class="media-manager__tool md-button md-raised md-small md-primary md-theme-default"
          @click="selectAll" :disabled="!currentPath || loading">
          <div class="md-ripple">
            <div class="md-button-content">全选</div>
          </div>
        </button>
        <button type="button" class="media-manager__tool md-button md-raised md-small md-primary md-theme-default"
          @click="reloadCurrentPath" :disabled="!currentPath || loading">
          <div class="md-ripple">
            <div class="md-button-content">刷新</div>
          </div>
        </button>
      </div>
      <div id="media-manager__files">
        <div v-if="!currentPath || !currentFiles.length"
          id="media-manager__files-empty">
          <span v-if="loading" class="el-tree-node__loading-icon el-icon-loading"></span>
          <span v-else>(空)</span>
        </div>
        <div v-if="currentPath && currentFiles.length && displayMode=='table'"
          class="jc-table-wrapper files-container md-scrollbar md-theme-default">
          <el-table
            ref="files_table"
            id="media-manager__files-table"
            class="jc-table"
            :data="currentFiles"
            @hook:mounted="makeSelection"
            @selection-change="handleSelectionChange">
            <el-table-column
              type="selection"
              width="55">
            </el-table-column>
            <el-table-column
              label="缩略图"
              width="120">
              <template slot-scope="scope">
                <div class="jc-media__thumb" v-html="getThumb(scope.row)"></div>
              </template>
            </el-table-column>
            <el-table-column
              prop="name"
              label="文件名"
              sortable>
            </el-table-column>
            <el-table-column
              label="尺寸"
              width="100">
              <template slot-scope="scope">
                <span class="jc-media__aspect">@{{ getAspect(scope.row) }}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="size"
              label="大小"
              width="100"
              sortable>
              <template slot-scope="scope">
                <span class="jc-media__size">@{{ getFileSize(scope.row) }}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="modified"
              label="修改时间"
              width="160"
              sortable>
              <template slot-scope="scope">
                <span class="jc-media__modified">@{{ getLastModified(scope.row) }}</span>
              </template>
            </el-table-column>
            <el-table-column
              class="jc-operators-col"
              prop="modified"
              label="操作"
              width="120">
              <template slot-scope="scope">
                <div class="jc-operators">
                  <button type="button" title="改名" class="md-button md-fab md-mini md-theme-default"
                    @click="renameFile(scope.row)">
                    <div class="md-ripple">
                      <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div>
                    </div>
                  </button>
                  <a :href="getPath(scope.row)" :download="scope.row.name" title="下载" class="md-button md-fab md-mini md-theme-default">
                    <div class="md-ripple">
                      <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">get_app</i></div>
                    </div>
                  </a>
                  <button type="button" title="删除" class="md-button md-fab md-mini md-theme-default"
                    @click="deleteFile(scope.row)">
                    <div class="md-ripple">
                      <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div>
                    </div>
                  </button>
                </div>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <div v-if="currentPath && currentFiles.length && displayMode=='card'"
          class="files-container md-scrollbar md-theme-default">
          <ul id="media-manager__files-card">
            <li v-for="file in currentFiles" :key="file.name"
              :class="{'jc-media-item':true, 'is-selected':selected[file.name]}"
              @click="toggleSelect(file, ...arguments)"
              @dblclick="selectExit(file, ...arguments)">
              <div class="jc-media-item-checkbox md-elevation-2">
                <i class="md-icon md-icon-font md-theme-default">check</i>
              </div>
              <div class="jc-media__thumb" v-html="getThumb(file)"></div>
              <div class="jc-media__info">
                <div class="jc-media__name">@{{ file.name }}</div>
                <div class="jc-media__image-size" v-if="file.width && file.height">尺寸：@{{ getAspect(file) }}</div>
                <div class="jc-media__size">大小：@{{ getFileSize(file) }}</div>
                <div class="jc-media__modified">修改时间：@{{ getLastModified(file) }}</div>
                <div class="jc-operators">
                  <button type="button" title="改名" class="md-button md-fab md-mini md-theme-default"
                    @click="renameFile(file, ...arguments)">
                    <div class="md-ripple">
                      <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div>
                    </div>
                  </button>
                  <a :href="getPath(file)" :download="file.name" title="下载" class="md-button md-fab md-mini md-theme-default"
                    @click="downloadFile(file, ...arguments)">
                    <div class="md-ripple">
                      <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">get_app</i></div>
                    </div>
                  </a>
                  <button type="button" title="删除" class="md-button md-fab md-mini md-theme-default"
                    @click="deleteFile(file, ...arguments)">
                    <div class="md-ripple">
                      <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div>
                    </div>
                  </button>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <div v-if="currentPath && currentFiles.length && displayMode=='grid'"
          class="files-container md-scrollbar md-theme-default">
          <ul id="media-manager__files-grid">
            <li v-for="file in currentFiles" :key="file.name"
              :class="{'jc-media-item':true, 'is-selected':selected[file.name]}"
              @click="toggleSelect(file, ...arguments)"
              @dblclick="selectExit(file)">
              <div class="jc-media-item-checkbox md-elevation-2">
                <i class="md-icon md-icon-font md-theme-default">check</i>
              </div>
              <div class="jc-media__thumb" v-html="getThumb(file)"></div>
              <div class="jc-media__info">
                <div class="jc-media__image-size" v-if="file.width && file.height">尺寸：@{{ getAspect(file) }}</div>
                <div class="jc-media__size">大小：@{{ getFileSize(file) }}</div>
                <div class="jc-media__modified">修改时间：@{{ getLastModified(file) }}</div>
                <div class="jc-operators">
                  <button type="button" title="改名" class="md-button md-fab md-mini md-theme-default"
                    @click="renameFile(file, ...arguments)">
                    <div class="md-ripple">
                      <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div>
                    </div>
                  </button>
                  <a :href="getPath(file)" :download="file.name" title="下载" class="md-button md-fab md-mini md-theme-default"
                    @click="downloadFile(file, ...arguments)">
                    <div class="md-ripple">
                      <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">get_app</i></div>
                    </div>
                  </a>
                  <button type="button" title="删除" class="md-button md-fab md-mini md-theme-default"
                    @click="deleteFile(file, ...arguments)">
                    <div class="md-ripple">
                      <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div>
                    </div>
                  </button>
                </div>
              </div>
              <div class="jc-media__name">@{{ file.name }}</div>
            </li>
          </ul>
        </div>
        <div id="media-manager__footer">
          <div id="media-manager__pagination">
            <el-pagination
              layout="prev, pager, next"
              :page-size="20"
              :total="currentFiles.length">
            </el-pagination>
          </div>
          <button :disabled="!this.currentPath" type="button" title="批量删除" class="md-button md-icon-button md-raised md-primary md-theme-default"
            @click="batchDelete">
            <div class="md-ripple">
              <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div>
            </div>
          </button>
          <button :disabled="!this.currentPath" type="button" title="新建文件夹" class="md-button md-icon-button md-raised md-primary md-theme-default"
            @click="createFolter">
            <div class="md-ripple">
              <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">create_new_folder</i></div>
            </div>
          </button>
          <button :disabled="!this.currentPath" type="button" title="上传" class="md-button md-icon-button md-raised md-primary md-theme-default"
            @click="openUpload">
            <div class="md-ripple">
              <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">cloud_upload</i></div>
            </div>
          </button>
        </div>
      </div>
    </div>
    <el-dialog
      id="media-manager__upload-dialog"
      title="上传文件"
      top="-5vh"
      :visible.sync="uploadDialogVisible"
      :destroy-on-close="true">
      <el-upload
        id="media-manager__upload"
        ref="image_upload"
        action="/admin/medias/upload"
        :data="uploadData"
        :on-success="handleUploadSuccess"
        :auto-upload="false"
        :file-list="uploadList"
        :on-change="handleFileListChange"
        :before-upload="checkUpload"
        :accept="mimeTypes"
        list-type="picture"
        name="files"
        drag
        multiple>
        <i class="el-icon-upload"></i>
        <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
      </el-upload>
      <span slot="footer" class="dialog-footer">
        <button type="button" class="md-button md-raised md-dense md-theme-default" @click="closeUpload">
          <div class="md-ripple">
            <div class="md-button-content">取 消</div>
          </div>
        </button>
        <button type="button" class="md-button md-raised md-dense md-primary md-theme-default" @click="handleSubmitUpload">
          <div class="md-ripple">
            <div class="md-button-content">上 传</div>
          </div>
        </button>
      </span>
    </el-dialog>
  </div>
@endsection

@section('script')
<script>
  let app = new Vue({
    el: '#media-manager',
    data() {
      return {
        treeProps: {
          label: 'name',
          children: 'subfolders',
        },

        folders: [{
          name: 'files',
          path: 'files',
        }, {
          name: 'images',
          path: 'images',
        }],

        files: {},

        currentFiles: [],
        currentPath: '',
        selected: {},

        filterBy: 'name',
        filterValues: {
          name: '',
          size_ge: null,
          size_le: 2000,
          ratio: null,
          width_ge: null,
          width_le: 1920,
        },
        filtered: false,

        displayMode: 'table',
        orderby: 'name.asc',
        loading: false,

        uploadDialogVisible: false,
        uploadList: [],
        uploadData: {
          _token: '{{ csrf_token() }}'
        },
      }
    },

    computed: {
      mimeTypes() {
        if (! this.currentPath) {
          return ''
        }

        const category = this.category();
        if (category === 'files') {
          return '{{ implode(config("media.categories.files.valid_mime"), ",") }}'
        }

        if (category === 'images') {
          return '{{ implode(config("media.categories.images.valid_mime"), ",") }}'
        }
      },
    },

    methods: {
      category(path) {
        path = path || this.currentPath
        const pos = path.indexOf('/');
        return pos < 0 ? path : path.substr(0, pos)
      },

      allowDrag(node) {
        return node.level > 1
      },

      allowDrop(draggingNode, dropNode, type) {
        // console.log(dropNode)
        return dropNode.level > 1 && draggingNode.data.path.substr(0,6) === dropNode.data.path.substr(0,6)
      },

      getNodeClass(node) {
        const path = node.data.path
        if (this.files[path] == null) {
          return 'is-fresh'
        }
        return ''
      },

      changeCurrentPath(data, node, treeNode) {
        const path = node.data.path;
        if (path == this.currentPath) {
          return
        }

        this.resetFilters()
        this.clearCurrentFiles()
        this.currentPath = path;

        if (this.files[path] == null) {
          this.load(path)
        } else {
          this.setCurrentFiles()
        }
      },

      getCurrentTreeNode() {
        if (! this.currentPath) {
          return null;
        }
        return this.$refs.folders.getNode(this.currentPath).treeNode
      },

      load(path) {
        path = path || this.currentPath;
        if (! path) {
          return
        }

        const node = this.$refs.folders.getNode(path);
        if (!node) {
          return
        }

        const data = node.data;

        // 添加加载图标
        const $loading = $('<span class="el-tree-node__loading-icon el-icon-loading"></span>');
        $loading.insertBefore($(node.treeNode.$el).find('>.el-tree-node__content>.el-tree-node__label'))

        // 设置加载状态
        this.loading = true

        // 清空当前项
        if (path === this.currentPath) {
          this.clearCurrentFiles()
        }

        // 后台获取目录内容
        axios.post('/admin/medias/under', {path: path}).then(function(response) {

          // 重设子文件夹
          const subfolders = [];
          const folders = response.data.folders;
          for (let i = 0; i < folders.length; i++) {
            subfolders.push({
              name: folders[i].name,
              path: path+'/'+folders[i].name
            })
          }
          app.$set(data, 'subfolders', subfolders)

          // 文件
          app.files[path] = response.data.files;
          if (app.currentPath === path) {
            app.setCurrentFiles()
          }

          // 移除加载状态
          app.loading = false
          $loading.remove()
        }).catch()
      },

      clearCurrentFiles() {
        this.selected = {}
        this.$set(this.$data, 'currentFiles', [])
      },

      setCurrentFiles(data) {
        if (data) {
          this.filtered = true
        } else {
          data = this.files[this.currentPath]
          this.filtered = false
        }
        this.$set(this.$data, 'currentFiles', clone(data))
      },

      reloadCurrentPath() {
        this.load()
      },

      resetFilters() {
        this.filterBy = 'name';
        this.filterValues.name = '';
        this.filterValues.size_ge = null;
        this.filterValues.size_le = 2000;
        this.filterValues.ratio = null;
        this.filterValues.width_ge = null;
        this.filterValues.width_le = 1920;
      },

      handleFilterTypeChange(value) {
        if (this.filtered) {
          this.filterValues.name = '';
          this.filterValues.size_ge = null;
          this.filterValues.size_le = 2000;
          this.filterValues.ratio = null;
          this.filterValues.width_ge = null;
          this.filterValues.width_le = 1920;

          this.setCurrentFiles()
        }
      },

      filterByName(value) {
        if (!this.currentPath) {
          return
        }
        if (! value) {
          this.setCurrentFiles()
          return
        }
        const allFiles = this.files[this.currentPath];
        if (allFiles.length) {
          value = value.toLowerCase()
          const files = allFiles.filter(file => {
            return file.name.toLowerCase().indexOf(value) >= 0
          })
          this.setCurrentFiles(files)
        }
      },

      getDefaultRatios(queryString, cb) {
        cb([
          {label: '4:3', value: '1.3333'},
          {label: '5:4', value: '1.25'},
          {label: '16:9', value: '1.7778'},
        ])
      },

      handleRatioInput(value) {
        value *= 1;
        if (! value) {
          this.setCurrentFiles()
        } else {
          this.filterByRatio(value)
        }
      },

      handleRatioSelect(item) {
        value = item.value*1
        this.filterByRatio(value)
      },

      filterByRatio(value) {
        const allFiles = this.files[this.currentPath];
        if (allFiles.length) {
          const files = allFiles.filter(file => {
            if (file.width && file.height) {
              return Math.abs(file.width/file.height - value) <= 0.0001
            }
            return false
          })
          this.setCurrentFiles(files)
        }
      },

      sortFiles(value) {
        if (!this.currentFiles.length) {
          return
        }
        const files = clone(this.currentFiles);
        switch (value) {
          case 'name.asc':
            files.sort(function(a, b) {
              return a.name < b.name ? -1 : (a.name > b.name)
            })
            break;
          case 'name.desc':
            files.sort(function(a, b) {
              return a.name > b.name ? -1 : (a.name < b.name)
            })
            break;
          case 'size.asc':
            files.sort(function(a, b) {
              return a.size < b.size ? -1 : (a.size > b.size)
            })
            break;
          case 'size.desc':
            files.sort(function(a, b) {
              return a.size > b.size ? -1 : (a.size < b.size)
            })
            break;
          case 'modified.asc':
            files.sort(function(a, b) {
              return a.modified < b.modified ? -1 : (a.modified > b.modified)
            })
            break;
          case 'modified.desc':
            files.sort(function(a, b) {
              return a.modified > b.modified ? -1 : (a.modified < b.modified)
            })
            break;
        }
        this.setCurrentFiles(files)
      },

      getBasename(path) {
        return path && path.split('/').pop() || '';
      },

      getThumb(file) {
        if (file.mimeType && file.mimeType.substr(0, 5) == 'image') {
          const path = '/media/'+this.currentPath+(file.thumb ? '/.thumbs/':'/')+file.name;
          let thumb = `<img src="${path}"/>`;

          // const size = file.width + ' x ' + file.height;
          // thumb += `<div class="jc-media__image-size">${size}</div>`

          return thumb
        }

        // return '<i class="md-icon md-icon-font md-theme-default">picture_as_pdf</i>'
        return '<span>txt</span>'
      },

      getPath(file) {
        return '/media/'+this.currentPath+'/'+file.name
      },

      getAspect(file) {
        return file.width + ' x ' + file.height
      },

      getFileSize(file) {
        let units = ['Kb','Mb','Gb'];

        let size = file.size;
        let unit = 'b';

        for (let i = 0; i < units.length; i++) {
          if (size >= 1024) {
            size /= 1024;
            unit = units[i];
          } else {
            break;
          }
        }

        return size.toFixed(2)+unit
      },

      getLastModified(file) {
        return moment(file.modified*1000).fromNow()
      },

      renameFile(file, event) {
        if (event) {
          event.stopPropagation()
        }
        const fileName = file.name.replace(/\.[^.]*$/, '');
        const ext = file.name.substr(fileName.length);
        const path = this.currentPath;
        this.$prompt('', '文件名：', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          inputValue: fileName,
          inputPattern: /^[a-z0-9\-_]+$/,
          inputErrorMessage: '文件夹名只能包含小写字母、数字、连字符和下划线'
        }).then(({ value }) => {
          if (value === fileName) {
            return
          }

          const loading = this.$loading({
            lock: true,
            text: '正在修改文件名 ...',
            background: 'rgba(255, 255, 255, 0.7)'
          });
          axios.post('/admin/medias/file/rename', {
            path: path,
            old_name: file.name,
            new_name: value + ext,
          }).then(response => {
            this.load(path)
            loading.close()
          }).catch((err) => {
            loading.close()
            console.error(err)
            this.$message.error(err);
          })
        }).catch(() => {});
      },

      downloadFile(file, event) {
        if (event) {
          event.stopPropagation()
        }
      },

      deleteFile(file, event) {
        if (event) {
          event.stopPropagation()
        }

        const path = this.currentPath;
        this.$confirm(`确定删除 ${file.name} ?`, '删除文件', {
          confirmButtonText: '是的',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          const loading = this.$loading({
            lock: true,
            text: '正在删除文件 ...',
            background: 'rgba(255, 255, 255, 0.7)'
          });
          axios.post('/admin/medias/file/delete', {
            path: path,
            file: file.name,
          }).then(response => {
            this.load(path)
            loading.close()
          }).catch((err) => {
            loading.close()
            console.error(err)
            this.$message.error(err);
          })
        }).catch(() => {});
      },

      // 批量删除文件
      batchDelete() {
        const selected = []
        for (const key in this.selected) {
          if (this.selected.hasOwnProperty(key) && this.selected[key]) {
            selected.push(key)
          }
        }
        if (! selected.length) {
          this.$message({
            message: '未选中任何文件',
            type: 'warning'
          });
          return
        }

        const path = this.currentPath;
        this.$confirm(`确定删除 ${selected.length} 个文件 ?`, '删除文件', {
          confirmButtonText: '是的',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          const loading = this.$loading({
            lock: true,
            text: '正在删除文件 ...',
            background: 'rgba(255, 255, 255, 0.7)'
          });
          axios.post('/admin/medias/file/delete', {
            path: path,
            file: selected,
          }).then(response => {
            this.load(path)
            loading.close()
          }).catch((err) => {
            loading.close()
            console.error(err)
            this.$message.error(err);
          })
        }).catch(() => {});
      },

      createFolter() {
        const path = this.currentPath;
        this.$prompt('', '文件夹名：', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          inputPattern: /^[a-z0-9\-_]+$/,
          inputErrorMessage: '文件夹名只能包含小写字母、数字、连字符和下划线'
        }).then(({ value }) => {
          const loading = this.$loading({
            lock: true,
            text: '正在创建文件夹 ...',
            background: 'rgba(255, 255, 255, 0.7)'
          });
          axios.post('/admin/medias/folder/create', {
            path: path,
            folder: value,
          }).then(response => {
            this.load(path)
            loading.close()
          }).catch((err) => {
            loading.close()
            console.error(err)
            this.$message.error(err);
          })
        }).catch(() => {});
      },

      toggleSelect(file, e) {
        const selected = !!this.selected[file.name];
        this.selected[file.name] = !selected;

        let $item = $(e.target);
        if (! $item.hasClass('.jc-media-item')) {
          $item = $item.parents('.jc-media-item').first()
        }
        $item.toggleClass('is-selected')

        // this.selected[file.name] = true;
      },

      selectAll() {
        if (this.displayMode === 'table') {
          const table = this.$refs.files_table;
          table.clearSelection()
          table.toggleAllSelection()
        } else {
          this.selected = {};
          this.currentFiles.forEach(file => {
            this.selected[file.name] = true
          })
          this.setCurrentFiles();
        }
      },

      makeSelection() {
        const table = this.$refs.files_table;
        const selected = this.selected;
        this.currentFiles.forEach(file => {
          if (selected[file.name]) {
            table.toggleRowSelection(file, true)
          }
        })
      },

      handleSelectionChange(selection) {
        this.selected = {};
        selection.forEach(row => {
          this.selected[row.name] = true;
        });
      },

      selectExit(file) {
        const url = '/media/'+this.currentPath+'/'+file.name;
        alert(url)
      },

      openUpload() {
        // this.uploadList = [];
        this.uploadDialogVisible = true;
      },

      closeUpload() {
        this.uploadDialogVisible = false;
        this.uploadList = [];
      },

      handleFileListChange(file, fileList) {
        this.uploadList = fileList.slice()
      },

      checkUpload(file) {
        // 检查文件大小
        if (file.size/1024/1024 > 2) {
          return false
        }

        // 检查文件是否已存在
        for (let i = 0; i < this.currentFiles.length; i++) {
          if (this.currentFiles[i].name == file.name) return false
        }

        return true
      },

      availableUploads() {
        let available = 0;
        const files = this.currentFiles.map(function(file) {
          return file.name
        })
        for (let i = 0; i < this.uploadList.length; i++) {
          if (files.indexOf(this.uploadList[i].name) < 0) {
            available++
          }
        }
        return available;
      },

      handleSubmitUpload() {
        this.filesUploading = this.availableUploads();
        if (this.filesUploading) {
          this.uploadData.path = this.currentPath
          this.$refs.image_upload.submit();
        } else {
          this.closeUpload()
        }
      },

      handleUploadSuccess(response) {
        this.filesUploading--;
        if (this.filesUploading <= 0) {
          this.closeUpload()
          this.resetFilters()
          this.load()
        }
      },
    },
  })
</script>
@endsection
