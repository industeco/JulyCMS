@extends('admin::layout')

@section('h1', '文件管理')

@section('main_content')
  <div id="media_manager">
    <el-tabs v-model="activeName" @tab-click="handleClick">
      <el-tab-pane label="图片" name="images"></el-tab-pane>
      <el-tab-pane label="文档" name="files"></el-tab-pane>
    </el-tabs>
    <div id="medias">
      <div id="medias_header">
        <el-breadcrumb separator="/">
          <el-breadcrumb-item v-for="item in this[this.activeName].breadcrumbs.slice(0, -1)" :key="item">
            <a href="#" @click="handleBreadcrumbClick(item)">@{{ getBreadcrumbName(item) }}</a>
          </el-breadcrumb-item>
          <el-breadcrumb-item>@{{ getBreadcrumbName(currentPath) }}</el-breadcrumb-item>
        </el-breadcrumb>
        <el-select size="small" placeholder="选择下级目录" @change="handleSubdirChange">
          <el-option
            v-for="item in currentMedias.folders"
            :key="item.name"
            :label="item.name"
            :value="item.name">
          </el-option>
        </el-select>
      </div>
      <ul id="files_list">
        <li class="jc-media-item" v-for="item in currentMedias.files" :key="item.name">
          <div class="jc-media__thumb"><img :src="getThumb(item)"></div>
          <div class="jc-media__info">
            <span class="jc-media__name">@{{ item.name }}</span>
            <span class="jc-media__size">大小：@{{ getSize(item) }}</span>
            <span class="jc-media__time">修改时间：@{{ getTime(item) }}</span>
          </div>
        </li>
      </ul>
    </div>
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

      }
    },

    computed: {
      currentPath() {
        const breadcrumbs = this[this.activeName].breadcrumbs;
        return breadcrumbs[breadcrumbs.length - 1];
      },
      currentMedias() {
        return this[this.activeName].medias[this.currentPath] || []
      },
    },

    created() {
      const path = this.activeName
      this.getUnder(path).then(function(response) {
        // console.log(response)
        Vue.set(app[app.activeName].medias, path, response.data)
      }).catch()
    },

    methods: {
      handleBreadcrumbClick(path) {
        const breadcrumbs = this[this.activeName].breadcrumbs;
        const index = breadcrumbs.indexOf(path);
        Vue.set(this[this.activeName], 'breadcrumbs', breadcrumbs.slice(0, index+1))
      },

      getBreadcrumbName(breadcrumb) {
        return breadcrumb && breadcrumb.split('/').pop() || '';
      },

      getUnder(path) {
        return axios.post('/admin/media/under', {path: path})
      },

      getThumb(media) {
        return '/media/'+this.currentPath+(media.thumb ? '/.thumbs/':'/')+media.name;
      },

      addBreadcrumb(path) {
        this[this.activeName].breadcrumbs.push(path)
      },

      handleSubdirChange(dir) {
        let path = this.currentPath+'/'+dir;
        this.addBreadcrumb(path);
        const medias = this[this.activeName].medias;
        if (medias[path] == null) {
          this.getUnder(path).then(function(response) {
            Vue.set(medias, path, response.data)
          }).catch()
        }
      },

      getSize(media) {
        let size = media.size;
        let unit = 'b';
        if (size >= 1024) {
          size /= 1024;
          unit = 'kb';
        }

        if (size >= 1024) {
          size /= 1024;
          unit = 'mb';
        }

        return size.toFixed(2)+unit
      },

      getTime(media) {
        return moment(media.modified*1000).fromNow()
      },

      handleClick(tab, event) {
        console.log(tab, event);
      },
    },
  })
</script>
@endsection
