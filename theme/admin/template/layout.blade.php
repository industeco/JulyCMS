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
  <div id="sidebar_wrapper">
    <div class="md-scrollbar md-theme-default" id="sidebar">
      <div id="sidebar_brand">
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
  <div id="main_wrapper">
    <!-- 右上导航栏 -->
    <nav id="nav">

      <!-- 展开 / 折叠左侧菜单 -->
      <button type="button" class="md-button md-icon-button md-theme-default" id="nav_toggle" title="折叠/展开">
        <!-- <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_menu"></use></svg> -->
        <i class="md-icon md-icon-font md-theme-default">menu</i>
      </button>

      <!-- 导航栏右侧菜单 -->
      <div id="nav_right">

        <!-- 搜索栏框 -->
        <form action="/search.php" method="GET" id="nav_search">
          <input type="text" name="keywords" placeholder="搜索" disabled>
          <!-- <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_search"></use></svg> -->
          <i class="md-icon md-icon-font md-theme-default">search</i>
        </form>

        <!-- 打开网站首页 -->
        <a href="/" target="_blank" class="md-button md-icon-button md-theme-default" title="网站首页">
          <!-- <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_home"></use></svg> -->
          <i class="md-icon md-icon-font md-theme-default">home</i>
        </a>

        <!-- 打开后台首页 -->
        <a href="/admin" class="md-button md-icon-button md-theme-default" title="后台首页">
          <!-- <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_dashboard"></use></svg> -->
          <i class="md-icon md-icon-font md-theme-default">dashboard</i>
        </a>

        <!-- 当前用户下拉菜单 -->
        <div class="md-menu jc-dropdown">
          <button type="button" class="jc-primary md-button md-icon-button md-primary md-theme-default" id="nav_admin_btn">
            <!-- <svg class="md-icon jc-svg-icon"><use xlink:href="#jcon_person"></use></svg> -->
            <i class="md-icon md-icon-font md-theme-default">person</i>
          </button>
          <div class="md-menu-content-medium md-menu-content md-theme-default" id="nav_admin_menu" style="display: none;">
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
    <div class="md-scrollbar md-theme-default" id="main">
      <div id="main_header">
        <h1>@yield('h1', '七月 CMS 后台')</h1>
        <div id="translate_btn">@yield('translate_btn')</div>
      </div>
      <div id="main_content">
        @yield('main_content')
        {{-- <p>{{ Request::getPathInfo() }}</p> --}}
      </div>
    </div>
  </div>

  <script src="/theme/admin/vendor/moment/moment.min.js"></script>
  <script src="/theme/admin/js/app.js"></script>
  <script src="/theme/admin/vendor/element-ui/index.js"></script>

  <!-- CDNJS :: Sortable (https://cdnjs.com/) -->
  {{-- <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.8.4/Sortable.min.js"></script> --}}
  <!-- CDNJS :: Vue.Draggable (https://cdnjs.com/) -->
  {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.20.0/vuedraggable.umd.min.js"></script> --}}

  <!-- <script src="../node_modules/vue-material/dist/vue-material.js"></script> -->

  <!-- <script src="https://cdn.ckeditor.com/4.10.0/standard/ckeditor.js"></script> -->
  <!-- <script src="https://unpkg.com/vue-ckeditor2"></script> -->

  <script src="/theme/admin/js/menu.js"></script>
  <script src="/theme/admin/js/utils.js"></script>

  @yield('script')

</body>
</html>
