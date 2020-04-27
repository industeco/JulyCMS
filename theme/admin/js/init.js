/* 初始化界面框架 */
(function() {

  let $sidebar = $('#sidebar_wrapper');
  $('#nav_toggle').click(function() {
    $sidebar.toggleClass('is-collapsed')
  })

  let $adminMenu = $('#nav_admin_menu');
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
    let $item = $(this);
    let $btn = $item.children('.md-list-item-content');
    let $menuWrapper = $item.children('.md-list-expand');
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

  // 测试
  // let activeItem = null;
  // $('#sidebar .md-list-item-link').click(function(e) {
  //   e.preventDefault()
  //   if (activeItem) {
  //     activeItem.parentNode.classList.remove('is-active')
  //   }
  //   if (activeItem === this) {
  //     activeItem = null
  //   } else {
  //     activeItem = this
  //     this.parentNode.classList.add('is-active')
  //   }
  // })

})();
