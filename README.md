# LeanEngine-Full-Stack

该项目为基于 [LeanCloud](http://leancloud.cn) 提供的 Nodejs 服务 [LeanEngine](https://leancloud.cn/docs/leanengine_guide-node.html) 的 Web 全栈开发的技术解决方案。

## 主要特点

LeanEngine-Full-Stack 是基于 LeanEngine Nodejs 服务的 Web 全栈开发技术解决方案。整合当前 Web 技术中通用的技术方案，与 LeanEngine 紧密结合，将基础架构、自动化构建、国际化方案等底层技术问题的解决方案都已经组织在一起。用户可以通过最简单的方式，直接开始的全栈开发，不用再次纠结那些底层技术选型。

## 技术栈

* 语言层面，整套方案 JavaScript 代码全部使用 ECMAScript 6
* Server 端运行环境基于 LeanEngine Nodejs 环境，依赖安装通过 npm，服务框架主要基于 Express 4.x
* Web 前端自动化方案主要基于 Gulp，框架基于 Angular 1.4.x，UI 框架主要基于 Angular Material，构建依赖基于 npm，Web 前端依赖通过 bower 安装，样式通过编写 SASS 而非直接写 CSS 文件

## 技术选型

整个脚手架 Server 端完全基于 LeanEngine，底层已经将 API 路由的基础结构做好，并且将一些常规处理也整合在内，已选型的技术方案主要包括：

* 服务端基本代码结构、组织结构
* 基础的路由分层，默认在 /api/ 路由下
* 对 API 的 HTML5 CORS 跨域协议的设置
* 对访问域白名单控制，集成的可配置文件
* 常规错误处理等

Web 前端从整体技术栈选择上，可以看出这是一个稳健并且有一定前瞻性的技术方案。基于已经非常成熟的 Angular 架构体系，UI 设计层面基于 Google 积累多年而发布的设计语言 Material Design，所以前端 UI 框架基于 Angular Material 框架。Angular 1.4 版本在性能上提升了很多，完全面向现代浏览器，可以直接使用 ECMAScript 6 来开发。内部已经写好并整合的方案主要包括：

* 代码基本组织结构，趋向于 HTML5 Web Compoment 的组织方式
* 底层配置，包括 HTML5 CORS 协议的底层支持、域名白名单等配置
* 纯前端路由方案，基于 HTML5 History API 和 ui-router
* 自动构建系统，基础的代码压缩、合并、ECMAScript 和 SASS 编译等过程，也会将构建后的生成代码拷贝到 public 目录中，供发布使用
* SASS 的基本结构，以及一些 Mixin 和基础单元的处理方式
* 纯前端的国际化方案，可以实时切换语言资源

## 目录结构

```
.
├── public          // LeanEngine Web 前端发布目录，前端（HTML\CSS\JavaScript）构建后放在此目录中
├── server-modules  // 服务器端代码模块目录
│    ├── app.js            // LeanEngine 服务端代码主入口
│    ├── api-router.js     // API 接口路由配置
│    ├── tool.js           // 工具方法
│    └── hello.js          // 示例代码
├── web-project     // Web 前端项目目录
│    ├── gulp           // 自动化构建的逻辑模块
│    ├── dist           // 构建之后的源码目录
│    └── src            // 源码目录
└── server.js       // LeanEngine 的环境配置
```

整套架构 Server 端与 Web 前端是完全分离，在 Server 端编写 REST API，Web 项目则是完全的 Web App，而不是通过模板来耦合。web-project 中是 Web 项目的全部代码，是完全独立的一套体系，也可以提出去，单独项目维护。

## 国际化方案

Web 端版本直接支持国际化方案，具体配置都在 `web-project/src/app/i18n` 目录中，项目中界面内有基本示例。可以实现通过纯 Web 前端实时切换语言，无需服务器切换。

## 使用方式

如果没有使用过，并不了解 LeanCloud 或 LeanEngine，先到[官网](http://leancloud.cn)中了解。

首先确认本机已经安装 [Node.js](http://nodejs.org/) 运行环境和 [LeanCloud 命令行工具](https://leancloud.cn/docs/cloud_code_commandline.html)，之后按照以下方式开始您的开发：

### 依赖安装

* 系统依赖 nodejs 环境为 0.12.x 版本，如果启动遇到错误，请首先确保 nodejs 版本
* 首先 clone 这个代码库到本地目录中
```
$ git clone git@github.com:leancloud/LeanEngine-Full-Stack.git
$ cd LeanEngine-Full-Stack
```

* 再在该项目`根目录`执行
```
$ npm install
```
安装服务端环境依赖

* 在 `web-project 目录`中执行
```
$ npm install
```
安装 Web 端构建依赖

* 在 `web-project 目录`中执行
```
$ bower install
```
安装 Web 端基础库

### 调试

* 在根目录执行
```
$ avoscloud
```
运行服务器端环境，通过 `http://localhost:3000/api/hello` 可以测试

* 在 web-project 目录中执行
```
$ gulp serve
```
运行 web 端环境，通过 `http://localhost:9000` 可以调试

* 开发时需要同时运行这两个任务（开两个 terminal），就可以同时调试 Server 与 Web

### 部署

首先请确保项目已经配置[通过 GitHub 部署](https://leancloud.cn/docs/leanengine_guide-node.html#使用_GitHub_托管源码)。

* 在 `web-project 目录`中执行
```
$ gulp build
```
构建系统会自动打包，自动压缩合并代码，发布到 public 目录中

* 将最新代码，连同 public 目录中的代码，全部提交到对应的 GitHub 仓库中

* 在根目录执行
```
$ avoscloud -g deploy
```
可以部署到 LeanEngine 的测试环境中，通过配置的测试地址访问

* （ 在根目录执行
```
$ avoscloud publish
```
发布整个项目到线上环境）

### 其他说明

* 当前项目中，服务端与 Web 端本地调试的域并不相同，所以前端与服务端基础代码中已经基于 HTML5 CORS 协议做了跨域支持，具体参考项目中代码

* web-project 目录完全可以独立，是一套完整的 Web 前端开发结构，本身也支持跨域方案，所以也可以 Web 与 Server 分工开发

* web-project 中要增加本地构建，请使用
```
$ npm install xxx --save-dev
```
安装到 devDependencies 里面（package.json）

