/**
 * Created by lzw on 15/11/19.
 */
'use strict';

const AV = require('leanengine');
const util = require('util');
var debug = require('debug')('user');
var fs = require('fs');

const gitHubClientId = '2fdb38b952b9aacf0174';
const gitHubClientSecret = 'e2d00d34749f75b89565b1afcecd275fff81021e';

let pub = {};
let MissTypeOrWrongType = 1;
let MissNickname = 2;

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

pub.requestSmsCode = async (req, res) => {
  try {
    await AV.Cloud.requestSmsCode(req.body.mobilePhoneNumber);
    res.sendStatus(200);
  } catch (error) {
    failErrorFn(res, error);
  }
};

var registerOrLogin = async (info, res) => {
  var user = new AV.User();
  try {
    await user.signUpOrlogInWithMobilePhone(info);
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
    type: parseInt(req.body.type)
  };
  if (info.type == null && info.type != TypeLearner && info.type != TypeReviewer) {
    res.send(400, createError(MissTypeOrWrongType, 'Miss type or type is wrong.'));
  } else if (info.nickname == null || info.nickname.trim().length == 0) {
    res.send(400, createError(MissNickname, 'Miss nickname or nickname is empty.'));
  } else {
    registerOrLogin(info, res);
  }
};

pub.login = (req, res) => {
  var info = {
    mobilePhoneNumber: req.body.mobilePhoneNumber,
    smsCode: req.body.smsCode
  };
  registerOrLogin(info, res);
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

var isDevelopment = () => {
  return !process.env.LC_APP_ENV || process.env.LC_APP_ENV == 'development';
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
