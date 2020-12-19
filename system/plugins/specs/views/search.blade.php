<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', '七月 CMS')</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700,400italic|Material+Icons">
  <link rel="stylesheet" href="/themes/backend/vendor/normalize.css/normalize.min.css">
  <link rel="stylesheet" href="/themes/backend/vendor/element-ui/theme-chalk/index.min.css">
  <link rel="stylesheet" href="/themes/backend/vendor/vue-material/vue-material.min.css">
  <link rel="stylesheet" href="/themes/backend/vendor/vue-material/theme/default.min.css">
  <link rel="stylesheet" href="/themes/backend/css/july.css">
  <style>
    #main {
      max-width: 1440px;
      margin: 40px auto;
      padding: 20px;
    }
  </style>
</head>
<body>
  <script src="/themes/backend/js/svg.js"></script>

  <div id="main">
    <el-form id="search_form" ref="specsSearchForm" label-width="100px">
      <el-form-item label="查找规格" size="small">
        <el-input type="text" v-model="keywords" autocomplete="off" native-size="60"></el-input>
        <el-button type="primary" @click.stop="submit()">提交</el-button>
      </el-form-item>
    </el-form>
    <div class="groups">
      <div class="group" v-for="(group,field) in groups">
        <h3>@{{ field }}</h3>
        <el-checkbox v-for="(cnt, val) in group">@{{ val+'('+cnt+')' }}</el-checkbox>
      </div>
    </div>
    <ol id="records">
      <li class="record" v-for="record in records">@{{ record }}</li>
    </ol>
  </div>

  <script src="/themes/backend/js/app.js"></script>
  <script src="/themes/backend/vendor/element-ui/index.js"></script>
  <script src="/themes/backend/js/utils.js"></script>
  <script>
    const app = new Vue({
      el: '#main',

      data() {
        return {
          keywords: '',
          groups: {},
          records: [],
        };
      },

      methods: {
        submit() {
          const keywords = this.keywords.trim();
          if (!keywords.length) {
            return;
          }

          const loading = app.$loading({
            lock: true,
            text: '正在查询……',
            background: 'rgba(0,0,0,0.7)',
          });

          const data = {
            keywords: keywords,
          };

          axios.post("{{ short_url('specs.search', $spec_id) }}", data).then(function(response) {
            loading.close();
            console.log(response.data.results.groups);
            Vue.set(app.$data, 'groups', response.data.results.groups);
            Vue.set(app.$data, 'records', response.data.results.records);
          }).catch(function(error) {
            loading.close();
            console.error(error);
            app.$message.error('查询失败');
          });
        },
      },
    });
  </script>

  @yield('script')
</body>
</html>
