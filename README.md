# LeanEngine-Full-Stack

该项目为基于 [LeanCloud](http://leancloud.cn) 提供的 Nodejs 服务 [LeanEngine](https://leancloud.cn/docs/leanengine_guide-node.html) 的 Web 全栈开发的技术解决方案。

## 主要特点

基于通用技术方案，与 LeanEngine 紧密结合，将基础架构、自动化构建、国际化方案等底层技术问题的解决方案都已经组织在一起。用户可以通过最简单的方式，直接开始自己的开发。

## 技术栈

JavaScript 全部代码基于 ECMAScript6（ES6）。

后端运行环境基于 LeanEngine Nodejs 环境，依赖安装通过 npm，服务框架主要基于 Express 4.x 。

Web 前端自动化方案主要基于 Gulp，框架基于 Angular 1.4.x，UI 框架主要基于 Angular Material，依赖安装通过 bower，样式通过编写 SASS 而非直接写 CSS 文件。

## 开发方式

首先确认本机已经安装 [Node.js](http://nodejs.org/) 运行环境和 [LeanCloud 命令行工具](https://leancloud.cn/docs/cloud_code_commandline.html)，之后按照以下方式开始您的开发：

### 依赖安装

* 首先 clone 这个代码库到本地目录中
```
$ git clone git@github.com:leancloud/LeanEngine-Full-Stack.git
$ cd LeanEngine-Full-Stack
```
* 在该项目`根目录`执行 `npm install` 安装服务端环境依赖
* 在 `web-project 目录`中执行 `npm install` 安装 Web 端构建依赖
* 在 `web-project 目录`中执行 `bower install` 安全 Web 端基础库

### 调试

* 在根目录执行 `avoscloud` 运行服务器端环境，通过 `http://localhost:3000/api/hello` 可以测试
* 在 web-project 目录中执行 `gulp serve` 运行 web 端环境，通过 `http://localhost:9000` 可以调试
* 开发时需要同时运行这两个服务，就可以调试 Web 与 Server

### 部署

首先请确保项目已经配置[通过 GitHub 部署](https://leancloud.cn/docs/leanengine_guide-node.html#使用_GitHub_托管源码)。

* 在 `web-project 目录`中执行 `gulp build`，构建系统会自动打包，自动压缩合并代码，发布到 public 目录中
* 将最新代码，连同 public 目录中的代码，全部提交到对应的 GitHub 仓库中
* 在根目录执行 `avoscloud -g deploy` 可以部署到 LeanEngine 的测试环境中，通过配置的测试地址访问

### 其他开发说明

* 当前项目中，服务端与 Web 端本地调试的域并不相同，所以前端与服务端基础代码中已经基于 HTML5 CORS 协议做了跨域支持，具体参考项目中代码
* web-project 目录完全可以独立，是一套完整的 Web 前端开发结构，本身也支持跨域方案，所以也可以 Web 与 Server 分工开发

## 目录结构

```
.
├── public          // LeanEngine Web 前端发布目录，前端（HTML\CSS\JavaScript）构建后放在此目录中
├── server-modules  // 服务器端代码模块目录
│    ├── api-router.js     // API 接口路由配置
│    ├── tool.js           // 工具方法
│    └── hello.js          // 示例代码
├── web-project     // Web 前端项目目录
│    └── src            // 源码目录
└── app.js          // LeanEngine 服务端代码主入口
```

## 国际化方案

Web 端版本直接支持国际化方案，具体配置都在 `web-project/src/app/i18n` 目录中，项目中界面内有基本示例。



