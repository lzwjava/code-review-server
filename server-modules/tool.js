/**
 * 每位工程师都有保持代码优雅的义务
 * Each engineer has a duty to keep the code elegant
 *
 * @author wangxiao
 */

// 一些工具方法

'use strict';

const error = require('./error');

let pub = {};

pub.l = (msg) => {
  console.log('\n\n', msg, '\n\n');
};

// 校验参数是否有为空
pub.rejectEmptyParam = (res, arr) => {
  let result = false;
  arr.forEach((v) => {
    if (typeof v === 'string') {
      if (!v.trim()) {
        result = true;
      }
    } else {
      if (!v) {
        result = true;
      }
    }
  });
  if (result) {
    const err = error.common.loseParam;
    res.status(err.status).send({
      err: err.status,
      msg: err.msg
    });
  }
  return result;
};

pub.fail = (res, err) => {
  res.status(err.status).send({
    err: err.status,
    msg: err.msg
  });
};

module.exports = pub;
