# code-review-server

Deploy: fab -H root@reviewcode.cn deploy

Install dependencies: composer install, composer update

# API

## user

移除用户擅长或想学领域

```
DELETE /user/tags/26
```

返回剩余的 tags 数组

```
{"code":0,"result":[],"error":""}
```
