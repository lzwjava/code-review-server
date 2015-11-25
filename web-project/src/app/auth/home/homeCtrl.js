/**
* 每位工程师都有保持代码优雅的义务
* Each engineer has a duty to keep the code elegant
*
* @author wangxiao
*/

export default ($scope, $window, $http, commonSer, helloSer) => {
  'ngInject';
  $scope.reviewers = [];

  $scope.goHome = () => {
    commonSer.goHome();
  };

  $scope.showRegisterModel = () => {
    $http({
      method: 'get',
      url: '/api/login/github'
    }).then((data) => {
      console.log(data);
      $window.location.href = data.url;
    });
  };

  helloSer.getData().then((data) => {
    $scope.reviewers = data;
  });

  $scope.getReviewer = (index) => {
    return $scope.reviewers[index];
  };

  $scope.registerSite = () => {
    var username = $scope.username;
    var password = $scope.password;
    console.log(username);
  };
};
