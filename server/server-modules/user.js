/**
 * Created by lzw on 15/11/19.
 */
'use strict';

const AV = require('leanengine');

const gitHubClientId = '2fdb38b952b9aacf0174';
const gitHubClientSecret = 'e2d00d34749f75b89565b1afcecd275fff81021e';

let pub = {};

pub.register = (req, res) => {
  var username = req.body.username;
  var password = req.body.password;
  var email = req.body.email;
  if (username && password && email) {
    var user = new AV.User();
    user.set('username', username);
    user.set('password', password);
    user.set('email', email);
    user.signUp().then((user) => {
      res.redirect('/');
      // login.renderEmailVerify(res, email);
    }, (error) => {
      //renderInfo(res, util.inspect(error));
    });
  } else {
    //mutil.renderError(res, '不能为空');
  }
};

pub.login = (req, res) => {
  //var username = req.body.username;
  //var password = req.body.password;
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
  var access = req.query.access_token;
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
        console.log('oauth access token request result:' + response);
      }, (response) => {
        console.log('error: ' + response.status);
      });
    }
  } else if (access) {
    var type = req.query.token_type;
    if (!access) {
      console.log('no access token');
    } else {
      AV.Cloud.httpRequest({
        method: 'GET',
        url: 'https://api.github.com/user?access_token=' + access
      }).then((response) => {
        console.log(response);
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
  res.send({url: 'https://github.com/login/oauth/authorize?client_id=' + gitHubClientId + '&redirect_uri=' + redirectUri + '&scope=&state=' + state});
};

module.exports = pub;
