/**
 * 每位工程师都有保持代码优雅的义务
 * Each engineer has a duty to keep the code elegant
 *
 * @author wangxiao
 */

// 所有的配置

'use strict';

let config = {

  // 服务端 host
  host: 'http://localhost:3000',

  // web 开发环境的 host
  webHost: 'http://localhost:9000',

  // 跨域白名单
  whiteOrigins: [
    'http://localhost:9000',
    'http://localhost:3000',
    // 以下两个是在 LeanCloud 中配置的 host，xxx 替换为自己的域名
    'http://dev.xxx.avosapps.com',
    'http://xxx.avosapps.com'
  ]
};

// 判断环境
switch (process.env.LC_APP_ENV) {

  // 当前环境为线上测试环境
  case 'stage':
    config.host = 'http://dev.xxx.avosapps.com';
    config.webHost = 'http://dev.xxx.avosapps.com';
  break;

  // 当前环境为线上正式运行的环境
  case 'production':
    config.host = 'http://xxx.avosapps.com';
    config.webHost = 'http://xxx.avosapps.com';
  break;
}

module.exports = config;
