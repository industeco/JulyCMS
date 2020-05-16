/* 初始化界面框架 */
(function() {
  切换左侧边栏
  const $sidebar = $('#layout_left');
  $('#navbar_toggle').click(function() {
    $sidebar.toggleClass('is-collapsed');
  });

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

  // 切换显示用户菜单
  const $adminMenu = $('#navbar_admin_menu');
  let isAdminMenuOnShow = false;
  $('#navbar_admin_btn').click(function(e) {
    e.stopPropagation()
    isAdminMenuOnShow = !isAdminMenuOnShow;
    $adminMenu.toggle();
  });

  // 当用户菜单显示时，点击其它任意地方隐藏
  $(document).on('click.admin-toggle', function(e) {
    e.stopPropagation()
    if (isAdminMenuOnShow) {
      isAdminMenuOnShow = false
      $adminMenu.hide()
    }
  });
})();
