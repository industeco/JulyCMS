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


function clone(obj) {
  return JSON.parse(JSON.stringify(obj))
}

// 折叠数据
function toTree(records) {
  if (!records || !records.length) {
    return []
  }

  const recsByKey = {};
  records.forEach(record => {
    recsByKey[record.node_id] = record
  })

  let treeData = [];
  for (const id in recsByKey) {
    const node = recsByKey[id]
    if (node.parent_id != null) {
      const parent = recsByKey[node.parent_id];
      if (! parent.children) {
        parent.children = []
      }
      parent.children.push(node)
    } else {
      treeData.push(node)
    }
  }

  return treeData
}

// 拆分数据
function toRecords(treeData, records, parent, prev, path) {
  if (records == null) {
    records = []
    parent = null
    prev = null
    path = '/'
  }

  treeData.forEach(node => {
    records.push({
      node_id: node.node_id,
      parent_id: parent || null,
      prev_id: prev || null,
      path: path,
    })
    if (node.children && node.children.length) {
      toRecords(node.children, records, node.node_id, null, path + node.node_id + '/')
    }
    prev = node.node_id
  });

  return records
}

function isEmptyObject(obj) {
  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      return false
    }
  }
  return true
}

function stringify(tar) {
  return JSON.stringify(tar, (prop, val) => {
    const _type = typeof val;
    if(_type == 'string') {
      val = val.trim();
      if(!isNaN(val*1)) val = val*1;
    } else if (_type == 'boolean') {
      val = val*1;
    }
    return val
  })
}

function isEqual(v1, v2) {
  return v1 == v2 || stringify(v1) == stringify(v2)
}
