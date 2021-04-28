
function clone(obj) {
  return _.cloneDeep(obj);
}

function isEmptyObject(obj) {
  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      return false;
    }
  }
  return true;
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
    return val;
  })
}

function isEqual(v1, v2) {
  const a1 = _.isArray(v1);
  const a2 = _.isArray(v2);
  if (a1 || a2) {
    if (a1 && a2) {
      if (v1.length !== v2.length) {
        return false;
      }
      return _.difference(v1, v2).length === 0;
    }
    return false;
  }
  // return v1 == v2 || stringify(v1) == stringify(v2);
  return _.isEqual(v1, v2);
}

// 检查 id 是否已存在
function unique(action) {
  return function(rule, value, callback) {
    axios.get(action.replace('_ID_', value)).then(function(response) {
      if (response.data.exists) {
        callback(new Error('id 已存在'));
      } else {
        callback();
      }
    }).catch(function(error) {
      console.error(error);
    });
  };
}

// 检查是否已存在
function exists(action, current, except) {
  except = except == null ? null : except;
  return function(rule, value, callback) {
    if (!value || value == current) {
      return callback();
    }
    axios.post(action, {value:value,except:except}).then(function(response) {
      if (response.data.exists) {
        callback(new Error('已存在'));
      } else {
        callback();
      }
    }).catch(function(error) {
      console.error(error);
    });
  };
}
