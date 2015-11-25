!function(e){function t(o){if(n[o])return n[o].exports;var i=n[o]={exports:{},id:o,loaded:!1};return e[o].call(i.exports,i,i.exports,t),i.loaded=!0,i.exports}var n={};return t.m=e,t.c=n,t.p="",t(0)}([function(e,t,n){"use strict";function o(e){return e&&e.__esModule?e:{"default":e}}var i=n(1),r=o(i),a=n(2),s=o(a),l=n(3),u=o(l),d=n(4),c=o(d),m=n(7),f=o(m),p=n(8),h=o(p),g=n(9),v=o(g),b=n(10),w=o(b);angular.module("webProject",["ngAnimate","ngCookies","ngSanitize","ui.router","ngMaterial"]).constant("lcConfig",r["default"]).constant("moment",window.moment).config(s["default"]).config(u["default"]).run(c["default"]).service("commonSer",f["default"]).service("helloSer",h["default"]).directive("lcHeader",v["default"]).controller("homeCtrl",w["default"])},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n={httpTimeout:2e4,apiHost:"http://localhost:3000",host:"http://localhost:9000"},o=window.location.host;"http://"+o!==n.host&&(n.apiHost="http://"+o),t["default"]=n,e.exports=t["default"]},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t["default"]=["$httpProvider","lcConfig",function(e,t){"ngInject";var n=t.httpTimeout,o=t.apiHost;e.defaults.withCredentials=!0,e.defaults.headers["delete"]={"Content-Type":"application/json;charset=utf-8"},e.interceptors.push([function(){return{request:function(e){return e.timeout=n,/^[http|https]/.test(e.url)||/\.html$/.test(e.url)||(e.url=o+e.url),e},response:function(e){return/\.html/.test(e.config.url)?e:e.data},responseError:function(e){return Promise.reject(e.data)}}}])}],e.exports=t["default"]},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t["default"]=["$stateProvider","$urlRouterProvider","$locationProvider",function(e,t,n){"ngInject";n.html5Mode(!0),e.state("home",{url:"/",templateUrl:"app/auth/home/home.html",controller:"homeCtrl"}),t.otherwise("/")}],e.exports=t["default"]},function(e,t,n){"use strict";function o(e){return e&&e.__esModule?e:{"default":e}}Object.defineProperty(t,"__esModule",{value:!0});var i=n(5),r=o(i),a=n(6),s=o(a),l={zhCn:r["default"],en:s["default"]},u="zhCn",d=function(e,t){"ngInject";e.i18n=l[u],e.lang=u,e.$watch("lang",function(){t.$.extend(e.i18n,l[e.lang])})};d.$inject=["$rootScope","$window"],t["default"]=d,e.exports=t["default"]},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t["default"]={title:"标题",name:"姓名",nickname:"昵称",username:"用户名",email:"邮箱",admin:"负责",op:"操作",kind:"类别",add:"添加",number:"编号",status:"状态",updateTime:"更新时间","null":"空",leftParenthesis:"（",rightParenthesis:"）",btn:{success:"完成",submit:"提交",edit:"修改","delete":"删除"},auth:{register:"注册",login:"登录"},header:{setting:"设置",logout:"登出",search:"搜索问题试试",ask:"我要提问"}},e.exports=t["default"]},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t["default"]={auth:{loginBtn:"Login By LeanCloud"}},e.exports=t["default"]},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t["default"]=["$http","$state","lcConfig","$window",function(e,t,n,o){"ngInject";return{goHome:function(){t.go("home")},redirect:function(e){o.location.href=e}}}],e.exports=t["default"]},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t["default"]=["$http","$window","lcConfig","$state",function(e,t,n,o){"ngInject";return{getData:function(){return e({method:"get",url:"/api/reviewers"})}}}],e.exports=t["default"]},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t["default"]=["authSer","commonSer","$rootScope","$state",function(e,t,n,o){"ngInject";return{restrict:"E",templateUrl:"app/common/header/header.html",scope:!0,replace:!0,link:function(e){}}}],e.exports=t["default"]},function(e,t){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t["default"]=["$scope","$window","$http","commonSer","helloSer",function(e,t,n,o,i){"ngInject";e.reviewers=[],e.goHome=function(){o.goHome()},e.showRegisterModel=function(){n({method:"get",url:"/api/login/github"}).then(function(e){console.log(e),t.location.href=e.url})},i.getData().then(function(t){e.reviewers=t}),e.getReviewer=function(t){return e.reviewers[t]},e.registerSite=function(){{var t=e.username;e.password}console.log(t)}}],e.exports=t["default"]}]),angular.module("webProject").run(["$templateCache",function(e){e.put("app/auth/home/home.html",'<div class="auth-home-module"><div class="block1"><div class="logo">Code Review</div><div class="btns"><md-button class="registerBtn lc-btn" ng-click="showRegisterModel()">{{i18n.auth.register}}</md-button><md-button class="loginBtn lc-btn md-raised light">{{i18n.auth.login}}</md-button></div></div><div class="reviewer" ng-repeat="reviewer in reviewers"><div class="reviewer-image"><a target="_blank" herf=""><img ng-src="{{reviewer.logo.url}}"><div class="reviewer-detail"><a class="reviewer-name">{{reviewer.name}}</a><div class="reviewer-description">{{reviewer.desc}}</div></div></a></div></div><div class="sample-image"><a target="_blank" href="https://github.com/lilei644/LLBootstrapButton/issues/1"><img src="../../../assets/sample.png"></a></div></div><div class="modal fade" id="modal-register" tabindex="-1" role="dialog" aria-labelledby="modal-register-label" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button><h4 class="modal-title" id="modal-register-label">注册</h4></div><form class="signup-form" ng-submit="registerSite()"><div class="error" style="display:none"></div><input type="text" id="signup-username" placeholder="Username" ng-model="username"> <input type="password" id="signup-password" placeholder="Create a Password" ng-model="password"><md-button>注册</md-button></form><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">关闭</button></div></div></div></div>'),e.put("app/common/header/header.html",'<div class="common-header-module"><div class="header-content"><div class="logo" ng-click="goHome();">Code Review</div><md-menu ng-if="user.username" class="account-menu" md-position-mode="target-right target" md-offset="0 48"><md-button md-menu-origin="" class="lc-btn" ng-click="openMenu($mdOpenMenu, $event)">{{user.username}}</md-button><md-menu-content><md-menu-item><md-button>{{i18n.header.setting}}</md-button></md-menu-item><md-menu-item><md-button>{{i18n.header.logout}}</md-button></md-menu-item></md-menu-content></md-menu><md-button ng-if="!user.username" class="loginBtn" ng-click="">{{i18n.auth.login}}</md-button><div class="search-input" ng-if="ui.showAskBtn"><md-button class="md-icon-button searchBtn" ng-click="" aria-label="close"><span></span></md-button><input class="search" type="text" placeholder="{{i18n.header.search}}"></div><md-button class="askBtn lc-btn md-raised" ng-click="creatTicket()" ng-if="ui.showAskBtn"><span class="ask-icon"></span> <span>{{i18n.header.ask}}</span></md-button></div></div>')}]);