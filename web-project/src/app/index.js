/**
* 每位工程师都有保持代码优雅的义务
* Each engineer has a duty to keep the code elegant
*
* @author wangxiao
*/

import config from './config/config';
import httpConfig from './config/http';
import routerConfig from './config/route';
import i18nConfig from './i18n/config';

// service
import commonSer from './common/commonSer';
import helloSer from './auth/helloSer';

// directive
import headerDirect from './common/header/headerDirect';

// controller
import homeCtrl from './auth/home/homeCtrl';

angular.module('webProject',
  ['ngAnimate', 'ngCookies', 'ngSanitize', 'ui.router', 'ngMaterial'])

  // 配置全局常量
  .constant('lcConfig', config)
  .constant('moment', window.moment)

  // 基础配置
  .config(httpConfig)
  .config(routerConfig)

  // 自动执行
  .run(i18nConfig)

  // services 初始化
  .service('commonSer', commonSer)
  .service('helloSer', helloSer)

  // directive 初始化
  .directive('lcHeader', headerDirect)

  // controller 初始化
  .controller('homeCtrl', homeCtrl);

