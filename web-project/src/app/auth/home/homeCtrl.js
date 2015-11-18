/**
* 每位工程师都有保持代码优雅的义务
* Each engineer has a duty to keep the code elegant
*
* @author wangxiao
*/

export default ($scope, $window, commonSer, helloSer) => {
  'ngInject';
  $scope.reviewers = [];

  $scope.goHome = () => {
    commonSer.goHome();
  };

  helloSer.getData().then((data) => {
    $scope.reviewers = data;
  });

  $scope.getReviewer = (index) => {
    return $scope.reviewers[index];
  }
};
