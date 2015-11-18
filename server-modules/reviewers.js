/**
 * Created by lzw on 15/11/18.
 */
'use strict';

const AV = require('leanengine');
const tool = require('./tool');
const _ = require('underscore');

const Reviwer = AV.Object.extend("Reviewer");

let pub = {};

pub.reviewers = (req, res) => {
  tool.l('reviewers');
  let query = new AV.Query(Reviwer);
  query.find().then(function(reviewers) {
    res.send(reviewers);
  }, function (error) {

  });
};

module.exports = pub;
