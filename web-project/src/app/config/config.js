/**
* 每位工程师都有保持代码优雅的义务
* Each engineer has a duty to keep the code elegant
*
* @author wangxiao
*/

let config = {

  // http 请求超时时间
  httpTimeout: 20000,

  // Api 的请求地址
  apiHost: 'http://localhost:3000',
};

// 判断是否为开发环境
if (window.location.host !== 'localhost:9000') {
  config.apiHost = 'http://' + window.location.host;
}

export default config;
