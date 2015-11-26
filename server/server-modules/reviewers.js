/**
 * Created by lzw on 15/11/18.
 */
'use strict';

const AV = require('leanengine');
const tool = require('./tool');
const _ = require('underscore');

const Reviwer = AV.Object.extend("Reviewer");

let pub = {};

pub.reviewers = async (req, res) => {
  tool.l('reviewers');
  const query = new AV.Query(Reviwer);
  const reviewers = await query.find();
  res.send(reviewers);
};

module.exports = pub;
