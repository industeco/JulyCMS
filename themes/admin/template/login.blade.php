<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>登录</title>
  <link rel="stylesheet" href="/themes/admin/vendor/element-ui/theme-chalk/index.css">
  <link rel="stylesheet" href="/themes/admin/css/element-fix.css">
</head>
<body>
  <div id="app">
    <el-dialog title="登录后台" width="400px" :visible="true" :show-close="false">
      <el-form label-width="80px" method="POST" action="/admin/login" ref="form">
        @csrf
        <el-form-item label="用户名：" size="medium">
          <el-input name="truename" v-model="truename"></el-input>
        </el-form-item>
        <el-form-item label="密码：" size="medium">
          <el-input type="password" name="password" v-model="password"></el-input>
        </el-form-item>
        @error('login')
        <el-form-item size="medium" error="{{ $message }}"></el-form-item>
        @enderror
        <el-form-item size="medium">
          <el-checkbox name="remember" v-model="remember">记住我</el-checkbox>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" :loading="loading" @click="submit">登&nbsp;&nbsp; 录</el-button>
      </div>
    </el-dialog>
  </div>

  <script src="/themes/admin/js/app.js"></script>
  <script src="/themes/admin/vendor/element-ui/index.js"></script>
  <script>
    let dialog = new Vue({
      el: '#app',
      data() {
        return {
          truename: "{{ old('truename') }}",
          password: "{{ old('password') }}",
          remember: false,
          loading: false,
        }
      },
      methods: {
        submit() {
          this.loading = true
          this.$refs.form.$el.submit()
        },
      },
    })
  </script>
</body>
</html>
