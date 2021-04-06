<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', '七月 CMS')</title>
  <link rel="stylesheet" href="/themes/backend/fonts/fonts.css">
  <link rel="stylesheet" href="/themes/backend/vendor/normalize.css/normalize.min.css">
  <link rel="stylesheet" href="/themes/backend/vendor/element-ui/theme-chalk/index.min.css">
  <link rel="stylesheet" href="/themes/backend/vendor/vue-material/vue-material.min.css">
  <link rel="stylesheet" href="/themes/backend/vendor/vue-material/theme/default.min.css">
  <link rel="stylesheet" href="/themes/backend/css/july.css">
  @yield('inline-style')
</head>
<body>
  <script src="/themes/backend/js/svg.js"></script>

  <!-- 左侧菜单 -->
  <div id="layout_left">
    <div class="md-scrollbar md-theme-default" id="app_sidebar">
      <div id="app_brand">
        <a href="{{ short_url('admin.home') }}">
          <svg class="jc-logo md-icon"><use xlink:href="#jcon_logo"></use></svg>
          <span>七月 cms</span>
        </a>
      </div>
      <ul class="md-list md-theme-default">
        @foreach (config('app.main_menu') as $item)
        @if ($item['route'])
        <li class="md-list-item{{ under_route($item['route'], Request::getPathInfo())?' is-active':'' }}">
          <a href="{{ short_url($item['route']) }}" class="md-list-item-link md-list-item-container md-button-clean">
            <div class="md-list-item-content">
              <i class="md-icon md-icon-font md-theme-default">{{ $item['icon'] }}</i>
              <span class="md-list-item-text">{{ $item['title'] }}</span>
            </div>
          </a>
        </li>
        @else
        <li class="md-list-item">
          <div class="md-list-item-expand md-list-item-container md-button-clean">
            <div class="md-list-item-content">
              <i class="md-icon md-icon-font md-theme-default">{{ $item['icon'] }}</i>
              <span class="md-list-item-text">{{ $item['title'] }}</span>
              <svg class="md-icon jc-svg-icon md-list-expand-icon"><use xlink:href="#jcon_expand_more"></use></svg>
            </div>
            <div class="md-list-expand">
              <ul class="md-list md-theme-default">
                @foreach ($item['children'] as $child)
                <li class="md-list-item md-inset{{ under_route($child['route'], Request::getPathInfo())?' is-active':'' }}">
                  <a href="{{ short_url($child['route']) }}" class="md-list-item-link md-list-item-container md-button-clean">
                    <div class="md-list-item-content">{{ $child['title'] }}</div>
                  </a>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
        </li>
        @endif
        @endforeach
      </ul>
    </div>
  </div>

  <!-- 右侧内容区 -->
  <div id="layout_right">
    <!-- 右上导航栏 -->
    <nav id="app_navbar">

      <div id="navbar_left">
        <!-- 展开 / 折叠左侧菜单 -->
        <button type="button" title="折叠/展开" class="md-button md-icon-button md-theme-default"
          @click.stop="toggleSidebar">
          <div class="md-ripple">
            <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">menu</i></div>
          </div>
        </button>

        @foreach (config('app.actions') as $action)
        <button type="button" class="md-button md-small md-primary md-theme-default"
          @click.stop="doAction('{{ short_url($action::getRouteName()) }}', '{{ $action::getTitle() }}')">
          <div class="md-ripple">
            <div class="md-button-content">{{ $action::getTitle() }}</div>
          </div>
        </button>
        @endforeach

        {{-- <button type="button" class="md-button md-small md-primary md-theme-default"
          @click.stop="rebuildIndex">
          <div class="md-ripple">
            <div class="md-button-content">重建索引</div>
          </div>
        </button>
        <button type="button" class="md-button md-small md-primary md-theme-default"
          @click.stop="clearCache">
          <div class="md-ripple">
            <div class="md-button-content">清除缓存</div>
          </div>
        </button>
        <button type="button" class="md-button md-small md-primary md-theme-default"
          @click.stop="buildGoogleSitemap">
          <div class="md-ripple">
            <div class="md-button-content">生成谷歌站点地图</div>
          </div>
        </button>
        <a href="{{ short_url('nodes.find_invalid_links') }}" target="_blank" class="md-button md-small md-primary md-theme-default">
          <div class="md-ripple">
            <div class="md-button-content">查找无效链接</div>
          </div>
        </a> --}}
      </div>

      <!-- 导航栏右侧菜单 -->
      <div id="navbar_right">
        <!-- 搜索栏框 -->
        <form action="{{ short_url('action.search') }}" method="GET" id="navbar_search">
          <input type="text" name="keywords" placeholder="搜索">
          <i class="md-icon md-icon-font md-theme-default">search</i>
        </form>

        <!-- 打开网站首页 -->
        <a href="/" target="_blank" class="md-button md-icon-button md-theme-default" title="网站首页">
          <div class="md-ripple">
            <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">home</i></div>
          </div>
        </a>

        <!-- 打开后台首页 -->
        <a href="{{ short_url('admin.home') }}" class="md-button md-icon-button md-theme-default" title="后台首页">
          <div class="md-ripple">
            <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">dashboard</i></div>
          </div>
        </a>

        <!-- 当前用户下拉菜单 -->
        <div class="md-menu jc-dropdown">
          <button type="button" class="jc-primary md-button md-icon-button md-primary md-theme-default"
            @click.stop="toggleAdminMenu">
            <div class="md-ripple">
              <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">person</i></div>
            </div>
          </button>
          <div class="md-menu-content-medium md-menu-content md-theme-default" v-show="adminMenuVisible">
            <div class="md-menu-content-container md-theme-default">
              <ul class="md-list md-theme-default">
                <li class="md-list-item md-menu-item md-theme-default">
                  <a href="#" class="md-list-item-link md-list-item-container md-button-clean"
                    @click.prevent="pwd.dialogVisible = true">
                    <div class="md-list-item-content">修改密码 </div>
                  </a>
                </li>
                <li class="md-list-item md-menu-item md-theme-default">
                  <a href="{{ short_url('admin.logout') }}" class="md-list-item-link md-list-item-container md-button-clean">
                    <div class="md-list-item-content">退出 </div>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <el-dialog
        id="change_pwd"
        ref="change_pwd"
        title="修改密码"
        top="-5vh"
        :close-on-click-modal="false"
        :append-to-body="true"
        :visible.sync="pwd.dialogVisible">
        <el-form :model="pwd.data" status-icon :rules="pwd.rules" ref="pwdForm" label-width="100px">
          <el-form-item label="当前密码" size="small" prop="current_password" :error="pwd.currentPwdError">
            <el-input type="password" native-size="40" v-model="pwd.data.current_password" autocomplete="off"></el-input>
          </el-form-item>
          <el-form-item label="密码" size="small" prop="password">
            <el-input type="password" native-size="40" v-model="pwd.data.password" autocomplete="off"></el-input>
          </el-form-item>
          <el-form-item label="确认密码" size="small" prop="password_confirmation">
            <el-input type="password" native-size="40" v-model="pwd.data.password_confirmation" autocomplete="off"></el-input>
          </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
          <button type="button" class="md-button md-raised md-dense md-theme-default"
            @click.stop="pwd.dialogVisible = false">
            <div class="md-ripple">
              <div class="md-button-content">取 消</div>
            </div>
          </button>
          <button type="button" class="md-button md-raised md-dense md-primary md-theme-default"
            @click.stop="changePwd">
            <div class="md-ripple">
              <div class="md-button-content">确 定</div>
            </div>
          </button>
        </span>
      </el-dialog>
    </nav>

    <!-- 右下功能区 -->
    <div id="app_main" class="jc-scroll-wrapper">
      <div class="jc-scroll md-scrollbar md-theme-default">
        <div id="main_header">
          <h1>@yield('h1', '七月后台')</h1>
          {{-- <div id="translate_btn">@yield('translate_btn')</div> --}}
        </div>
        <div id="main_content">
          @yield('main_content')
        </div>
      </div>
    </div>
  </div>

  {{-- <script src="/themes/backend/vendor/js-base64/base64.min.js"></script> --}}
  <script src="/themes/backend/vendor/md5/md5.min.js"></script>
  <script src="/themes/backend/vendor/moment/moment.min.js"></script>
  <script src="/themes/backend/vendor/ckeditor/ckeditor.js"></script>
  <script src="/themes/backend/js/app.js"></script>
  <script src="/themes/backend/vendor/element-ui/index.js"></script>
  <script src="/themes/backend/js/utils.js"></script>
  <script>
    // 左侧边栏有下级菜单项的点击展开效果
    $('#app_sidebar .md-list-item-expand').each(function() {
      const $item = $(this);
      const $clickableBar = $item.children('.md-list-item-content');
      const $submenu = $item.children('.md-list-expand');

      let isExpanded = false;
      if ($submenu.find('>.md-list>.md-list-item.is-active').length) {
        isExpanded = true
        $item.addClass('md-active')
        $submenu.css('height', 'auto')
      }

      $clickableBar.click(function (e) {
        e.stopPropagation()
        isExpanded = !isExpanded;
        $item.toggleClass('md-active');
        if (isExpanded) {
          $submenu.css('height', 'auto')
        } else {
          $submenu.css('height', 0)
        }
      })
    });

    // 导航栏
    const navbar = new Vue({
      el: '#app_navbar',
      data() {
        var validatePass = (rule, value, callback) => {
          if (value === '') {
            callback(new Error('请输入密码'));
          } else {
            if (this.pwd.data.password_confirmation !== '') {
              this.$refs.pwdForm.validateField('password_confirmation');
            }
            callback();
          }
        };

        var validatePass2 = (rule, value, callback) => {
          if (value === '') {
            callback(new Error('请再次输入密码'));
          } else if (value !== this.pwd.data.password) {
            callback(new Error('两次输入密码不一致!'));
          } else {
            callback();
          }
        };

        return {
          adminMenuVisible: false,
          pwd: {
            data: {
              current_password: '',
              password: '',
              password_confirmation: '',
            },

            rules: {
              current_password: [
                { required:true, message:'请输入当前密码', trigger: 'blur' },
              ],
              password: [
                { min: 8, message: '至少 8 个字符', trigger: 'change' },
                { validator: validatePass, trigger: 'blur' },
              ],
              password_confirmation: [
                { min: 8, message: '至少 8 个字符', trigger: 'change' },
                { validator: validatePass2, trigger: 'blur' },
              ],
            },
            dialogVisible: false,
            currentPassError: '',
          },
        };
      },

      methods: {
        toggleSidebar() {
          $('#layout_left').toggleClass('is-collapsed');
        },

        process(config) {
          const loading = this.$loading({
            lock: true,
            text: config.text || '正在处理 ...',
            background: 'rgba(255, 255, 255, 0.7)',
          });

          return axios[(config.method || 'get')](config.action)
            .then(response => {
              loading.close();
              return response;
            })
            .catch(error => {
              loading.close();
              console.error(error);
              this.$message.error('发生错误！请查看控制台');
              return error;
            });
        },

        doAction(action, title) {
          this.process({
            text: `正在${title} ...`,
            method: 'post',
            action: action,
          }).then(response => {
            status = response.status;
            if (status && status >= 200 && status <= 299) {
              this.$message.success(`已完成：${title}`);
            }
          });
        },

        // {{--
        // rebuildIndex() {
        //   this.process({
        //     text: '正在重建索引 ...',
        //     method: 'post',
        //     action: "{{ short_url('nodes.build_index') }}",
        //   }).then(response => {
        //     status = response.status;
        //     if (status && status >= 200 && status <= 299) {
        //       this.$message.success('重建索引完成');
        //     }
        //   });
        // },

        // clearCache() {
        //   this.process({
        //     text: '正在清除缓存 ...',
        //     method: 'post',
        //     action: "{{ short_url('action.clear_cache') }}",
        //   }).then(response => {
        //     status = response.status;
        //     if (status && status >= 200 && status <= 299) {
        //       this.$message.success('缓存已清除');
        //     }
        //   });
        // },

        // buildGoogleSitemap() {
        //   this.process({
        //     text: '正在生成谷歌站点地图 ...',
        //     method: 'post',
        //     action: "{{ short_url('action.build.google-sitemap') }}",
        //   }).then(response => {
        //     status = response.status;
        //     if (status && status >= 200 && status <= 299) {
        //       this.$message.success('谷歌站点地图已生成');
        //     }
        //   });
        // },
        // --}}

        toggleAdminMenu() {
          this.adminMenuVisible = !this.adminMenuVisible;
          if (this.adminMenuVisible) {
            // 当用户菜单显示时，点击其它任意地方隐藏
            $(document).on('click.admin-toggle', function(e) {
              navbar.toggleAdminMenu();
            });
          } else {
            $(document).off('click.admin-toggle');
          }
        },

        changePwd() {
          this.pwd.currentPassError = '';

          const loading = this.$loading({
            lock: true,
            text: '正在修改密码 ...',
            background: 'rgba(0, 0, 0, 0.7)',
          });

          const form = this.$refs.pwdForm;
          form.validate().then(() => {
            axios.post("{{ short_url('action.change_password') }}", clone(this.pwd.data)).then(response => {
              loading.close();
              console.log(response)
              if (response.status === 200) {
                this.pwd.dialogVisible = false;
                this.$message.success('密码修改成功');
              } else {
                this.pwd.currentPassError = '密码错误';
              }
            }).catch(error => {
              loading.close();
              console.error(error);
              this.$message.error('发生错误！请查看控制台');
            });
          }).catch(function(error) {
            loading.close();
          });
        },
      },
    });
  </script>

  @yield('script')
</body>
</html>
