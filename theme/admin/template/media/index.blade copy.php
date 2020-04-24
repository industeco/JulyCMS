@extends('admin::layout')

@section('h1', '文件管理')

@section('main_content')
  <div id="media_manager">
    <el-tabs id="media_manager_header" v-model="activeName" @tab-click="handleTabClick">
      <el-tab-pane label="图片" name="images"></el-tab-pane>
      <el-tab-pane label="文档" name="files"></el-tab-pane>
    </el-tabs>
    <el-breadcrumb id="media_manager_breadcrumbs" separator="/">
      <el-breadcrumb-item v-for="crumb in this[this.activeName].breadcrumbs.slice(0, -1)" :key="crumb">
        <a href="#" @click="changeCurrentPath(crumb)">@{{ getBasename(crumb) }}</a>
      </el-breadcrumb-item>
      <el-breadcrumb-item>@{{ getBasename(this.currentPath) }}</el-breadcrumb-item>
    </el-breadcrumb>
    <div id="media_manager_list">
      <ul id="folder_list" class="md-list md-theme-default" v-if="showFolders">
        <li class="md-list-item" title="双击打开" v-for="item in results.folders" :key="item.name" @dblclick="handleSubdirChange(item.name)">
          <div class="md-list-item-content">
            <i class="md-icon md-icon-font md-theme-default">folder</i>
            <span class="md-list-item-text">@{{ item.name }}</span>
            <button type="button" title="改名" class="md-button md-fab md-mini md-primary md-theme-default jc-theme-light">
              <div class="md-ripple">
                <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">edit</i></div>
              </div>
            </button>
            <button type="button" title="删除" class="md-button md-fab md-mini md-accent md-theme-default jc-theme-light">
              <div class="md-ripple">
                <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div>
              </div>
            </button>
          </div>
        </li>
      </ul>
      <ul id="file_list">
        <li class="jc-media-item" v-for="file in results.files" :key="file.name" @dblclick="selectFile(file)">
          <div class="jc-media__thumb"><img :src="getThumb(file)"></div>
          <div class="jc-media__info">
            <span class="jc-media__name">@{{ file.name }}</span>
            <span class="jc-media__image-size" v-if="file.width && file.height">尺寸：@{{ getImageSize(file) }}</span>
            <span class="jc-media__size">大小：@{{ getFileSize(file) }}</span>
            <span class="jc-media__modified">修改时间：@{{ getLastModified(file) }}</span>
          </div>
        </li>
      </ul>
    </div>
    <div id="media_manager_footer"></div>
  </div>
@endsection

@section('script')
<script>
  let app = new Vue({
    el: '#media_manager',
    data() {
      return {
        activeName: 'images',

        files: {
          breadcrumbs: ['files'],
          medias: {},
        },

        images: {
          breadcrumbs: ['images'],
          medias: {},
        },

        results: {},

        showFolders: true,
      }
    },

    computed: {
      category() {
        return this[this.activeName]
      },
      breadcrumbs() {
        return this[this.activeName].breadcrumbs
      },
      currentPath() {
        const breadcrumbs = this[this.activeName].breadcrumbs;
        return breadcrumbs[breadcrumbs.length - 1];
      },
      medias() {
        return this[this.activeName].medias
      },
    },

    created() {
      this.changeCurrentPath();
    },

    methods: {
      load(path) {
        axios.post('/admin/media/under', {path: path}).then(function(response) {
          // console.log(response)
          app.medias[path] = response.data
          if (app.currentPath === path) {
            app.setResults(response.data)
          }
        }).catch()
      },

      // addBreadcrumb(path) {
      //   this[this.activeName].breadcrumbs.push(path)
      // },

      changeCurrentPath(path) {
        this.clearResults()
        if (path) {
          const index = this.breadcrumbs.indexOf(path);
          if (index >= 0) {
            Vue.set(this.category, 'breadcrumbs', this.breadcrumbs.slice(0, index+1))
          } else {
            this.breadcrumbs.push(path)
          }
        }
        path = path || this.currentPath;
        if (this.medias[path] == null) {
          this.load(path)
        } else {
          this.setResults()
        }
      },

      handleSubdirChange(dir) {
        this.changeCurrentPath(this.currentPath+'/'+dir);
      },

      handleTabClick(tab, event) {
        this.changeCurrentPath();
      },

      clearResults() {
        Vue.set(this.$data, 'results', {})
      },

      setResults(data) {
        data = clone(data || this.medias[this.currentPath])
        Vue.set(this.$data, 'results', data)
      },

      getBasename(path) {
        return path && path.split('/').pop() || '';
      },

      getThumb(file) {
        return '/media/'+this.currentPath+(file.thumb ? '/.thumbs/':'/')+file.name;
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
