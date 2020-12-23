<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>搜索规格</title>
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
    #search {
      display: flex;
      justify-content: center;
    }
    #groups, #records {
      margin: 20px 0;
      padding: 20px 40px;
      border: 1px solid #ebebeb;
    }
    #records {
      padding-left: 60px;
    }
    .record {
      margin-bottom: 20px;
    }
    .record_data {
      padding: 5px;
      font-family: 'Courier New', Courier, monospace;
      font-size: 12px;
    }
  </style>
</head>
<body>
  <div id="main">
    <div id="search">
      <el-form id="search_form" ref="search_form">
        <el-form-item size="small">
          <el-input type="text" v-model="keywords" autocomplete="off" native-size="60" @keyup.enter.native="submit()"></el-input>
          <el-button type="primary" @click.stop="submit()">搜索</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div id="groups">
      <div class="group" v-for="(group,field) in groups">
        <h3>@{{ field }}</h3>
        <el-checkbox v-for="(cnt, val) in group">@{{ val+'('+cnt+')' }}</el-checkbox>
      </div>
    </div>
    <ol id="records">
      <li class="record" v-for="record in records">
        <div class="record_data">@{{ record }}</div>
        <div><a :href="'/specs/{{ $spec_id }}/records/'+record.id" target="_blank" rel="noopener noreferrer">详情</a></div>
      </li>
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

      mounted() {
        this.$refs.search_form.$el.onsubmit = () => {return false}
      },

      methods: {
        submit(e) {
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
