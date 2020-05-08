
function clone(obj) {
  return JSON.parse(JSON.stringify(obj))
}

// 折叠数据
function toTree(nodes) {
  nodes = nodes || [];
  if (nodes.length <= 1) {
    return nodes;
  }

  const nodesById = {
    0: {},
  };

  nodes.forEach(node => {
    nodesById[node.node_id] = node;
  })

  nodes.forEach(node => {
    if (node.prev_id) {
      nodesById[node.prev_id].next_id = node.node_id;
    } else {
      const parent_id = node.parent_id || 0;
      nodesById[parent_id].child_id = node.node_id;
    }
  })

  return getChildNodes(nodesById, 0);
}

function getChildNodes(nodes, parent_id) {
  const children = [];
  const parent = nodes[parent_id];
  let node = nodes[parent.child_id];
  while (node) {
    children.push(node);
    node.children = getChildNodes(nodes, node.node_id);
    node = nodes[node.next_id];
  }
  return children;
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
