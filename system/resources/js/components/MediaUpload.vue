<template>
  <div class="jc-upload">
    <ul v-if="status!=='complete'" class="jc-upload__filelist md-scrollbar md-theme-default"
      :class="{'is-uploading': status === 'uploading'}">
      <li v-for="file in fileList" :key="file.name" class="jc-upload-file" :class="{'is-disabled': !file.disabled}">
        <div class="jc-thumb">
          <img v-if="isImage(file)" :src="file.url" alt="" class="jc-thumb__img">
          <span v-else class="jc-thumb__ext">{{ fileExtension(file) }}</span>
        </div>
        <button type="button" title="移除" class="md-button md-fab md-mini md-accent md-theme-default" @click="handleRemove(file)">
          <div class="md-ripple">
            <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">close</i></div>
          </div>
        </button>
        <el-progress type="circle" :percentage="file.percentage" :status="file.status"></el-progress>
      </li>
    </ul>
    <ul v-if="status==='complete'" class="jc-upload__reportlist md-scrollbar md-theme-default">
      <li v-for="result in results" :key="result.id" class="jc-upload-report" :class="{'is-failed':!result.success}">
        <div class="jc-upload-report__content">
          <div class="jc-upload-report__filename">{{ result.file.name }}</div>
          <div class="jc-upload-report__message">{{ result.success ? '成功！' : '失败！' }} {{ result.message }}</div>
        </div>
        <div class="jc-thumb">
          <img v-if="isImage(result.file)" :src="result.file.url" alt="" class="jc-thumb__img">
          <span v-else class="jc-thumb__ext">{{ fileExtension(result.file) }}</span>
        </div>
      </li>
    </ul>
    <el-upload
      v-show="status==='standingby' || status==='ready'"
      ref="el_upload"
      drag
      multiple
      :action="action"
      :accept="accept"
      :data="postData"
      :name="name"
      :auto-upload="false"
      :on-change="handleChange"
      :on-progress="handleProgress"
      :on-success="handleSuccess"
      :show-file-list="false">
      <i class="el-icon-upload"></i>
      <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
    </el-upload>
  </div>
</template>

<script>

  function clone(obj) {
    return JSON.parse(JSON.stringify(obj))
  }

  export default {
    props: {
      action: String,
      accept: String,
      name: {
        type: String,
        default: 'file',
      },
      fileExist: Function,
    },

    data() {
      return {
        postData: {},

        // 文件列表，包含可上传与不可上传
        fileList: [],

        // 可上传文件数
        available: 0,

        // 已上传文件数
        uploaded: 0,

        // 状态
        // standingby -> ready -> uploading -> complete
        status: 'standingby',

        // 服务器响应，用于生成报告
        // 格式为 { 文件名：消息 }
        response: {},

        // 上传结果
        results: [],

        msgTimmer: null,
        message: [],
      };
    },

    methods: {
      notify(title, msg, type) {
        // setTimeout(() => {
        //   this.$notify({
        //     title: '错误',
        //     message: '这是一条错误的提示消息',
        //     type: type || 'info',
        //     duration: 5000,
        //     showClose: true,
        //   });
        // }, 100);

        const h = this.$createElement;
        this.message.push(h('p', {style: 'margin: 5px 0'}), msg);

        if (this.msgTimmer) {
          clearTimeout(this.msgTimmer);
        }
        this.msgTimmer = setTimeout(() => {
          this.$notify({
            title: title,
            message: h('div', null, this.message),
            type: type || 'info',
            duration: 5000,
            showClose: true,
          });
          this.message = [];
        }, 100);
      },

      isImage(file) {
        const type = file.type || file.raw.type;
        return type.indexOf('image') >= 0;
      },

      fileExtension(file) {
        const name = file.name;
        return name.substr(name.lastIndexOf('.') + 1);
      },

      getFile(file) {
        let target;
        this.fileList.every(item => {
          target = file.uid === item.uid ? item : null;
          return !target;
        });
        return target;
      },

      checkFile(file) {
        // 检查文件类型
        const ext = this.fileExtension(file);
        const accept = this.accept.split(',');
        if (!(ext && accept.indexOf('.'+ext) >= 0) && !(file.type && accept.indexOf(file.type) >= 0)) {
          return `${file.name} 类型不正确`;
        }

        // 检查文件大小
        else if (file.size/1024/1024 > 5) {
          return `${file.name} 文件太大`;
        }

        // 检查文件是否已存在
        else if (typeof this.fileExist === 'function' && this.fileExist(file)) {
          return `${file.name} 已在服务器中`;
        }

        return null;
      },

      clearFiles() {
        this.$refs.el_upload.clearFiles();
      },

      reset() {
        this.$set(this.$data, 'fileList', []);
        this.available = 0;
        this.uploaded = 0;
        this.response = {};
        this.status = 'standingby';
        this.results = [];
      },

      changeStatus(status) {
        this.status = status;
        this.$emit('status-change', status);
      },

      handleChange(file, fileList) {
        if (this.status === 'standingby' || this.status === 'ready') {
          if (this.getFile(file.raw)) {
            this.$refs.el_upload.handleRemove(null, file.raw);
            this.notify('排除文件：', `${file.name} 已存在`, 'error');
            return;
          };

          file.url = URL.createObjectURL(file.raw);
          this.fileList.push(file);

          const error = this.checkFile(file.raw);
          if (error) {
            this.notify('排除文件：', error, 'error');
            file.disabled = true;
            this.$refs.el_upload.handleRemove(null, file.raw);
          } else {
            this.available++;
            if (this.status === 'standingby') {
              this.changeStatus('ready');
            }
          }
        }
      },

      handleRemove(file) {
        if (this.status === 'standingby' || this.status === 'ready') {
          this.fileList.splice(this.fileList.indexOf(file), 1);
          if (! file.disabled) {
            this.$refs.el_upload.handleRemove(null, file);
            this.available--;
            if (this.available === 0) {
              this.changeStatus('standingby');
            }
          }
        }
      },

      handleProgress(event, file, fileList) {
        const target = this.getFile(file);
        if (target) {
          target.percentage = event.percent;
          target.status = file.status;
        }
      },

      submit(data) {
        if (this.status === 'ready') {
          if (data) {
            for (const key in data) {
              if (data.hasOwnProperty(key)) {
                this.postData[key] = data[key];
              }
            }
          }
          this.changeStatus('uploading');
          this.$refs.el_upload.submit();
        }
      },

      handleSuccess(response) {
        this.response = _.extend(this.response, response);
        this.uploaded++;
        if (this.uploaded === this.available) {
          this.changeStatus('complete');
          this.complete()
        }
      },

      report() {
        this.fileList.forEach(file => {
          if (file.disabled) return;

          const response = this.response[file.name];
          let success = true, msg = '';

          if (! response) {
            success = false;
            msg = '可能的原因：文件已存在';
          } else if (response !== file.name) {
            msg = `文件被重命名为：${response}`;
          }

          this.results.push({
            id: file.uid,
            file: file,
            success: success,
            message: msg,
          });
        });
      },

      complete() {
        this.report();
        this.clearFiles();
      },
    },
  }
</script>
