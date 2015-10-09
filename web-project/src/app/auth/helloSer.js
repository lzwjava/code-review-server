/**
* 每位工程师都有保持代码优雅的义务
* Each engineer has a duty to keep the code elegant
*
* @author wangxiao
*/

export default ($http, $window, lcConfig, $state) => {
  'ngInject';
  return {
    getData: () => {
      return $http({
        method: 'get',

        // api 请求的默认host 等设置在 config http.js 中
        url: '/api/hello'
      });
    }
  };
};

