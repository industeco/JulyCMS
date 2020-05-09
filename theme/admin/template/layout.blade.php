<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', '七月 CMS')</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700,400italic|Material+Icons">
  <link rel="stylesheet" href="/theme/admin/vendor/normalize.css/normalize.css">
  <link rel="stylesheet" href="/theme/admin/vendor/vue-material/vue-material.css">
  <link rel="stylesheet" href="/theme/admin/vendor/vue-material/theme/default.css">
  <link rel="stylesheet" href="/theme/admin/vendor/element-ui/theme-chalk/index.css">
  <link rel="stylesheet" href="/theme/admin/css/july.css">
</head>
<body>
  <script src="/theme/admin/js/svg.js"></script>

  <!-- 左侧菜单 -->
  <div id="layout_left">
    <div class="md-scrollbar md-theme-default" id="app_sidebar">
      <div id="app_brand">
        <a href="/admin">
          <svg class="jc-logo md-icon"><use xlink:href="#jcon_logo"></use></svg>
          <span>七月 cms</span>
        </a>
      </div>

      <ul class="md-list md-theme-default">
        <li class="md-list-item{{ under_route(Request::getPathInfo(),'/admin/nodes')?' is-active':'' }}">
          <a href="/admin/nodes" class="md-list-item-link md-list-item-container md-button-clean">
            <div class="md-list-item-content">
              <i class="md-icon md-icon-font md-theme-default">create</i>
              <span class="md-list-item-text">内容</span>
            </div>
          </a>
        </li>
        <li class="md-list-item{{ under_route(Request::getPathInfo(),'/admin/node_types')?' is-active':'' }}">
          <a href="/admin/node_types" class="md-list-item-link md-list-item-container md-button-clean">
            <div class="md-list-item-content">
              <i class="md-icon md-icon-font md-theme-default">category</i>
              <span class="md-list-item-text">类型</span>
            </div>
          </a>
        </li>
        <li class="md-list-item">
          <div class="md-list-item-expand md-list-item-container md-button-clean">
            <div class="md-list-item-content">
              <i class="md-icon md-icon-font md-theme-default">device_hub</i>
              <span class="md-list-item-text">结构</span>
              <svg class="md-icon jc-svg-icon md-list-expand-icon"><use xlink:href="#jcon_expand_more"></use></svg>
            </div>
            <div class="md-list-expand">
              <ul class="md-list md-theme-default">
                <li class="md-list-item md-inset{{ under_route(Request::getPathInfo(),'/admin/catalogs')?' is-active':'' }}">
                  <a href="/admin/catalogs" class="md-list-item-link md-list-item-container md-button-clean">
                    <div class="md-list-item-content">目录 </div>
                  </a>
                </li>
                <li class="md-list-item md-inset{{ under_route(Request::getPathInfo(),'/admin/tags')?' is-active':'' }}">
                  <a href="/admin/tags" class="md-list-item-link md-list-item-container md-button-clean">
                    <div class="md-list-item-content">标签 </div>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </li>
        <li class="md-list-item{{ under_route(Request::getPathInfo(),'/admin/medias')?' is-active':'' }}">
          <a href="/admin/medias" class="md-list-item-link md-list-item-container md-button-clean">
            <div class="md-list-item-content">
              <i class="md-icon md-icon-font md-theme-default">folder</i>
              <span class="md-list-item-text">文件</span>
            </div>
          </a>
        </li>
        <li class="md-list-item{{ under_route(Request::getPathInfo(),'/admin/configs')?' is-active':'' }}">
          <a href="/admin/configs" class="md-list-item-link md-list-item-container md-button-clean">
            <div class="md-list-item-content">
              <i class="md-icon md-icon-font md-theme-default">settings</i>
              <span class="md-list-item-text">设置</span>
            </div>
          </a>
        </li>
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
          @click="toggleSidebar">
          <!-- <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_menu"></use></svg> -->
          <div class="md-ripple">
            <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">menu</i></div>
          </div>
        </button>
        <button type="button" class="md-button md-small md-primary md-theme-default"
          @click="rebuildIndex">
          <!-- <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_menu"></use></svg> -->
          <div class="md-ripple">
            <div class="md-button-content">重建索引</div>
          </div>
        </button>
        <button type="button" class="md-button md-small md-primary md-theme-default"
          @click="clearCache">
          <!-- <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_menu"></use></svg> -->
          <div class="md-ripple">
            <div class="md-button-content">清除缓存</div>
          </div>
        </button>
      </div>

      <!-- 导航栏右侧菜单 -->
      <div id="navbar_right">
        <!-- 搜索栏框 -->
        <form action="/search.php" method="GET" id="navbar_search">
          <input type="text" name="keywords" placeholder="搜索（暂不可用）" disabled>
          <i class="md-icon md-icon-font md-theme-default">search</i>
        </form>

        <!-- 打开网站首页 -->
        <a href="/" target="_blank" class="md-button md-icon-button md-theme-default" title="网站首页">
          <div class="md-ripple">
            <div class="md-button-content"><i class="md-icon md-icon-font md-theme-default">home</i></div>
          </div>
        </a>

        <!-- 打开后台首页 -->
        <a href="/admin" class="md-button md-icon-button md-theme-default" title="后台首页">
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
                  <a href="/admin/password" class="md-list-item-link md-list-item-container md-button-clean">
                    <div class="md-list-item-content">修改密码 </div>
                  </a>
                </li>
                <li class="md-list-item md-menu-item md-theme-default">
                  <a href="/admin/logout" class="md-list-item-link md-list-item-container md-button-clean">
                    <div class="md-list-item-content">退出 </div>
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
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

  <script src="/theme/admin/vendor/moment/moment.min.js"></script>
  <script src="/theme/admin/js/app.js"></script>
  <script src="/theme/admin/vendor/element-ui/index.js"></script>
  <script src="/theme/admin/js/utils.js"></script>
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
        return {
          adminMenuVisible: false,
        };
      },

      methods: {
        toggleSidebar() {
          $('#layout_left').toggleClass('is-collapsed');
        },

        rebuildIndex() {
          const loading = this.$loading({
            lock: true,
            text: '正在重建索引 ...',
            background: 'rgba(255, 255, 255, 0.7)',
          });

          axios.get('/admin/cmd/rebuildindex').then(response => {
            loading.close();
            console.log(response);
            this.$message.success('重建索引完成');
          }).catch(error => {
            loading.close();
            console.error(error);
            this.$message.error('重建索引失败');
          });
        },

        clearCache() {
          const loading = this.$loading({
            lock: true,
            text: '正在清除缓存 ...',
            background: 'rgba(255, 255, 255, 0.7)',
          });

          axios.get('/admin/cmd/clearcache').then(response => {
            loading.close();
            console.log(response);
            this.$message.success('缓存已清除');
          }).catch(error => {
            loading.close();
            console.error(error);
            this.$message.error('后台错误');
          });
        },

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
      },
    });
  </script>

  @yield('script')
</body>
</html>
