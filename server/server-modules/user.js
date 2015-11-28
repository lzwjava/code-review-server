/**
 * Created by lzw on 15/11/19.
 */
'use strict';

const AV = require('leanengine');
const util = require('util');

const gitHubClientId = '2fdb38b952b9aacf0174';
const gitHubClientSecret = 'e2d00d34749f75b89565b1afcecd275fff81021e';

let pub = {};

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
    res.status(400).send(inspectError(error));
  };
};

pub.requestSmsCode = (req, res) => {
  AV.Cloud.requestSmsCode(req.body.mobilePhoneNumber).then(() => {
    res.sendStatus(200);
  }, failErrorFn(res));
};

pub.register = (req, res) => {
  var info = {
    mobilePhoneNumber: req.body.mobilePhoneNumber,
    smsCode: req.body.smsCode
  };
  var user = new AV.User();
  user.signUpOrlogInWithMobilePhone(info).then((user) => {
    res.sendStatus(201);
  }, failErrorFn(res));
};

pub.login = (req, res) => {
  var username = req.body.username;
  var password = req.body.password;
  //AV.User.logIn(username, password).then((user) => {
  //  res.redirect('/');
  //});
  loginByGitHubAuth(req, res);
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
