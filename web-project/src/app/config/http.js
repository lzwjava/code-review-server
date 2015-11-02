/**
* 每位工程师都有保持代码优雅的义务
* Each engineer has a duty to keep the code elegant
*
* @author wangxiao
*/

export default ($httpProvider, lcConfig) => {
  'ngInject';

  const httpTimeout = lcConfig.httpTimeout;
  const apiHost = lcConfig.apiHost;

  $httpProvider.defaults.withCredentials = true;
  // delete 可以携带 josn 数据。
  $httpProvider.defaults.headers.delete = {
    'Content-Type': 'application/json;charset=utf-8'
  };

  // 全局 $http 请求配置。
  $httpProvider.interceptors.push([() => {
    return {
      request: (config) => {
          config.timeout = httpTimeout;

          // 当 url 中没有 http 或者 https 的时候，自动拼接默认的 apiHost
          if (!/^[http|https]/.test(config.url) && !/\.html$/.test(config.url)) {
              config.url = apiHost + config.url;
          }
          return config;
      },
      response: (response) => {
          if (/\.html/.test(response.config.url)) {
              return response;
          } else {
              return response.data;
          }
      },
      responseError: function(response) {
        return Promise.reject(response.data);
      }
    };
  }]);

};
