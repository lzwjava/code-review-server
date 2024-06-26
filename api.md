# code-review-server

Deploy: fab -H root@reviewcode.cn deploy

Install dependencies: composer install, composer update

# API

### 快速参考

描述 |方法|请求  |参数|返回
-----|----|------|----|----
发送验证码|POST|/user/requestSmsCode|mobilePhoneNumber|
注册|POST|/user/register|mobilePhoneNumber,username,smsCode,password,type| 注册的用户
登录|POST|/user/login |mobilePhoneNumber,password|登录用户
更新用户信息|PATCH|/user|company,jobTitle,gitHubUsername,introduction,avatarUrl,maxOrders...|更新后的用户
获取当前用户|GET|/user/self||当前用户
移除领域|DELETE| /user/tags/:tagId|| 剩余的 tags 数组
添加领域|POST|/user/tags |tagId| 当前 tags 数组
创建审核订单|POST|/orders|gitHubUrl,remark,reviewerId,codeLines|新创建的订单
查看我的订单|GET|/user/orders|status,skip,limit|订单数组
查看一个订单|GET|/orders/:orderId||
接手订单|POST|/orders/:orderId | status=consented |
拒绝订单|POST|/orders/:orderId | status=rejected |
打赏|POST|orders/:orderId/reward|amount
七牛token|GET|/qiniu/token||
大神列表|GET|/reviewers |skip,limit|
查看一个大神|GET|/reviewers/:reviewerId||
创建审核|POST|/reviews|orderId,content,title|
更新审核|PATCH|/reviews/:reviewId|content,title|
精选审核案例|GET|/reviews | displaying,skip,limit|
一个大神的审核案例|GET|/reviewers/:reviewerId/reviews | skip,limit|
记录案例页阅读数|POST|/reviews/:reviewId/visits|referrer|
获取视频列表|GET|/videos||
记录视频访问次数|POST|/videos/:videoId/visits|referrer|
未付款时取消订单|DELETE|/orders/:orderId||
请求重置密码|POST|/user/requestResetPassword|mobilePhoneNumber|
重置密码|POST|/user/resetPassword|mobilePhoneNumber,smsCode,password|

## user

移除用户擅长或想学领域

```
DELETE /user/tags/10
```

返回剩余的 tags 数组

```
{"code":0,"result":[],"error":""}
```
