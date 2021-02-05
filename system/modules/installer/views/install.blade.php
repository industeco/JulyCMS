<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>安装 JulyCMS</title>
  <link rel="stylesheet" href="/themes/backend/fonts/fonts.css">
  <link rel="stylesheet" href="/themes/backend/vendor/normalize.css/normalize.css">
  <link rel="stylesheet" href="/themes/backend/vendor/vue-material/vue-material.css">
  <link rel="stylesheet" href="/themes/backend/vendor/vue-material/theme/default.css">
  <link rel="stylesheet" href="/themes/backend/vendor/element-ui/theme-chalk/index.css">
  <link rel="stylesheet" href="/themes/backend/css/july.css">
  <link rel="stylesheet" href="/themes/backend/css/install.css">
</head>
<body>
  <div id="install" class="md-elevation-7">
    <h1 class="jc-install-title">欢迎使用 JulyCMS</h1>
    <el-steps :active="currentStep" finish-status="success" align-center>
      <el-step title="检查安装环境" icon="el-icon-finished"></el-step>
      <el-step title="初始化配置" icon="el-icon-s-operation"></el-step>
      <el-step title="安装" icon="el-icon-s-flag" :status="lastStepStatus"></el-step>
    </el-steps>
    <div id="install_steps" v-show="isMounted" style="display: none">
      <div class="jc-install-step" v-if="currentStep===0">
        <div class="jc-install-step-content">
          <ul class="jc-env-list">
            @foreach ($requirements as $requirement => $ok)
            <li class="jc-env{{ $ok ? ' is-ok' : '' }}">
              <span>{{ $requirement }}</span>
            </li>
            @endforeach
          </ul>
        </div>
        <div class="jc-install-step-footer">
          <button type="button" class="md-button md-raised md-primary md-theme-default"
            :disabled="!environmentsOk"
            @click.stop="stepToSettings">
            <div class="md-ripple">
              <div class="md-button-content">下一步</div>
            </div>
          </button>
        </div>
      </div>
      <div class="jc-install-step" v-if="currentStep===1">
        <div class="jc-install-step-content">
          <el-form ref="settings_form"
            :model="settings"
            :rules="rules"
            label-width="120px">
            <el-form-item label="网址" prop="app_url">
              <el-input
                size="medium"
                native-size="50"
                v-model="settings.app_url"
                placeholder="https://www.example.com">
              </el-input>
            </el-form-item>
            <el-form-item label="管理账号" prop="admin_name">
              <el-input
                size="medium"
                native-size="50"
                v-model="settings.admin_name"
                placeholder="admin"></el-input>
            </el-form-item>
            <el-form-item label="管理密码" prop="admin_password">
              <div class="jc-form-item-group">
                <el-input
                  size="medium"
                  native-size="50"
                  v-model="settings.admin_password">
                </el-input>
                <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
                  @click.stop="randomPassword">
                  <div class="md-ripple"><div class="md-button-content">随机</div></div>
                </button>
              </div>
            </el-form-item>
            <el-form-item label="数据文件" prop="db_database" class="has-helptext">
              <div class="jc-form-item-group">
                <el-input
                  size="medium"
                  native-size="50"
                  v-model="settings.db_database"
                  placeholder="database.db3">
                </el-input>
                <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
                  @click.stop="randomDatabase">
                  <div class="md-ripple"><div class="md-button-content">随机</div></div>
                </button>
              </div>
              <span class="jc-form-item-help"><i class="el-icon-info"></i> SQLite 数据文件</span>
            </el-form-item>
            <el-form-item label="企业名" prop="site_subject">
              <el-input
                size="medium"
                native-size="50"
                v-model="settings.site_subject"
                placeholder="衡水网科计算机服务有限公司">
              </el-input>
            </el-form-item>
            <el-form-item label="邮箱" prop="mail_to_address" class="has-helptext">
              <el-input
                size="medium"
                native-size="50"
                v-model="settings.mail_to_address"
                placeholder="someone@example.com">
              </el-input>
              <span class="jc-form-item-help"><i class="el-icon-info"></i> 用于接收站内邮件</span>
            </el-form-item>
          </el-form>
        </div>
        <div class="jc-install-step-footer">
          <button type="button" class="md-button md-raised md-primary md-theme-default"
            @click.stop="install">
            <div class="md-ripple">
              <div class="md-button-content">安装</div>
            </div>
          </button>
        </div>
      </div>
      <div class="jc-install-step" v-if="currentStep===2">
        <div class="jc-install-step-content">
          <div v-if="lastStepStatus==='finish'">
            <h3>账号：@{{ settings.admin_name }}</h3>
            <h3>密码：@{{ settings.admin_password }}</h3>
          </div>
        </div>
        <div class="jc-install-step-footer">
          <button type="button" class="md-button md-raised md-primary md-theme-default"
            :disabled="lastStepStatus!=='finish'" @click.stop="login">
            <div class="md-ripple">
              <div class="md-button-content">转到登录</div>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
  <script src="/themes/backend/js/app.js"></script>
  <script src="/themes/backend/vendor/element-ui/index.js"></script>
  <script src="/themes/backend/js/utils.js"></script>
  <script>
    const app = new Vue({
      el: '#install',
      data() {
        return {
          currentStep: 0,
          environmentsOk: true,
          settings: {
            app_url: 'http://127.0.0.1',
            admin_name: 'admin',
            admin_password: 'admin666',
            db_database: null,
            site_subject: 'Someone',
            mail_to_address: 'someone@example.com',
          },
          rules: {
            app_url: [
              { required: true, message: '网址不能为空', trigger: 'blur' },
              { pattern: /^\s*https?:\/\/(127\.0\.0\.1\s*$|localhost\s*$|www\.)/i, trigger: 'change' },
              { type: 'url', message: '网址格式错误', trigger: 'change' },
            ],
            admin_name: [
              { required: true, message: '管理账号不能为空', trigger: 'blur' },
              { pattern: /^[a-zA-Z0-9\-_ ]+$/, message: '管理账号只能使用大小写字母、数字，连字符（-），下划线（_）或空格', trigger: ['change', 'blur'] },
              { pattern: /[a-zA-Z0-9\-_]/, message: '管理账号不能全是空格', trigger: ['change', 'blur'] },
            ],
            admin_password: [
              { required: true, message: '管理密码不能为空', trigger: 'blur' },
              { min: 8, message: '管理密码至少 8 位字符', trigger: 'blur' },
            ],
            db_database: [
              { required: true, message: '数据文件不能为空', trigger: 'blur' },
              { pattern: /^[a-z0-9_]+\.db3$/, message: '数据文件只能包含小写字母、数字和下划线，且后缀名必须是 .db3', trigger: 'blur' },
            ],
            site_subject: [
              { required: true, message: '企业名不能为空', trigger: 'blur' },
              { pattern: /^[^\\"']+$/, message: '企业名不能包含 \\ 和 "', trigger: 'blur' },
            ],
            mail_to_address: [
              { required: true, message: '邮箱不能为空', trigger: 'blur' },
              { type: 'email', message: '邮箱格式错误', trigger: ['blur', 'change'] },
            ],
          },

          isMounted: false,
          lastStepStatus: 'wait',
        };
      },

      created() {
        this.randomDatabase();

        @foreach ($requirements as $requirement => $ok)
          @if(! $ok)
            this.environmentsOk = false;
            @break
          @endif
        @endforeach
      },

      mounted() {
        this.isMounted = true;
      },

      methods: {
        stepToSettings() {
          if (this.environmentsOk) {
            this.currentStep = 1;
          }
        },

        randomDatabase() {
          const chars = 'abcdefghijklmnopqrstuvwxyz_0123456789';
          const maxPos = chars.length;
          let db = '';
          for (let i=0; i<12; i++) {
            db += chars.charAt(Math.floor(Math.random() * maxPos));
          }
          this.settings.db_database = db + '.db3';
        },

        randomPassword() {
          const chars = 'ABDEFGHJKMNQRTXYabdefhijkmnrtxy34678~!#$%^&*_-+=?;,.';
          const maxPos = chars.length;
          let admin_password = '';
          for (let i=0; i<10; i++) {
            admin_password += chars.charAt(Math.floor(Math.random() * maxPos));
          }
          this.settings.admin_password = admin_password;
        },

        install() {
          const form = this.$refs.settings_form;
          const loading = this.$loading({
            lock: true,
            text: '正在安装 ...',
            background: 'rgba(255, 255, 255, 0.7)',
          });

          form.validate().then(() => {
            this.currentStep = 2;
            this.lastStepStatus = 'process';

            // 更新 .env 文件，创建数据库
            axios.post('/install', this.settings).then(response => {
              // console.log(response);

              // 迁移数据
              axios.post('/install/migrate', {
                admin_name: this.settings.admin_name,
                admin_password: this.settings.admin_password,
              }).then(response => {
                // console.log(response);
                loading.close();
                this.lastStepStatus = 'finish';
                this.$message.success('安装完成');
              }).catch(err => {
                loading.close();
                console.error(err);
                this.$message.error('发生错误，可查看控制台');
              });
            }).catch(err => {
              loading.close();
              console.error(err);
              this.$message.error('发生错误，可查看控制台');
            });
          }).catch(err => {
            loading.close();
          });
        },

        login() {
          location.href = "{{ short_url('admin.login') }}";
        },
      },
    });
  </script>
</body>
</html>
