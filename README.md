# LeanEngine-Full-Stack
LeanEngine Full Stack Generator

## 简介

该项目为基于 LeanEngine 的 Web 全栈开发的技术解决方案。

### 技术栈

JavaScript 全部代码基于 EcmaScript 6（ES6）。

后端运行环境基于 LeanEngine Nodejs 环境，依赖安装通过 npm，服务框架主要基于 Express 4.x 。

Web 前端自动化方案主要基于 Gulp，框架基于 Angular 1.4.x，UI 框架主要基于 Angular Material，依赖安装通过 bower，样式通过编写 SASS 而非直接写 CSS 文件。

### 开发方式

按照以下方式开始您的开发

* 首先 clone 这个代码库到本地目录中
* 在该项目根目录下执行 `npm install` 安装服务端环境依赖
* 在 web-project 目录中执行 `npm install` 安装 Web 端构建依赖
* 在 web-project 目录中执行 `bower install` 安全 Web 端基础库
* 在根目录执行 `avoscloud` 运行服务器端环境，通过 `http://localhost:3000/api/hello` 可以测试
* 在 web-project 目录中执行 `gulp serve` 运行 web 端环境，通过 `http://localhost:9000` 可以调试

