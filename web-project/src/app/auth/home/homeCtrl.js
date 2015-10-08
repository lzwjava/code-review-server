/**
* 每位工程师都有保持代码优雅的义务
* Each engineer has a duty to keep the code elegant
*
* @author wangxiao
*/

export default ($scope, $window, authSer, commonSer) => {
  'ngInject';
  $scope.login = () => {
    authSer.login();
  };

  $scope.goHome = () => {
    commonSer.goHome();
  };
};
