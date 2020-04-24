@extends('admin::layout')

@section('h1', '文件管理')

@section('main_content')
  <div id="media_manager">
    <div id="media_manager_folders">
      <h2>目录：</h2>
      <el-tree
        :data="folders"
        :props="treeProps"
        node-key="path"
        :node-class="getNodeClass"
        @node-click="load"
        default-expand-all
        :expand-on-click-node="false"></el-tree>
    </div>
    <div id="media_manager_main">
      <el-table
        id="media_manager_files"
        class="jc-table"
        v-if="results.length"
        :data="results">
        <el-table-column
          label="缩略图"
          width="150">
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
          prop="size"
          label="大小"
          width="180"
          sortable>
          <template slot-scope="scope">
            <span class="jc-media__size">@{{ getFileSize(scope.row) }}</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="modified"
          label="最后修改"
          width="180"
          sortable>
          <template slot-scope="scope">
            <span class="jc-media__modified">@{{ getLastModified(scope.row) }}</span>
          </template>
        </el-table-column>
      </el-table>
      {{-- <ul id="media_manager_files" v-if="results.length">
        <li class="jc-media-item" v-for="file in results" :key="file.name" @dblclick="selectFile(file)">
          <div class="jc-media__thumb"><img :src="getThumb(file)"></div>
          <div class="jc-media__info">
            <span class="jc-media__name">@{{ file.name }}</span>
            <span class="jc-media__image-size" v-if="file.width && file.height">尺寸：@{{ getImageSize(file) }}</span>
            <span class="jc-media__size">大小：@{{ getFileSize(file) }}</span>
            <span class="jc-media__modified">修改时间：@{{ getLastModified(file) }}</span>
          </div>
        </li>
      </ul> --}}
      <p id="media_manager_empty" v-if="!results.length">(...)</p>
    </div>
  </div>
@endsection

@section('script')
<script>
  let app = new Vue({
    el: '#media_manager',
    data() {
      return {
        treeProps: {
          label: 'name',
          children: 'folders',
        },

        folders: [{
            name: 'files',
            path: 'files',
            folders: [],
          }, {
            name: 'images',
            path: 'images',
            folders: [],
          }],

        files: {},

        results: [],

        currentPath: '',
      }
    },

    methods: {
      getNodeClass(node) {
        const path = node.data.path
        if (this.files[path] == null) {
          return 'is-unloaded'
        }
        return ''
      },

      load(data, node, treeNode) {
        const path = node.data.path;
        if (path == this.currentPath) {
          return
        }

        this.clearResults()
        this.currentPath = path;

        if (this.files[path] == null) {
          // 添加加载图标
          const $loading = $('<span class="el-tree-node__loading-icon el-icon-loading"></span>');
          $loading.insertBefore($(treeNode.$el).find('.el-tree-node__label'))

          // 后台获取目录内容
          axios.post('/admin/media/under', {path: path}).then(function(response) {
            if (! data.folders) {
              app.$set(data, 'folders', [])
            }

            const folders = response.data.folders;
            for (let i = 0; i < folders.length; i++) {
              data.folders.push({
                name: folders[i].name,
                path: path+'/'+folders[i].name
              })
            }

            app.files[path] = response.data.files;
            if (app.currentPath === path) {
              app.setResults(response.data.files)
            }

            // 移除加载图标
            $loading.remove()
          }).catch()
        } else {
          this.setResults()
        }
      },

      clearResults() {
        this.$set(this.$data, 'results', [])
      },

      setResults(data) {
        data = clone(data || this.files[this.currentPath])
        this.$set(this.$data, 'results', data)
      },

      getBasename(path) {
        return path && path.split('/').pop() || '';
      },

      getThumb(file) {
        if (file.mimeType && file.mimeType.substr(0, 5) == 'image') {
          const path = '/media/'+this.currentPath+(file.thumb ? '/.thumbs/':'/')+file.name;
          let thumb = `<img src="${path}"/>`;

          const size = file.width + ' x ' + file.height;
          thumb += `<div class="jc-media__image-size">${size}</div>`

          return thumb
        }
        return ''
      },

      getImageSize(file) {
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

      selectFile(file) {
        const url = '/media/'+this.currentPath+'/'+file.name;
        alert(url)
      },
    },
  })
</script>
@endsection
