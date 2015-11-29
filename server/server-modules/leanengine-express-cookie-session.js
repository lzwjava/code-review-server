/**
 * Created by lzw on 15/11/29.
 */
var AV, Cookie, crc32, debug, signature, url;

AV = require('leanengine');

signature = require('cookie-signature');

crc32 = require('buffer-crc32');

url = require('url');

debug = require('debug')('avos:cookieSession');

Cookie = require('connect').middleware.session.Cookie;

module.exports = function(options) {
  var key, trustProxy;

  options || (options = {});
  key = options.key || 'avos.sess';
  if (options.fetchUser == null) {
    options.fetchUser = false;
  }
  options.cookie || (options.cookie = {});
  options.cookie.httpOnly = true;
  options.cookie.signed = true;
  trustProxy = true;
  return function(req, res, next) {
    var cookie, originalPath, secret, session, sessionToken, uid, user;

    secret = options.secret || req.secret;
    if (secret == null) {
      throw new Error('`secret` option required for avos cookie sessions');
    }
    req._avos_session = {};
    cookie = req._avos_session.cookie = new Cookie(options.cookie);
    originalPath = url.parse(req.originalUrl).pathname;
    if (0 !== originalPath.indexOf(cookie.path)) {
      return next();
    }
    AV.Cloud.__express_req = req;
    AV.Cloud.__express_res = res;
    req._avos_session = req.signedCookies[key] || {};
    req._avos_session.cookie = cookie;
    session = req._avos_session;
    uid = session._uid;
    sessionToken = session._sessionToken;
    res.once('header', function() {
      var proto, tls, val;
      delete AV.Cloud.__express_req;
      delete AV.Cloud.__express_res;
      if (req._avos_session == null) {
        debug('clear session');
        cookie.expire = new Date(0);
        res.setHeader('Set-Cookie', cookie.serialize(key, ''));
        return;
      }
      delete req._avos_session.cookie;
      proto = (req.headers['x-forwarded-proto'] || '').toLowerCase();
      tls = req.connection.encrypted || (trustProxy && 'https' === proto.split(/\s*,\s*/)[0]);
      debug('serializing %j', req._avos_session);
      val = 'j:' + JSON.stringify(req._avos_session);
      val = 's:' + signature.sign(val, secret);
      val = cookie.serialize(key, val);
      debug('set-cookie %j', cookie);
      return res.setHeader('Set-Cookie', val);
    });
    debug('uid %j', uid);
    debug('sessionToken %j', sessionToken);
    if ((uid != null) && (sessionToken != null)) {
      user = new AV.User;
      user.id = uid;
      user._sessionToken = sessionToken;
      AV.User._saveCurrentUser(user, true);
      if (options.fetchUser) {
        return user.fetch({
          success: function() {
            return next();
          },
          error: function(err) {
            return next(err);
          }
        });
      } else {
        return next();
      }
    } else {
      AV.User.logOut(true);
      return next();
    }
  };
};