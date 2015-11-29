/**
 * Created by lzw on 15/11/19.
 */
'use strict';

const AV = require('leanengine');
const util = require('util');
var debug = require('debug')('user');
var fs = require('fs');
const tool = require('./tool');

const gitHubClientId = '2fdb38b952b9aacf0174';
const gitHubClientSecret = 'e2d00d34749f75b89565b1afcecd275fff81021e';

let pub = {};
let MissTypeOrWrongType = 1;
let MissNickname = 2;
let MissPassword = 3;
let MissSmsCodeOrPassword = 4;

let TypeReviewer = 1;
let TypeLearner = 2;

debug('debugging user now');

let inspectError = (error) => {
  if (error == null) {
    error = "Unknown error";
  }
  if (typeof error != 'string')
    error = util.inspect(error);
  return error;
};

var createError = function (code, desc) {
  return {"error": desc, "code": code};
};

let failErrorFn = (res) => {
  return (error) => {
    failError(res, error);
  };
};

let failError = (res, error) => {
  res.status(400).send(inspectError(error));
};

let failReason = (res, code, desc) => {
  failError(res, createError(code, desc));
};

pub.requestSmsCode = async (req, res) => {
  try {
    await AV.Cloud.requestSmsCode(req.body.mobilePhoneNumber);
    res.sendStatus(200);
  } catch (error) {
    failErrorFn(res, error);
  }
};

var registerOrLogin = async (res, info) => {
  var user = new AV.User();
  try {
    await user.signUpOrlogInWithMobilePhone(info);
    setCookieToken(res, user);
    res.send(user);
  } catch (error) {
    failError(res, error);
  }
};

pub.register = (req, res) => {
  var info = {
    mobilePhoneNumber: req.body.mobilePhoneNumber,
    smsCode: req.body.smsCode,
    nickname: req.body.nickname,
    password: req.body.password,
    type: parseInt(req.body.type)
  };
  if (info.type == null && info.type != TypeLearner && info.type != TypeReviewer) {
    failReason(res, MissTypeOrWrongType, 'Miss type or type is wrong.');
  } else if (info.nickname == null || info.nickname.trim().length == 0) {
    failReason(res, MissNickname, 'Miss nickname or nickname is empty.');
  } else if (info.password == null || info.password.trim().length == 0) {
    failReason(res, MissPassword, 'Miss password or password is empty');
  } else {
    registerOrLogin(res, info);
  }
};

pub.login = async (req, res) => {
  var info = {
    mobilePhoneNumber: req.body.mobilePhoneNumber,
    smsCode: req.body.smsCode,
    password: req.body.password
  };
  if (!info.smsCode && !info.password) {
    failReason(res, MissSmsCodeOrPassword, 'Miss sms code or password to login');
  } else if (info.smsCode) {
    registerOrLogin(res, info);
  } else {
    try {
      let user = await AV.User.logIn(info.mobilePhoneNumber, info.password);
      setCookieToken(res, user);
      res.send(user);
    } catch (error) {
      failError(res, error);
    }
  }
};

var saveFile = async (file) => {
  if (file == null || file.name.length == 0) {
    return AV.Promise.as();
  } else {
    try {
      let data = await fs.readFile(file.path);
      debug('get data succeed');
      var theFile = new AV.File(file.name, data);
      await theFile.save();
      debug('save file succeed');
      return AV.Promise.as(theFile);
    } catch (err) {
      return AV.Promise.error(err);
    }
  }
};

let getUserBySessionToken = (sessionToken) => {
  return AV.User.become(sessionToken);
};

pub.updateInfo = async (req, res) => {
  debug('updating info now');
  let nickname = req.body.nickname;
  let avatar = req.files.avatar;
  debug('avatar is ' + avatar);
  let avatarFile = await saveFile(avatar);
  var updateInfo = {};
  if (nickname && nickname.trim().length > 0) {
    updateInfo.nickname = nickname;
  }
  if (avatarFile) {
    updateInfo.avatar = avatarFile;
  }
  let sessionToken = req.header('X-CR-Session');
  var user = await getUserBySessionToken(sessionToken);
  try {
    await user.save(updateInfo);
  } catch (error) {
    failError(res, error);
  }
};

pub.fetchUser = async (req) => {
  try {
    if (req.user) {
      await req.user.fetch();
    }
  } catch (error) {
    debug('fetch error ' + error);
  }
};

pub.currentUser = async (req, res) => {
  await pub.fetchUser(req);
  if (req.user) {
    res.send(req.user);
  } else {
    res.redirect('/');
  }
};

let CookieSessionKey = 'codereviewsession';

let setCookieToken = (res, user) => {
  let session = {uid: user.id, sessionToken: user._sessionToken};
  //res.clearCookie(CookieSessionKey);
  let secure = !tool.isDevelopment();
  debug('set cookie %j', session);
  res.cookie(CookieSessionKey, session, { secure: secure, httpOnly: true, signed: true, maxAge: 1000 * 60 * 60 * 24 * 60}); // 2 months
};

pub.tokenParser = () => {
  return (req, res, next) => {
    debug('signed cookies %j', req.signedCookies);
    var session = req.signedCookies[CookieSessionKey];
    debug('signed cookies is %s', session);
    if (session != null && session.uid != null && session.sessionToken != null) {
      req.user = new AV.User();
      req.user.id = session.uid;
      req.user._sessionToken = session.sessionToken;
      debug('set req.user');
      next();
    } else {
      next();
    }
  };
};

function randomString() {
  var text = '';
  var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  for (var i = 0; i < 5; i++)
    text += possible.charAt(Math.floor(Math.random() * possible.length));
  return text;
}

pub.gitHubCallback = (req, res) => {
  var code = req.query.code;
  var token = req.query.access_token;
  if (code) {
    if (!code) {
      console.log('no oauth code');
    } else {
      AV.Cloud.httpRequest({
        method: 'POST',
        url: 'https://github.com/login/oauth/access_token',
        body: {
          client_id: gitHubClientId,
          client_secret: gitHubClientSecret,
          code: code
        }
      }).then((response) => {
        console.log('oauth access token request succeed');
      }, (response) => {
        console.log('error: ' + response.status);
      });
    }
  } else if (token) {
    var type = req.query.token_type;
    console.log('get access token' + token);
    if (!token) {
      console.log('no access token');
    } else {
      AV.Cloud.httpRequest({
        method: 'GET',
        url: 'https://api.github.com/user?access_token=' + token
      }).then((response) => {
        console.log('user result:' + response);
      });
    }
  } else {
    console.log('no code or access token');
  }
  res.send();
};

var domain = () => {
  return 'https://codereview.avosapps.com';
};

pub.loginByGitHubAuth = (req, res) => {
  var state = randomString();
  var redirectUri = encodeURI(domain() + '/api/login/github/callback');
  res.redirect('https://github.com/login/oauth/authorize?client_id=' + gitHubClientId + '&redirect_uri=' + redirectUri + '&scope=&state=' + state)
};

module.exports = pub;
