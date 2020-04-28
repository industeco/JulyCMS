/* 初始化界面框架 */
(function() {
  const $sidebar = $('#sidebar_wrapper');
  $('#nav_toggle').click(function() {
    $sidebar.toggleClass('is-collapsed')
  })

  const $adminMenu = $('#nav_admin_menu');
  let isAdminMenuOnShow = false;
  $('#nav_admin_btn').click(function(e) {
    e.stopPropagation()
    isAdminMenuOnShow = !isAdminMenuOnShow
    $adminMenu.toggle()
  })

  $(document).on('click.admin-toggle', function(e) {
    e.stopPropagation()
    if (isAdminMenuOnShow) {
      isAdminMenuOnShow = false
      $adminMenu.hide()
    }
  })

  $('#sidebar .md-list-item-expand').each(function() {
    const $item = $(this);
    const $btn = $item.children('.md-list-item-content');
    const $menuWrapper = $item.children('.md-list-expand');
    let isExpanded = false;
    if ($menuWrapper.find('>.md-list>.md-list-item.is-active').length) {
      isExpanded = true
      $item.addClass('md-active')
      $menuWrapper.css('height', 'auto')
    }

    $btn.click(function (e) {
      e.stopPropagation()
      isExpanded = !isExpanded;
      $item.toggleClass('md-active');
      if (isExpanded) {
        $menuWrapper.css('height', 'auto')
      } else {
        $menuWrapper.css('height', 0)
      }
    })
  })
})();
