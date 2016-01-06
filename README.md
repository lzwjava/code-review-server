# code-review-server

Deploy: fab -H root@reviewcode.cn deploy

Install dependencies: composer install, composer update

# API

### 快速参考

描述 | 请求  | 返回
-----|------|--------------
移除领域| DELETE /user/tags/:tagId | 剩余的 tags 数组
添加领域| POST /user/tags  tagId=10| 当前 tags 数组

## user

移除用户擅长或想学领域

```
DELETE /user/tags/10
```

返回剩余的 tags 数组

```
{"code":0,"result":[],"error":""}
```
