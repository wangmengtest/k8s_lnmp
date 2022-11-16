/*
 Navicat Premium Data Transfer

 Source Server         : component_test
 Source Server Type    : MySQL
 Source Server Version : 50718
 Source Host           : 10.110.110.34:3306
 Source Schema         : component-test-2

 Target Server Type    : MySQL
 Target Server Version : 50718
 File Encoding         : 65001

 Date: 17/06/2021 19:45:11
*/

-- ----------------------------
-- Table structure for photo_sign_img
-- ----------------------------
DROP TABLE IF EXISTS `photo_sign_img`;
CREATE TABLE `photo_sign_img` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到用户uid',
  `sign_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到任务id',
  `img_url` varchar(512) NOT NULL DEFAULT '' COMMENT '图片地址',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_signid` (`user_id`,`sign_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='用户签到照片详情表';

-- ----------------------------
-- Table structure for photo_sign_record
-- ----------------------------
DROP TABLE IF EXISTS `photo_sign_record`;
CREATE TABLE `photo_sign_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sign_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到任务id',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到用户uid',
  `third_user_id` varchar(32) NOT NULL DEFAULT '' COMMENT '三方系统对应用户uid',
  `room_id` varchar(32) NOT NULL DEFAULT '' COMMENT 'PAAS直播房间id',
  `nickname` varchar(120) NOT NULL DEFAULT '' COMMENT '冗余用户昵称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '签到状态:0未签到，1已签到',
  `sign_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '签到时间',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '发起签到客户端来源，标识是app发起的或是pc端发起等',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  `username` varchar(100) NOT NULL DEFAULT '' COMMENT '冗余用户名',
  `phone` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '冗余用户手机号',
  PRIMARY KEY (`id`),
  KEY `idx_signid_userid` (`sign_id`,`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='用户照片签到记录表';

-- ----------------------------
-- Table structure for photo_sign_task
-- ----------------------------
DROP TABLE IF EXISTS `photo_sign_task`;
CREATE TABLE `photo_sign_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建签到任务对应操作者用户uid',
  `room_id` varchar(32) NOT NULL DEFAULT '' COMMENT 'PAAS直播房间id',
  `sign_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已签到人数',
  `no_sign_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未签到人数',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '签到状态:0签到中，1签到结束',
  `begin_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发起签到任务开始时间',
  `end_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发起签到任务结束时间',
  `show_time` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '发起签到任务倒计时显示秒数',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '发起签到客户端来源，标识是app发起的或是pc端发起等',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_roomid` (`user_id`,`room_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='照片签到任务表';

