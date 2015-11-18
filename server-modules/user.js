/**
 * Created by lzw on 15/11/19.
 */
'use strict';

const AV = require('leanengine');

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
  var username = req.body.username;
  var password = req.body.password;
  AV.User.logIn(username, password).then((user) => {
    res.redirect('/');
  });
};

module.exports = pub;