
// 折叠数据
function toTree(records) {
  const recsByKey = {};
  records.forEach(record => {
    recsByKey[record.id] = record
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
function toRecords(treeData, records, parent, prev) {
  if (records == null) {
    records = []
    parent = null
    prev = null
  }

  treeData.forEach(node => {
    records.push({
      id: node.id,
      parent_id: parent || null,
      prev_id: prev || null,
    })
    if (node.children && node.children.length) {
      toRecords(node.children, records, node.id, null)
    }
    prev = node.id
  });

  return records
}
