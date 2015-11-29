/**
 * 每位工程师都有保持代码优雅的义务
 * Each engineer has a duty to keep the code elegant
 *
 * @author wangxiao
 */

// 所有 API 的路由

'use strict';

const router = require('express').Router();

// 添加一个模块
const hello = require('./hello');
const reviewers = require('./reviewers');
const user = require('./user');

// 一个 API 路由下的 hello 接口，访问 /api/hello
router.get('/hello', hello.hello);
router.get('/reviewers', reviewers.reviewers);
router.post('/register', user.register);
router.post('/login', user.login);
router.post('/requestSmsCode', user.requestSmsCode);
router.post('/user', user.updateInfo);
router.get('/user', user.currentUser);

router.get('/login/github/callback', user.gitHubCallback);
router.get('/login/github', user.loginByGitHubAuth);

module.exports = router;
