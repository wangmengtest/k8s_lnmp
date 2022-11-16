/*
 Navicat Premium Data Transfer

 Source Server         : vhall_component
 Source Server Type    : MySQL
 Source Server Version : 50616
 Source Host           : ******
 Source Schema         : vhall_component

 Target Server Type    : MySQL
 Target Server Version : 50616
 File Encoding         : 65001

 Date: 14/09/2020 13:43:38
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;-- ----------------------------
-- Table structure for access
-- ----------------------------
DROP TABLE IF EXISTS `access`;
CREATE TABLE `access`  (
                           `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                           `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '权限名称',
                           `rule` int(10) NOT NULL COMMENT '权限码',
                           `url` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '路径',
                           `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0:正常 1:禁止',
                           `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '类型 0：显示 1：操作',
                           `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                           `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
                           `deleted_at` datetime(0) NULL DEFAULT NULL,
                           PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 64 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '权限详情表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of access
-- ----------------------------
INSERT INTO `access` VALUES (1, '第三方推流', 10001, '', 0, 0, '2019-10-25 15:30:52', '2019-10-25 15:30:52', NULL);
INSERT INTO `access` VALUES (2, '打点录制', 10002, '', 0, 0, '2019-10-25 15:30:52', '2019-10-25 15:30:52', NULL);
INSERT INTO `access` VALUES (3, '分享 -- 重复了', 10003, '', 0, 0, '2019-10-25 15:30:52', '2019-10-25 15:30:52', NULL);
INSERT INTO `access` VALUES (4, '播放器控制条', 10004, '', 0, 0, '2019-10-25 15:30:52', '2019-10-25 15:30:52', NULL);
INSERT INTO `access` VALUES (5, '清晰度选择', 10005, '', 0, 0, '2019-10-25 15:30:52', '2019-10-25 15:30:52', NULL);
INSERT INTO `access` VALUES (6, '倍速播放', 10006, '', 0, 0, '2019-10-25 15:30:52', '2019-10-25 15:30:52', NULL);
INSERT INTO `access` VALUES (7, '弹幕', 10007, '', 0, 0, '2019-10-25 15:30:52', '2019-10-25 15:30:52', NULL);
INSERT INTO `access` VALUES (8, '开启旁路推流', 10008, '', 0, 0, '2019-10-25 15:30:52', '2019-10-25 15:30:52', NULL);
INSERT INTO `access` VALUES (9, '设置旁路布局', 10009, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (10, '设置大屏显示', 10010, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (11, '设置主讲人', 10011, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (12, '操作上麦申请（同意、拒绝）', 10012, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (13, '邀请上麦', 10013, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (14, '申请上麦', 10014, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (15, '开关自己音视频', 10015, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (16, '开关他人音视频', 10016, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (17, '下麦自己', 10017, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (18, '下麦他人', 10018, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (19, '全屏', 10019, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (20, '举手开关', 10020, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (21, '举手', 10021, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (22, '举手列表', 10022, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (23, '文档上传', 11001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (24, '文档开关', 11002, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (25, '文档演示', 11003, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (26, '文档翻页', 11004, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (27, '文档画笔', 11005, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (28, '白板', 11006, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (29, '成员列表', 12001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (30, '踢出/恢复', 12002, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (31, '禁言/恢复', 12003, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (32, '禁言踢出列表', 12004, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (33, '聊天审核', 12005, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (34, '全员禁言', 12006, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (35, '回放', 13001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (36, '红包', 14001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (37, '抽奖', 15001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (38, '问卷', 16001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (39, '问答', 17001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (40, '签到', 18001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (41, '点赞', 19001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (42, '礼物', 20001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (43, '分享', 21001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (44, '打赏', 22001, '', 0, 0, '2019-10-25 15:30:53', '2019-10-25 15:30:53', NULL);
INSERT INTO `access` VALUES (45, '问卷-发布问卷(主播)', 0, '/v1/question/publish', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (46, '发送公告', 0, '/v1/chat/send-notice', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (47, '全体禁言', 0, '/v1/inav/set-all-banned', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (48, '设置文档白板权限', 0, '/v1/inav/set-doc-permission', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (49, '设置观看端布局/清晰度', 0, '/v1/inav/set-stream', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (50, '设置观众申请上麦许可（举手）', 0, '/v1/inav/set-handsup', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (51, '同意用户上麦申请 ', 0, '/v1/inav/agree-apply', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (52, '拒绝用户上麦申请 ', 0, '/v1/inav/reject-apply', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (53, '邀请用户上麦 ', 0, '/v1/inav/invite', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (54, '用户下麦 ', 0, '/v1/inav/nospeak', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (55, '设置设备开关状态 ', 0, '/v1/inav/set-device-status', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (56, '设置用户演示状态（主画面） ', 0, '/v1/inav/set-main-screen', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (57, '桌面演示开关 ', 0, '/v1/inav/set-desktop', 1, 1, '2019-10-28 11:59:42', '2019-10-28 11:59:42', NULL);
INSERT INTO `access` VALUES (58, '公告', 23001, '', 0, 0, '2020-07-01 15:38:26', '2020-07-01 15:38:26', NULL);
INSERT INTO `access` VALUES (59, '公告发送', 23002, '', 0, 0, '2020-03-22 00:00:00', '2020-03-22 00:00:00', NULL);
INSERT INTO `access` VALUES (60, '观众端问答', 17002, '', 0, 0, '2020-02-17 19:37:26', '2020-02-17 19:37:20', NULL);
INSERT INTO `access` VALUES (62, '@', 23004, '', 0, 0, '2020-04-26 11:18:22', '2020-04-26 11:18:26', NULL);
INSERT INTO `access` VALUES (63, '回复', 23003, '', 0, 0, '2020-04-26 11:17:29', '2020-04-26 11:17:34', NULL);

-- ----------------------------
-- Table structure for access_op_log
-- ----------------------------
DROP TABLE IF EXISTS `access_op_log`;
CREATE TABLE `access_op_log`  (
                                  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                  `operator` int(11) NOT NULL COMMENT '操作者',
                                  `content` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作内容',
                                  `type` tinyint(1) NOT NULL COMMENT '操作行为类型:   1:组 2:角色 3:权限 ',
                                  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '操作时间',
                                  `updated_at` datetime(0) NULL DEFAULT NULL,
                                  `deleted_at` datetime(0) NULL DEFAULT NULL,
                                  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '权限操作日志表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for group_access
-- ----------------------------
DROP TABLE IF EXISTS `group_access`;
CREATE TABLE `group_access`  (
                                 `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                 `group_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '组id',
                                 `access_id` int(11) NOT NULL,
                                 `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0：开启 1：关闭',
                                 `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                 `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
                                 `deleted_at` datetime(0) NULL DEFAULT NULL,
                                 PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户组权限关系表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for group
-- ----------------------------
DROP TABLE IF EXISTS `group`;
CREATE TABLE `group`  (
                          `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                          `group_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '组名称',
                          `app_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '应用id',
                          `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0：正常 1：删除',
                          `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                          `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
                          `deleted_at` datetime(0) NULL DEFAULT NULL,
                          PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户组表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for role_access
-- ----------------------------
DROP TABLE IF EXISTS `role_access`;
CREATE TABLE `role_access`  (
                                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                `role_id` int(11) NOT NULL DEFAULT 0 COMMENT '角色id',
                                `access_id` int(11) NOT NULL DEFAULT 0 COMMENT '权限id',
                                `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0：开启 1：关闭',
                                `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
                                `deleted_at` datetime(0) NULL DEFAULT NULL,
                                PRIMARY KEY (`id`) USING BTREE,
                                INDEX `idx_role_id`(`role_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '角色权限表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for user_group
-- ----------------------------
DROP TABLE IF EXISTS `user_group`;
CREATE TABLE `user_group`  (
                               `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                               `account_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户id',
                               `group_id` int(11) NOT NULL DEFAULT 0 COMMENT '组id',
                               `app_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '应用id',
                               `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:正常 1:删除',
                               `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                               `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
                               `deleted_at` datetime(0) NULL DEFAULT NULL,
                               PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户和组关系表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for user_role
-- ----------------------------
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role`  (
                              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                              `account_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户id',
                              `role_id` int(11) NOT NULL DEFAULT 0 COMMENT '角色ID',
                              `app_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '应用id',
                              `status` tinyint(1) NULL DEFAULT 0 COMMENT '0：正常 1：删除',
                              `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                              `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
                              `deleted_at` datetime(0) NULL DEFAULT NULL,
                              PRIMARY KEY (`id`) USING BTREE,
                              INDEX `idx_uid`(`account_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户角色关系表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for accounts
-- ----------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts`  (
                             `account_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                             `phone` bigint(11) NOT NULL COMMENT '手机号码',
                             `username` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
                             `nickname` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
                             `sex` tinyint(1) NOT NULL DEFAULT 1 COMMENT '性别>0|女,1|男',
                             `token` char(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '登录标识',
                             `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态>-1|封停,0|正常',
                             `profile_photo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '头像',
                             `account_type` tinyint(1) NOT NULL DEFAULT 2 COMMENT '用户类型：1--发起端；2--观看端；3--游客',
                             `third_user_id` varchar(64) DEFAULT NULL COMMENT '第三方用户ID',
                             `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                             `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                             `deleted_at` datetime(0) DEFAULT NULL,
                             PRIMARY KEY (`account_id`) USING BTREE,
                             UNIQUE KEY `uniq_third_user_id` (`third_user_id`),
                             KEY `phone` (`phone`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for accounts
-- ----------------------------
DROP TABLE IF EXISTS `anchor_extends`;
CREATE TABLE `anchor_extends`  (
                                   `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                   `account_id` int(10) UNSIGNED NOT NULL,
                                   `connect_num` int(11) NOT NULL default '0' COMMENT '并发限制数量',
                                   `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                   `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `deleted_at` datetime(0) DEFAULT NULL,
                                   PRIMARY KEY (`id`) USING BTREE,
                                   UNIQUE KEY `uniq_account_id` (`account_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '主播用户扩展表' ROW_FORMAT = Compact;
-- ----------------------------
-- Table structure for actions
-- ----------------------------
DROP TABLE IF EXISTS `actions`;
CREATE TABLE `actions`  (
                            `action_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
                            `controller_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '控制器名称',
                            `action_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作名称',
                            `pid` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父id',
                            `desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
                            `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
                            `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                            `deleted_at` datetime(0) NULL DEFAULT NULL,
                            PRIMARY KEY (`action_id`) USING BTREE,
                            UNIQUE INDEX `controller_action_unique`(`controller_name`, `action_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 82 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '操作表' ROW_FORMAT = Compact;
-- ----------------------------
-- Records of actions
-- ----------------------------
INSERT INTO `actions` VALUES (1, 'RecordController', '', 0, '回放控制器', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (2, 'RecordController', 'getAction', 1, '回放-信息', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (3, 'RecordController', 'listAction', 1, '回放-列表', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (4, 'RecordController', 'deleteAction', 1, '回放-删除', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (5, 'DocumentController', '', 0, '文档控制器', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (6, 'DocumentController', 'getAction', 5, '文档-信息', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (7, 'DocumentController', 'listAction', 5, '文档-列表', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (8, 'DocumentController', 'exportListAction', 5, '文档-导出列表', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (9, 'DocumentController', 'deleteAction', 5, '文档-删除', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (10, 'AuthController', '', 0, '验证控制器', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (11, 'AuthController', 'loginAction', 10, '验证-登录', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (12, 'AuthController', 'logoutAction', 10, '验证-退出', '2019-03-20 18:14:31', '2019-03-20 18:14:31', NULL);
INSERT INTO `actions` VALUES (13, 'ActionController', '', 0, '操作控制器', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (14, 'ActionController', 'listAction', 13, '操作-列表', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (15, 'ActionController', 'addAction', 13, '操作-添加', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (16, 'ActionController', 'deleteAction', 13, '操作-删除', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (17, 'ActionController', 'editAction', 13, '操作-编辑', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (18, 'ActionController', 'generateAction', 13, '操作-列表生成器', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (19, 'QuestionController', '', 0, '问卷控制器', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (20, 'QuestionController', 'getAction', 19, '问卷-信息', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (21, 'QuestionController', 'listAction', 19, '问卷-列表', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (22, 'QuestionController', 'exportListAction', 19, '问卷-导出', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (23, 'QuestionController', 'deleteAction', 19, '问卷-删除', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (24, 'AccountController', '', 0, '用户控制器', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (25, 'AccountController', 'getAction', 24, '用户-信息', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (26, 'AccountController', 'listAction', 24, '用户-列表', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (27, 'AccountController', 'exportListAction', 24, '用户-导出', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (28, 'AccountController', 'addAction', 24, '用户-新增', '2019-03-20 18:14:32', '2019-03-20 18:14:32', NULL);
INSERT INTO `actions` VALUES (29, 'AccountController', 'editAction', 24, '用户-编辑', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (30, 'AccountController', 'editStatusAction', 24, '用户-修改状态', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (31, 'RoleController', '', 0, '角色控制器', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (32, 'RoleController', 'getAction', 31, '角色-信息', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (33, 'RoleController', 'listAction', 31, '角色-列表', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (34, 'RoleController', 'addAction', 31, '角色-添加', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (35, 'RoleController', 'deleteAction', 31, '角色-删除', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (36, 'RoleController', 'editAction', 31, '角色-编辑', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (37, 'RoleController', 'editStatusAction', 31, '角色-状态', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (38, 'RoleController', 'editMenuesAction', 31, '角色-编辑菜单权限', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (39, 'RoleController', 'editActionsAction', 31, '角色-编辑操作权限', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (40, 'MenuController', '', 0, '菜单控制器', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (41, 'MenuController', 'listAction', 40, '菜单-列表', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (42, 'MenuController', 'addAction', 40, '菜单-添加', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (43, 'MenuController', 'deleteAction', 40, '菜单-删除', '2019-03-20 18:14:33', '2019-03-20 18:14:33', NULL);
INSERT INTO `actions` VALUES (44, 'MenuController', 'editAction', 40, '菜单-编辑', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (45, 'PaasController', '', 0, 'PAAS服务控制器', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (46, 'PaasController', 'getAccessTokenAction', 45, '获取AccessToken', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (47, 'AdminController', '', 0, '管理员控制器', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (48, 'AdminController', 'getAction', 47, '管理员-管理员信息', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (49, 'AdminController', 'listAction', 47, '管理员-列表', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (50, 'AdminController', 'exportListAction', 47, '管理员-导出', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (51, 'AdminController', 'deleteAction', 47, '管理员-删除', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (52, 'AdminController', 'addAction', 47, '管理员-添加', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (53, 'AdminController', 'editAction', 47, '管理员-编辑', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (54, 'AdminController', 'editPasswordAction', 47, '管理员-修改密码', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (55, 'AdminController', 'editStatusAction', 47, '管理员-修改状态', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (56, 'StatController', '', 0, '统计控制器', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (57, 'StatController', 'indexAction', 56, '统计-概览', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (58, 'StatController', 'liveAction', 56, '统计-房间', '2019-03-20 18:14:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (59, 'StatController', 'exportPvAction', 56, '统计-导出房间累计观看次数记录', '2020-12-04 15:43:34', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (60, 'StatController', 'exportMessageAction', 56, '统计-导出房间聊天记录', '2020-12-04 15:43:43', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (61, 'StatController', 'exportListAction', 56, '统计-导出列表', '2020-12-04 15:43:43', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (62, 'TagController', '', 0, '标签控制器', '2020-10-21 11:33:49', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (63, 'TagController', 'saveAction', 62, '标签-保存', '2020-10-21 11:33:49', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (64, 'TagController', 'listAction', 62, '标签-列表', '2020-10-21 11:33:49', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (65, 'TagController', 'editAction', 62, '标签-编辑', '2020-10-21 11:33:49', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (66, 'TagController', 'deleteAction', 62, '标签-删除', '2020-10-21 11:33:49', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (67, 'RoomController', '', 0, '房间控制器', '2020-10-21 11:33:49', '2019-03-20 18:14:34', NULL);
INSERT INTO `actions` VALUES (68, 'RoomController', 'getAction', 67, '房间-信息', '2020-10-21 11:33:54', '2019-03-20 18:14:35', NULL);
INSERT INTO `actions` VALUES (69, 'RoomController', 'listAction', 67, '房间-列表', '2020-10-21 11:34:00', '2019-03-20 18:14:35', NULL);
INSERT INTO `actions` VALUES (70, 'RoomController', 'exportListAction', 67, '房间-导出', '2020-10-21 11:34:05', '2019-03-20 18:14:35', NULL);
INSERT INTO `actions` VALUES (71, 'RoomController', 'deleteAction', 67, '房间-删除', '2020-10-21 11:34:10', '2019-03-20 18:14:35', NULL);
INSERT INTO `actions` VALUES (72, 'RoomController', 'setDefaultRecordAction', 67, '房间-默认回放', '2020-10-21 11:34:15', '2019-03-20 18:14:35', NULL);
INSERT INTO `actions` VALUES (82, 'ConnectctlController', 'getConnectNumAction', 24, '并发信息', '2021-02-25 22:48:04', '2021-02-25 22:46:35', NULL);
INSERT INTO `actions` VALUES (83, 'ConnectctlController', 'setConnectNumAction', 24, '并发设置', '2021-02-25 22:48:33', '2021-02-25 22:48:09', NULL);

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role`  (
                         `role_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                         `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '角色名称',
                         `code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '角色标识',
                         `desc` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '描述',
                         `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0：正常 1：无效',
                         `app_id` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '应用id',
                         `level` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色级别',
                         `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                         `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                         `deleted_at` datetime(0) NULL DEFAULT NULL,
                         PRIMARY KEY (`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '角色表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES (1, '主持人', 'qwes', '主持人', 1, 'd77b80ba', 1, '2019-10-25 16:06:23', '2019-10-25 16:06:23', NULL);
INSERT INTO `role` VALUES (8, '测试人员修改', 'TCNW8S', '解释一下', 1, '', 0, '2020-06-17 15:07:03', '2020-06-17 15:04:19', NULL);
INSERT INTO `role` VALUES (9, '测试', 'ZOFMG1', '', 1, '', 0, '2020-06-23 19:24:04', '2020-06-23 19:24:04', NULL);
INSERT INTO `role` VALUES (11, '管理员', 'DE22FQ', '拥有所有权限', 1, '', 0, '2020-07-17 17:33:44', '2020-07-17 17:33:44', NULL);
INSERT INTO `role` VALUES (15, 'test2', '9BXCEY', '', 1, NULL, 0, '2020-08-11 17:04:59', '2020-08-11 16:54:25', '2020-08-11 17:04:59');
INSERT INTO `role` VALUES (16, 'test3', 'HNCZLZ', '', 1, NULL, 0, '2020-08-11 17:04:59', '2020-08-11 17:03:13', '2020-08-11 17:04:59');


-- ----------------------------
-- Table structure for role_actions
-- ----------------------------
DROP TABLE IF EXISTS `role_actions`;
CREATE TABLE `role_actions`  (
                                 `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
                                 `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
                                 `action_id` int(11) UNSIGNED NOT NULL COMMENT '操作ID',
                                 `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
                                 `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP  COMMENT '创建时间',
                                 `deleted_at` datetime(0) NULL DEFAULT NULL,
                                 PRIMARY KEY (`id`) USING BTREE,
                                 UNIQUE INDEX `role_action_unique`(`role_id`, `action_id`) USING BTREE,
                                 INDEX `fk_role_actions_action`(`action_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2059 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '角色-操作权限表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of role_actions
-- ----------------------------
INSERT INTO `role_actions` VALUES (1, 1, 1, '2020-06-17 14:43:41', '2020-06-17 14:44:00', NULL);
INSERT INTO `role_actions` VALUES (1449, 6, 10, '2020-06-17 14:44:57', '2020-06-17 14:44:57', NULL);
INSERT INTO `role_actions` VALUES (1450, 6, 11, '2020-06-17 14:44:57', '2020-06-17 14:44:57', NULL);
INSERT INTO `role_actions` VALUES (1451, 6, 12, '2020-06-17 14:44:57', '2020-06-17 14:44:57', NULL);
INSERT INTO `role_actions` VALUES (1452, 6, 32, '2020-06-17 14:44:58', '2020-06-17 14:44:58', NULL);
INSERT INTO `role_actions` VALUES (1453, 6, 45, '2020-06-17 14:44:58', '2020-06-17 14:44:58', NULL);
INSERT INTO `role_actions` VALUES (1454, 6, 46, '2020-06-17 14:44:59', '2020-06-17 14:44:59', NULL);
INSERT INTO `role_actions` VALUES (1455, 6, 48, '2020-06-17 14:44:59', '2020-06-17 14:44:59', NULL);
INSERT INTO `role_actions` VALUES (1456, 6, 57, '2020-06-17 14:45:00', '2020-06-17 14:45:00', NULL);
INSERT INTO `role_actions` VALUES (1465, 8, 10, '2020-06-17 15:04:19', '2020-06-17 15:04:19', NULL);
INSERT INTO `role_actions` VALUES (1466, 8, 11, '2020-06-17 15:04:20', '2020-06-17 15:04:20', NULL);
INSERT INTO `role_actions` VALUES (1467, 8, 12, '2020-06-17 15:04:20', '2020-06-17 15:04:20', NULL);
INSERT INTO `role_actions` VALUES (1468, 8, 32, '2020-06-17 15:04:21', '2020-06-17 15:04:21', NULL);
INSERT INTO `role_actions` VALUES (1469, 8, 45, '2020-06-17 15:04:21', '2020-06-17 15:04:21', NULL);
INSERT INTO `role_actions` VALUES (1470, 8, 46, '2020-06-17 15:04:22', '2020-06-17 15:04:22', NULL);
INSERT INTO `role_actions` VALUES (1471, 8, 48, '2020-06-17 15:04:22', '2020-06-17 15:04:22', NULL);
INSERT INTO `role_actions` VALUES (1472, 8, 57, '2020-06-17 15:04:23', '2020-06-17 15:04:23', NULL);
INSERT INTO `role_actions` VALUES (1481, 9, 61, '2020-06-23 19:41:34', '2020-06-23 19:41:34', NULL);
INSERT INTO `role_actions` VALUES (1482, 9, 66, '2020-06-23 19:41:35', '2020-06-23 19:41:35', NULL);
INSERT INTO `role_actions` VALUES (1483, 9, 65, '2020-06-23 19:41:35', '2020-06-23 19:41:35', NULL);
INSERT INTO `role_actions` VALUES (1484, 9, 64, '2020-06-23 19:41:35', '2020-06-23 19:41:35', NULL);
INSERT INTO `role_actions` VALUES (1485, 9, 63, '2020-06-23 19:41:36', '2020-06-23 19:41:36', NULL);
INSERT INTO `role_actions` VALUES (1486, 9, 62, '2020-06-23 19:41:36', '2020-06-23 19:41:36', NULL);
INSERT INTO `role_actions` VALUES (1487, 9, 56, '2020-06-23 19:41:37', '2020-06-23 19:41:37', NULL);
INSERT INTO `role_actions` VALUES (1488, 9, 60, '2020-06-23 19:41:37', '2020-06-23 19:41:37', NULL);
INSERT INTO `role_actions` VALUES (1489, 9, 59, '2020-06-23 19:41:37', '2020-06-23 19:41:37', NULL);
INSERT INTO `role_actions` VALUES (1490, 9, 58, '2020-06-23 19:41:38', '2020-06-23 19:41:38', NULL);
INSERT INTO `role_actions` VALUES (1491, 9, 57, '2020-06-23 19:41:38', '2020-06-23 19:41:38', NULL);
INSERT INTO `role_actions` VALUES (1492, 9, 47, '2020-06-23 19:41:38', '2020-06-23 19:41:38', NULL);
INSERT INTO `role_actions` VALUES (1493, 9, 55, '2020-06-23 19:41:39', '2020-06-23 19:41:39', NULL);
INSERT INTO `role_actions` VALUES (1494, 9, 54, '2020-06-23 19:41:39', '2020-06-23 19:41:39', NULL);
INSERT INTO `role_actions` VALUES (1495, 9, 53, '2020-06-23 19:41:40', '2020-06-23 19:41:40', NULL);
INSERT INTO `role_actions` VALUES (1496, 9, 52, '2020-06-23 19:41:40', '2020-06-23 19:41:40', NULL);
INSERT INTO `role_actions` VALUES (1497, 9, 51, '2020-06-23 19:41:40', '2020-06-23 19:41:40', NULL);
INSERT INTO `role_actions` VALUES (1498, 9, 50, '2020-06-23 19:41:41', '2020-06-23 19:41:41', NULL);
INSERT INTO `role_actions` VALUES (1499, 9, 49, '2020-06-23 19:41:41', '2020-06-23 19:41:41', NULL);
INSERT INTO `role_actions` VALUES (1500, 9, 48, '2020-06-23 19:41:41', '2020-06-23 19:41:41', NULL);
INSERT INTO `role_actions` VALUES (1501, 9, 45, '2020-06-23 19:41:42', '2020-06-23 19:41:42', NULL);
INSERT INTO `role_actions` VALUES (1502, 9, 46, '2020-06-23 19:41:42', '2020-06-23 19:41:42', NULL);
INSERT INTO `role_actions` VALUES (1503, 9, 40, '2020-06-23 19:41:42', '2020-06-23 19:41:42', NULL);
INSERT INTO `role_actions` VALUES (1504, 9, 44, '2020-06-23 19:41:43', '2020-06-23 19:41:43', NULL);
INSERT INTO `role_actions` VALUES (1505, 9, 43, '2020-06-23 19:41:43', '2020-06-23 19:41:43', NULL);
INSERT INTO `role_actions` VALUES (1506, 9, 42, '2020-06-23 19:41:44', '2020-06-23 19:41:44', NULL);
INSERT INTO `role_actions` VALUES (1507, 9, 41, '2020-06-23 19:41:44', '2020-06-23 19:41:44', NULL);
INSERT INTO `role_actions` VALUES (1508, 9, 31, '2020-06-23 19:41:44', '2020-06-23 19:41:44', NULL);
INSERT INTO `role_actions` VALUES (1509, 9, 39, '2020-06-23 19:41:45', '2020-06-23 19:41:45', NULL);
INSERT INTO `role_actions` VALUES (1510, 9, 38, '2020-06-23 19:41:45', '2020-06-23 19:41:45', NULL);
INSERT INTO `role_actions` VALUES (1511, 9, 37, '2020-06-23 19:41:45', '2020-06-23 19:41:45', NULL);
INSERT INTO `role_actions` VALUES (1512, 9, 36, '2020-06-23 19:41:46', '2020-06-23 19:41:46', NULL);
INSERT INTO `role_actions` VALUES (1513, 9, 35, '2020-06-23 19:41:46', '2020-06-23 19:41:46', NULL);
INSERT INTO `role_actions` VALUES (1514, 9, 34, '2020-06-23 19:41:47', '2020-06-23 19:41:47', NULL);
INSERT INTO `role_actions` VALUES (1515, 9, 33, '2020-06-23 19:41:47', '2020-06-23 19:41:47', NULL);
INSERT INTO `role_actions` VALUES (1516, 9, 32, '2020-06-23 19:41:47', '2020-06-23 19:41:47', NULL);
INSERT INTO `role_actions` VALUES (1517, 9, 24, '2020-06-23 19:41:48', '2020-06-23 19:41:48', NULL);
INSERT INTO `role_actions` VALUES (1518, 9, 30, '2020-06-23 19:41:48', '2020-06-23 19:41:48', NULL);
INSERT INTO `role_actions` VALUES (1519, 9, 29, '2020-06-23 19:41:48', '2020-06-23 19:41:48', NULL);
INSERT INTO `role_actions` VALUES (1520, 9, 28, '2020-06-23 19:41:49', '2020-06-23 19:41:49', NULL);
INSERT INTO `role_actions` VALUES (1521, 9, 27, '2020-06-23 19:41:49', '2020-06-23 19:41:49', NULL);
INSERT INTO `role_actions` VALUES (1522, 9, 26, '2020-06-23 19:41:49', '2020-06-23 19:41:49', NULL);
INSERT INTO `role_actions` VALUES (1523, 9, 25, '2020-06-23 19:41:50', '2020-06-23 19:41:50', NULL);
INSERT INTO `role_actions` VALUES (1524, 9, 19, '2020-06-23 19:41:50', '2020-06-23 19:41:50', NULL);
INSERT INTO `role_actions` VALUES (1525, 9, 23, '2020-06-23 19:41:51', '2020-06-23 19:41:51', NULL);
INSERT INTO `role_actions` VALUES (1526, 9, 22, '2020-06-23 19:41:51', '2020-06-23 19:41:51', NULL);
INSERT INTO `role_actions` VALUES (1527, 9, 21, '2020-06-23 19:41:51', '2020-06-23 19:41:51', NULL);
INSERT INTO `role_actions` VALUES (1528, 9, 20, '2020-06-23 19:41:52', '2020-06-23 19:41:52', NULL);
INSERT INTO `role_actions` VALUES (1529, 9, 13, '2020-06-23 19:41:52', '2020-06-23 19:41:52', NULL);
INSERT INTO `role_actions` VALUES (1530, 9, 18, '2020-06-23 19:41:52', '2020-06-23 19:41:52', NULL);
INSERT INTO `role_actions` VALUES (1531, 9, 17, '2020-06-23 19:41:53', '2020-06-23 19:41:53', NULL);
INSERT INTO `role_actions` VALUES (1532, 9, 16, '2020-06-23 19:41:53', '2020-06-23 19:41:53', NULL);
INSERT INTO `role_actions` VALUES (1533, 9, 15, '2020-06-23 19:41:54', '2020-06-23 19:41:54', NULL);
INSERT INTO `role_actions` VALUES (1534, 9, 14, '2020-06-23 19:41:54', '2020-06-23 19:41:54', NULL);
INSERT INTO `role_actions` VALUES (1535, 9, 10, '2020-06-23 19:41:54', '2020-06-23 19:41:54', NULL);
INSERT INTO `role_actions` VALUES (1536, 9, 12, '2020-06-23 19:41:55', '2020-06-23 19:41:55', NULL);
INSERT INTO `role_actions` VALUES (1537, 9, 11, '2020-06-23 19:41:55', '2020-06-23 19:41:55', NULL);
INSERT INTO `role_actions` VALUES (1538, 9, 5, '2020-06-23 19:41:55', '2020-06-23 19:41:55', NULL);
INSERT INTO `role_actions` VALUES (1539, 9, 9, '2020-06-23 19:41:56', '2020-06-23 19:41:56', NULL);
INSERT INTO `role_actions` VALUES (1540, 9, 8, '2020-06-23 19:41:56', '2020-06-23 19:41:56', NULL);
INSERT INTO `role_actions` VALUES (1541, 9, 7, '2020-06-23 19:41:56', '2020-06-23 19:41:56', NULL);
INSERT INTO `role_actions` VALUES (1542, 9, 6, '2020-06-23 19:41:57', '2020-06-23 19:41:57', NULL);
INSERT INTO `role_actions` VALUES (1543, 9, 1, '2020-06-23 19:41:57', '2020-06-23 19:41:57', NULL);
INSERT INTO `role_actions` VALUES (1544, 9, 4, '2020-06-23 19:41:58', '2020-06-23 19:41:58', NULL);
INSERT INTO `role_actions` VALUES (1545, 9, 3, '2020-06-23 19:41:58', '2020-06-23 19:41:58', NULL);
INSERT INTO `role_actions` VALUES (1546, 9, 2, '2020-06-23 19:41:58', '2020-06-23 19:41:58', NULL);
INSERT INTO `role_actions` VALUES (1640, 23, 1, '2020-09-30 15:16:24', '2020-09-30 15:16:24', NULL);
INSERT INTO `role_actions` VALUES (1641, 23, 4, '2020-09-30 15:16:24', '2020-09-30 15:16:24', NULL);
INSERT INTO `role_actions` VALUES (1642, 23, 3, '2020-09-30 15:16:24', '2020-09-30 15:16:24', NULL);
INSERT INTO `role_actions` VALUES (1643, 23, 2, '2020-09-30 15:16:25', '2020-09-30 15:16:25', NULL);
INSERT INTO `role_actions` VALUES (1644, 23, 10, '2020-09-30 15:16:25', '2020-09-30 15:16:25', NULL);
INSERT INTO `role_actions` VALUES (1645, 23, 11, '2020-09-30 15:16:25', '2020-09-30 15:16:25', NULL);
INSERT INTO `role_actions` VALUES (1646, 23, 12, '2020-09-30 15:16:26', '2020-09-30 15:16:26', NULL);
INSERT INTO `role_actions` VALUES (1647, 23, 32, '2020-09-30 15:16:26', '2020-09-30 15:16:26', NULL);
INSERT INTO `role_actions` VALUES (1648, 23, 45, '2020-09-30 15:16:27', '2020-09-30 15:16:27', NULL);
INSERT INTO `role_actions` VALUES (1649, 23, 46, '2020-09-30 15:16:27', '2020-09-30 15:16:27', NULL);
INSERT INTO `role_actions` VALUES (1650, 23, 48, '2020-09-30 15:16:27', '2020-09-30 15:16:27', NULL);
INSERT INTO `role_actions` VALUES (1651, 23, 57, '2020-09-30 15:16:28', '2020-09-30 15:16:28', NULL);
INSERT INTO `role_actions` VALUES (1916, 11, 66, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1917, 11, 65, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1918, 11, 64, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1919, 11, 63, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1920, 11, 62, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1921, 11, 61, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1922, 11, 56, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1923, 11, 60, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1924, 11, 59, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1925, 11, 58, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1926, 11, 57, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1927, 11, 47, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1928, 11, 55, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1929, 11, 54, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1930, 11, 53, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1931, 11, 52, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1932, 11, 51, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1933, 11, 50, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1934, 11, 49, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1935, 11, 48, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1936, 11, 45, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1937, 11, 46, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1938, 11, 40, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1939, 11, 44, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1940, 11, 43, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1941, 11, 42, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1942, 11, 41, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1943, 11, 31, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1944, 11, 39, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1945, 11, 38, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1946, 11, 37, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1947, 11, 36, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1948, 11, 35, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1949, 11, 34, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1950, 11, 33, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1951, 11, 32, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1952, 11, 24, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1953, 11, 30, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1954, 11, 29, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1955, 11, 28, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1956, 11, 27, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1957, 11, 26, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1958, 11, 25, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1959, 11, 19, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1960, 11, 23, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1961, 11, 22, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1962, 11, 21, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1963, 11, 20, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1964, 11, 13, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1965, 11, 18, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1966, 11, 17, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1967, 11, 16, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1968, 11, 15, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1969, 11, 14, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1970, 11, 10, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1971, 11, 12, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1972, 11, 11, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1973, 11, 5, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1974, 11, 9, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1975, 11, 8, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1976, 11, 7, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1977, 11, 6, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1978, 11, 4, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1979, 11, 3, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1980, 11, 2, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (1981, 11, 1, '2020-09-30 16:59:26', '2020-09-30 16:59:26', NULL);
INSERT INTO `role_actions` VALUES (2012, 18, 56, '2020-09-30 20:11:16', '2020-09-30 20:11:16', NULL);
INSERT INTO `role_actions` VALUES (2013, 18, 60, '2020-09-30 20:11:16', '2020-09-30 20:11:16', NULL);
INSERT INTO `role_actions` VALUES (2014, 18, 59, '2020-09-30 20:11:16', '2020-09-30 20:11:16', NULL);
INSERT INTO `role_actions` VALUES (2015, 18, 58, '2020-09-30 20:11:16', '2020-09-30 20:11:16', NULL);
INSERT INTO `role_actions` VALUES (2016, 18, 57, '2020-09-30 20:11:16', '2020-09-30 20:11:16', NULL);
INSERT INTO `role_actions` VALUES (2017, 18, 47, '2020-09-30 20:11:16', '2020-09-30 20:11:16', NULL);
INSERT INTO `role_actions` VALUES (2018, 18, 55, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2019, 18, 54, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2020, 18, 53, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2021, 18, 52, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2022, 18, 51, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2023, 18, 50, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2024, 18, 49, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2025, 18, 48, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2026, 18, 45, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2027, 18, 46, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2028, 18, 31, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2029, 18, 39, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2030, 18, 38, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2031, 18, 37, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2032, 18, 36, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2033, 18, 35, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2034, 18, 34, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2035, 18, 33, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2036, 18, 32, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2037, 18, 19, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2038, 18, 23, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2039, 18, 22, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2040, 18, 21, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2041, 18, 20, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2042, 18, 10, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2043, 18, 12, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2044, 18, 11, '2020-09-30 20:11:17', '2020-09-30 20:11:17', NULL);
INSERT INTO `role_actions` VALUES (2045, 20, 65, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2046, 20, 57, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2047, 20, 48, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2048, 20, 45, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2049, 20, 46, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2050, 20, 32, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2051, 20, 19, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2052, 20, 23, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2053, 20, 22, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2054, 20, 21, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2055, 20, 20, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2056, 20, 10, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2057, 20, 12, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);
INSERT INTO `role_actions` VALUES (2058, 20, 11, '2020-09-30 22:17:11', '2020-09-30 22:17:11', NULL);

-- ----------------------------
-- Table structure for role_menues
-- ----------------------------
DROP TABLE IF EXISTS `role_menues`;
CREATE TABLE `role_menues`  (
                                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
                                `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
                                `menu_id` int(11) UNSIGNED NOT NULL COMMENT '菜单ID',
                                `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '修改时间',
                                `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                `deleted_at` datetime(0) NULL DEFAULT NULL,
                                PRIMARY KEY (`id`) USING BTREE,
                                UNIQUE INDEX `role_menu_unique`(`role_id`, `menu_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 261 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '角色-菜单权限表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of role_menues
-- ----------------------------
INSERT INTO `role_menues` VALUES (1, 1, 1, '2020-06-17 14:03:31', '2020-06-17 14:03:31', NULL);
INSERT INTO `role_menues` VALUES (6, 1, 2, '2020-06-17 14:44:56', '2020-06-23 15:02:50', NULL);
INSERT INTO `role_menues` VALUES (14, 1, 7, '2020-06-17 15:14:26', '2020-06-23 14:56:39', NULL);
INSERT INTO `role_menues` VALUES (15, 1, 8, '2020-06-17 15:14:26', '2020-06-23 14:56:03', NULL);
INSERT INTO `role_menues` VALUES (16, 1, 3, '2020-06-17 15:14:27', '2020-06-23 15:02:54', NULL);
INSERT INTO `role_menues` VALUES (17, 1, 4, '2020-06-23 15:03:04', '2020-06-23 15:03:04', NULL);
INSERT INTO `role_menues` VALUES (18, 1, 5, '2020-06-23 15:03:09', '2020-06-23 15:03:09', NULL);
INSERT INTO `role_menues` VALUES (19, 1, 6, '2020-06-23 15:03:14', '2020-06-23 15:03:14', NULL);
INSERT INTO `role_menues` VALUES (20, 1, 9, '2020-06-23 15:03:26', '2020-06-23 15:03:26', NULL);
INSERT INTO `role_menues` VALUES (21, 1, 10, '2020-06-23 15:03:30', '2020-06-23 15:03:30', NULL);
INSERT INTO `role_menues` VALUES (23, 1, 11, '2020-06-23 15:03:40', '2020-06-23 15:03:40', NULL);
INSERT INTO `role_menues` VALUES (25, 1, 16, '2020-06-24 11:43:49', '2020-06-24 11:43:49', NULL);
INSERT INTO `role_menues` VALUES (27, 1, 18, '2020-06-24 11:50:16', '2020-06-24 11:50:16', NULL);
INSERT INTO `role_menues` VALUES (54, 9, 7, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (55, 9, 9, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (56, 9, 8, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (57, 9, 6, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (58, 9, 11, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (59, 9, 3, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (60, 9, 5, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (61, 9, 4, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (62, 9, 2, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (63, 9, 16, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (64, 9, 10, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (65, 9, 1, '2020-07-17 17:54:04', '2020-07-17 17:54:04', NULL);
INSERT INTO `role_menues` VALUES (69, 15, 1, '2020-08-11 16:54:26', '2020-08-11 16:54:26', NULL);
INSERT INTO `role_menues` VALUES (70, 16, 1, '2020-08-11 17:03:13', '2020-08-11 17:03:13', NULL);
INSERT INTO `role_menues` VALUES (71, 22, 1, '2020-09-29 17:35:42', '2020-09-29 17:35:42', NULL);
INSERT INTO `role_menues` VALUES (207, 24, 1, '2020-09-30 16:30:03', '2020-09-30 16:30:03', NULL);
INSERT INTO `role_menues` VALUES (208, 23, 18, '2020-09-30 16:31:49', '2020-09-30 16:31:49', NULL);
INSERT INTO `role_menues` VALUES (209, 23, 7, '2020-09-30 16:31:49', '2020-09-30 16:31:49', NULL);
INSERT INTO `role_menues` VALUES (210, 23, 9, '2020-09-30 16:31:49', '2020-09-30 16:31:49', NULL);
INSERT INTO `role_menues` VALUES (211, 23, 8, '2020-09-30 16:31:49', '2020-09-30 16:31:49', NULL);
INSERT INTO `role_menues` VALUES (212, 23, 5, '2020-09-30 16:31:49', '2020-09-30 16:31:49', NULL);
INSERT INTO `role_menues` VALUES (213, 23, 2, '2020-09-30 16:31:49', '2020-09-30 16:31:49', NULL);
INSERT INTO `role_menues` VALUES (214, 23, 16, '2020-09-30 16:31:49', '2020-09-30 16:31:49', NULL);
INSERT INTO `role_menues` VALUES (215, 23, 10, '2020-09-30 16:31:49', '2020-09-30 16:31:49', NULL);
INSERT INTO `role_menues` VALUES (216, 23, 1, '2020-09-30 16:31:49', '2020-09-30 16:31:49', NULL);
INSERT INTO `role_menues` VALUES (220, 18, 6, '2020-09-30 20:10:40', '2020-09-30 20:10:40', NULL);
INSERT INTO `role_menues` VALUES (221, 18, 11, '2020-09-30 20:10:40', '2020-09-30 20:10:40', NULL);
INSERT INTO `role_menues` VALUES (222, 18, 2, '2020-09-30 20:10:40', '2020-09-30 20:10:40', NULL);
INSERT INTO `role_menues` VALUES (223, 18, 16, '2020-09-30 20:10:40', '2020-09-30 20:10:40', NULL);
INSERT INTO `role_menues` VALUES (224, 18, 10, '2020-09-30 20:10:40', '2020-09-30 20:10:40', NULL);
INSERT INTO `role_menues` VALUES (225, 18, 1, '2020-09-30 20:10:40', '2020-09-30 20:10:40', NULL);
INSERT INTO `role_menues` VALUES (226, 20, 6, '2020-09-30 22:17:05', '2020-09-30 22:17:05', NULL);
INSERT INTO `role_menues` VALUES (227, 20, 11, '2020-09-30 22:17:05', '2020-09-30 22:17:05', NULL);
INSERT INTO `role_menues` VALUES (228, 20, 2, '2020-09-30 22:17:05', '2020-09-30 22:17:05', NULL);
INSERT INTO `role_menues` VALUES (229, 20, 16, '2020-09-30 22:17:05', '2020-09-30 22:17:05', NULL);
INSERT INTO `role_menues` VALUES (230, 20, 10, '2020-09-30 22:17:05', '2020-09-30 22:17:05', NULL);
INSERT INTO `role_menues` VALUES (231, 20, 1, '2020-09-30 22:17:05', '2020-09-30 22:17:05', NULL);
INSERT INTO `role_menues` VALUES (232, 25, 1, '2020-09-30 22:17:52', '2020-09-30 22:17:52', NULL);
INSERT INTO `role_menues` VALUES (246, 11, 18, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (247, 11, 7, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (248, 11, 9, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (249, 11, 8, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (250, 11, 6, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (251, 11, 11, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (252, 11, 3, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (253, 11, 5, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (254, 11, 4, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (255, 11, 2, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (256, 11, 16, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (257, 11, 10, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (258, 11, 1, '2020-10-12 16:31:45', '2020-10-12 16:31:45', NULL);
INSERT INTO `role_menues` VALUES (259, 26, 1, '2020-10-12 16:35:18', '2020-10-12 16:35:18', NULL);
INSERT INTO `role_menues` VALUES (260, 27, 1, '2020-10-12 16:36:46', '2020-10-12 16:36:46', NULL);

-- ----------------------------
-- Table structure for admins
-- ----------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins`  (
                           `admin_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
                           `admin_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '登录名',
                           `nick_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
                           `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
                           `mobile` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
                           `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邮箱',
                           `token` char(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '凭证',
                           `token_expire` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '凭证过期时间',
                           `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
                           `last_ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0.0.0' COMMENT '登录ip',
                           `last_time` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后登录时间',
                           `login_num` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '登入统计',
                           `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0>禁用,1>正常',
                           `updated_at` datetime(0) NOT NULL COMMENT '更新时间',
                           `created_at` datetime(0) NOT NULL COMMENT '创建时间',
                           `deleted_at` datetime(0) NULL DEFAULT NULL,
                           PRIMARY KEY (`admin_id`) USING BTREE,
                           UNIQUE INDEX `admin_name_unique`(`admin_name`) USING BTREE,
                           UNIQUE INDEX `nick_name_unique`(`nick_name`) USING BTREE,
                           INDEX `fk_admins_role`(`role_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of admins
-- ----------------------------
INSERT INTO `admins` VALUES (1, 'admin', 'ADMIN', '$2y$10$JkjJKwSOSswI2SXIYTpnsuxu52sPNlPYFuJN5IxtiF4u0rujnOXm2', '', '', '5e52aa0f0f1560a2', '2020-09-10 15:23:38', 1, '1.119.193.36', '2020-07-17 17:53:18', 323, 1, '2020-09-09 15:23:38', '2019-02-04 21:02:09', NULL);

-- ----------------------------
-- Table structure for tag
-- ----------------------------
DROP TABLE IF EXISTS `anchor_manage`;
CREATE TABLE `anchor_manage`
(
    `anchor_id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主播id',
    `account_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联账户id',
    `nickname`   varchar(20)      NOT NULL DEFAULT '' COMMENT '昵称',
    `real_name`  varchar(20)      NOT NULL DEFAULT '' COMMENT '真实姓名',
    `phone`      varchar(20)      NOT NULL DEFAULT '' COMMENT '手机号',
    `avatar`     varchar(200)     NOT NULL DEFAULT '' COMMENT '头像',
    `created_at` datetime(0)      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` datetime(0)      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` datetime(0)               DEFAULT NULL,
    PRIMARY KEY (`anchor_id`) USING BTREE,
    UNIQUE INDEX `phone_unique` (`phone`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '主播表';

-- ----------------------------
-- Table structure for tag
-- ----------------------------
DROP TABLE IF EXISTS `anchor_room_lk`;
CREATE TABLE `anchor_room_lk`
(
    `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `anchor_id`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '主播id',
    `il_id`      int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '房间id',
    `created_at` datetime(0)      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` datetime(0)      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` datetime(0)               DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `il_id_unique` (`il_id`) USING BTREE
) ENGINE = InnoDB
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '房间主播关联表';

-- ----------------------------
-- Table structure for config_info
-- ----------------------------
DROP TABLE IF EXISTS `config_info`;
CREATE TABLE `config_info`  (
                                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                `key` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '配置key',
                                `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '配置',
                                `updated_at` datetime(0) NOT NULL COMMENT '修改时间',
                                `created_at` datetime(0) NOT NULL,
                                `deleted_at` datetime(0) NULL DEFAULT NULL,
                                PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for document_status
-- ----------------------------
DROP TABLE IF EXISTS `document_status`;
CREATE TABLE `document_status`  (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `account_id` int(11) NOT NULL,
                                    `il_id` int(11) NOT NULL COMMENT '互动直播id',
                                    `document_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                    `status` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '状态>0|未开启,1已开启',
                                    `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                    `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    `deleted_at` datetime(0) DEFAULT NULL,
                                    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文档状态操作记录表' ROW_FORMAT = Compact;


-- ----------------------------
-- Table structure for room_documents
-- ----------------------------
DROP TABLE IF EXISTS `room_documents`;
CREATE TABLE `room_documents`  (
                                   `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                   `app_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                                   `document_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文档id',
                                   `hash` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文档hash',
                                   `ext` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件类型',
                                   `page` smallint(6) NOT NULL DEFAULT 0 COMMENT '总页数',
                                   `trans_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '转换进度>1| 待转码, 2|转码中,3|转码成功,4|转码失败',
                                   `status_jpeg` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '静态转换状态: 0待转换 100转换中 200完成 500失败',
                                   `status_swf` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'swf转换状态: 0待转换 100转换中 200完成 500失败',
                                   `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '动态转换状态: 0待转换 100转换中 200完成 500失败',
                                   `file_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文档名称',
                                   `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房间id',
                                   `account_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '用户id',
                                   `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                   `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                   `deleted_at` datetime(0) DEFAULT NULL,
                                   PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '文档关联' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for export
-- ----------------------------
DROP TABLE IF EXISTS `export`;
CREATE TABLE `export` (
                          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                          `il_id` int(11) NOT NULL COMMENT '直播id',
                          `account_id` int(11) NOT NULL COMMENT '操作人id',
                          `source_id` varchar(32) NOT NULL DEFAULT '' COMMENT '三方资源标识id 问卷 考试 投票 问答 等',
                          `file_name` varchar(128) NOT NULL DEFAULT '' COMMENT '文件名',
                          `title` text NOT NULL COMMENT '标题',
                          `export` varchar(32) NOT NULL DEFAULT '' COMMENT '导出模块',
                          `params` text NOT NULL COMMENT '参数',
                          `status` int(11) NOT NULL DEFAULT '1' COMMENT '执行状态',
                          `ext` varchar(32) NOT NULL DEFAULT '' COMMENT '文件类型',
                          `oss_file` varchar(128) default '' not null comment 'oss存储地址',
                          `callback` varchar(64)  default '' not null comment '导出函数,命名规则,服务名:方法名',
                          `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          `deleted_at` datetime(0) DEFAULT NULL,
                          PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for filter_words
-- ----------------------------
DROP TABLE IF EXISTS `filter_words`;
CREATE TABLE `filter_words` (
                                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `keyword` varchar(100) NOT NULL COMMENT '敏感词',
                                `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
                                `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
                                `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
                                `account_id` int(11) NOT NULL COMMENT '商户id',
                                `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1-正常，0-删除',
                                `il_id` int(11) DEFAULT '0' COMMENT '房间id',
                                `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建者id',
                                PRIMARY KEY (`id`),
                                KEY `index_account_id_il_id` (`account_id`,`il_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='敏感词库';

-- ----------------------------
-- Table structure for filter_words_log
-- ----------------------------
DROP TABLE IF EXISTS `filter_words_log`;
CREATE TABLE `filter_words_log` (
                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                    `account_id` int(11) NOT NULL COMMENT '发送用户id',
                                    `il_id` int(11) DEFAULT '0' COMMENT '房间id',
                                    `content` varchar(1000) NOT NULL COMMENT '敏感词',
                                    `live_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型 1直播中 2回放',
                                    `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
                                    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
                                    `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
                                    PRIMARY KEY (`id`),
                                    KEY `index_il_id` (`il_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发送敏感词记录表';



-- 添加菜单
INSERT INTO `menues` (`name`, `url`, `pid`, `sort`, `updated_at`, `created_at`, `deleted_at`)
VALUES
('敏感词管理', 'SENSITIVE', 3, 0, now(), now(), NULL);



-- 添加方法使用
INSERT INTO `actions` ( `controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', "",0, '敏感词控制器', now(), now(), NULL);


INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'listAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-列表', now(), now(), NULL);

INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'createAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-新增', now(), now(), NULL);

INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'updateAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-修改', now(), now(), NULL);

INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'deleteAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-删除', now(), now(), NULL);


INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'templateAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-模版下载', now(), now(), NULL);


INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'importAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-批量导入', now(), now(), NULL);



-- ----------------------------
-- Table structure for menues
-- ----------------------------
DROP TABLE IF EXISTS `menues`;
CREATE TABLE `menues`  (
                           `menu_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
                           `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '菜单名称',
                           `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '菜单链接',
                           `pid` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '父ID',
                           `sort` tinyint(4) NOT NULL DEFAULT 0 COMMENT '排序',
                           `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  COMMENT '修改时间',
                           `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                           `deleted_at` datetime(0) NULL DEFAULT NULL,
                           PRIMARY KEY (`menu_id`) USING BTREE,
                           UNIQUE INDEX `name_url_pid_unique`(`name`, `url`, `pid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 27 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '菜单表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of menues
-- ----------------------------
INSERT INTO `menues` VALUES (1, '统计台', 'STATIC', 0, 0, '2019-03-20 00:00:00', '2019-03-20 00:00:00', NULL);
INSERT INTO `menues` VALUES (2, '房间管理', '', 0, 0, '2019-03-20 00:00:00', '2019-03-20 00:00:00', NULL);
INSERT INTO `menues` VALUES (3, '资料管理', '', 0, 0, '2019-03-20 00:00:00', '2019-03-20 00:00:00', NULL);
INSERT INTO `menues` VALUES (4, '文档列表', 'DOCUMENT', 3, 0, '2019-03-20 00:00:00', '2019-03-20 00:00:00', NULL);
INSERT INTO `menues` VALUES (5, '问卷列表', 'QUESTION', 3, 0, '2019-03-20 00:00:00', '2019-03-20 00:00:00', NULL);
INSERT INTO `menues` VALUES (6, '用户管理', '', 0, 0, '2019-03-20 00:00:00', '2019-03-20 00:00:00', NULL);
INSERT INTO `menues` VALUES (7, '管理员管理', '', 0, 0, '2019-03-20 00:00:00', '2019-03-20 00:00:00', NULL);
INSERT INTO `menues` VALUES (8, '管理员列表', 'ADMIN_LIST', 7, 0, '2019-03-20 00:00:00', '2019-03-20 00:00:00', NULL);
INSERT INTO `menues` VALUES (9, '角色管理', 'ROLE_MANAGE', 7, 0, '2019-03-20 00:00:00', '2019-03-20 00:00:00', NULL);
INSERT INTO `menues` VALUES (10, '房间列表', 'ROOMS', 2, 0, '2019-03-28 11:25:31', '2019-03-28 11:25:34', NULL);
INSERT INTO `menues` VALUES (11, '用户列表', 'USER_MANAGE', 6, 0, '2019-03-28 11:25:54', '2019-03-28 11:25:56', NULL);
INSERT INTO `menues` VALUES (16, '标签管理', 'TAG', 2, 0, '2019-03-28 00:00:00', '2019-03-28 00:00:00', NULL);
INSERT INTO `menues` VALUES (18, '支付管理', 'PAY_MANAGE', 0, 0, '2019-03-28 00:00:00', '2019-03-28 00:00:00', NULL);

-- ----------------------------
-- Table structure for notices
-- ----------------------------
DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices`  (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `app_id` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
                            `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '房间id',
                            `account_id` int(11) NULL DEFAULT 0 COMMENT '用户id',
                            `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '内容',
                            `created_at` datetime(0) NULL DEFAULT NULL,
                            `updated_at` datetime(0) NULL DEFAULT NULL,
                            `deleted_at` datetime(0) NULL DEFAULT NULL,
                            `red_packet_uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                            `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1:红包消息 ，0：公告',
                            PRIMARY KEY (`id`) USING BTREE,
                            INDEX `idx_room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '公告' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for record
-- ----------------------------
DROP TABLE IF EXISTS `record`;
CREATE TABLE `record`  (
                           `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                           `account_id` int(11) NOT NULL COMMENT '用户id',
                           `il_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '房间id',
                           `room_id` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '房间活动id',
                           `source` tinyint(2) NOT NULL DEFAULT 1 COMMENT '来源: 0=回放，1=上传， 2=录制, 10=合成, 11=剪辑',
                           `vod_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '视频文件id',
                           `name` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文件名',
                           `transcode_status` tinyint(11) NOT NULL DEFAULT 0 COMMENT '文件状态:0新增排队中 1转码成功 2转码失败 3转码中 4转码部分成功',
                           `duration` int(11) NOT NULL COMMENT '时长/秒',
                           `storage` int(11) NOT NULL COMMENT '存储量/KB',
                           `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '状态 0：正常   1：删除',
                           `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
                           `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP,
                           `deleted_at` datetime(0) DEFAULT NULL,
                           `created_time` datetime(0) DEFAULT NULL COMMENT '统计时间',
                           PRIMARY KEY (`id`) USING BTREE,
                           INDEX `record_id`(`vod_id`) USING BTREE,
                           INDEX `il_id`(`il_id`) USING BTREE,
                           INDEX `room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '点播表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for record_attends
-- ----------------------------
DROP TABLE IF EXISTS `record_attends`;
CREATE TABLE `record_attends`  (
                                   `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                   `il_id` int(11) UNSIGNED NOT NULL COMMENT '互动直播id',
                                   `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
                                   `record_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '回放id',
                                   `watch_account_id` int(11) NOT NULL COMMENT '观众用户id',
                                   `start_time` datetime(0) DEFAULT NULL COMMENT '进入时间',
                                   `end_time` datetime(0) DEFAULT NULL COMMENT '离开时间',
                                   `duration` int(100) NOT NULL COMMENT '观看时长/秒',
                                   `terminal` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '终端',
                                   `browser` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '浏览器',
                                   `country` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '国家',
                                   `province` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '地域',
                                   `created_time` datetime(0) DEFAULT NULL COMMENT '统计时间',
                                   `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
                                   `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                   `deleted_at` datetime(0) DEFAULT NULL,
                                   PRIMARY KEY (`id`) USING BTREE,
                                   INDEX `idx_ilid`(`il_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '回放-访问统计表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for record_stats
-- ----------------------------
DROP TABLE IF EXISTS `record_stats`;
CREATE TABLE `record_stats`  (
                                 `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                 `il_id` int(10) UNSIGNED NOT NULL COMMENT '互动直播id',
                                 `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
                                 `record_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '回放id',
                                 `flow` int(10) NOT NULL DEFAULT 0 COMMENT '流量/KB',
                                 `pv_num` int(10) NOT NULL DEFAULT 0 COMMENT 'pv量/次',
                                 `uv_num` int(10) NOT NULL DEFAULT 0 COMMENT 'uv量/人',
                                 `duration` int(10) NOT NULL DEFAULT 0 COMMENT '观看时长/秒',
                                 `created_time` datetime(0) DEFAULT NULL COMMENT '统计时间',
                                 `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
                                 `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                 `deleted_at` datetime(0) DEFAULT NULL,
                                 PRIMARY KEY (`id`) USING BTREE,
                                 INDEX `idx_ilid`(`il_id`) USING BTREE,
                                 INDEX `idx_record_id`(`record_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '回放-用量统计表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for dibbling
-- ----------------------------
DROP TABLE IF EXISTS `dibbling`;
CREATE TABLE `dibbling`  (
                             `id` int(11) NOT NULL AUTO_INCREMENT,
                             `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '\'PAAS直播房间id\'',
                             `vod_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '点播ID',
                             `start_time` datetime(0) DEFAULT NULL COMMENT '开始时间',
                             `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否失效 0 未失效 1 已失效',
                             `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                             `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
                             `deleted_at` datetime(0) DEFAULT NULL COMMENT '删除时间',
                             PRIMARY KEY (`id`) USING BTREE,
                             INDEX `index_room`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '点播转直播表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for inav_stats
-- ----------------------------
DROP TABLE IF EXISTS `inav_stats`;
CREATE TABLE `inav_stats`  (
                               `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                               `il_id` int(11) UNSIGNED NOT NULL COMMENT '互动直播id',
                               `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
                               `flow` int(11) NOT NULL DEFAULT 0 COMMENT '流量/KB',
                               `pv_num` int(11) NOT NULL DEFAULT 0 COMMENT 'pv量/次',
                               `uv_num` int(11) NOT NULL DEFAULT 0 COMMENT 'uv量/人',
                               `duration` int(11) NOT NULL DEFAULT 0 COMMENT '互动时长/秒',
                               `created_time` datetime(0) DEFAULT NULL COMMENT '统计时间',
                               `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                               `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `deleted_at` datetime(0) DEFAULT NULL,
                               PRIMARY KEY (`id`) USING BTREE,
                               INDEX `idx_ilid`(`il_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '互动统计表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for rooms
-- ----------------------------
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms`  (
                          `il_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '房间id',
                          `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'PAAS直播房间id',
                          `subject` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房间标题',
                          `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
                          `inav_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'PAAS互动房间id',
                          `channel_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'PAAS频道ID',
                          `nify_channel` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'PAAS通知频道ID',
                          `record_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '默认回放id',
                          `start_time` datetime(0) DEFAULT NULL COMMENT '预计开始时间',
                          `begin_time_stamp` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间时间戳',
                          `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '直播介绍',
                          `category` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属列表',
                          `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面图片地址',
                          `topics` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签,多个逗号隔开',
                          `layout` tinyint(4) NOT NULL DEFAULT 1 COMMENT '布局>1|为单视频,2|音频+文档,3|文档+视频',
                          `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态>0|待直播/预约,1|直播中,2|直播结束',
                          `is_delete` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除>0|否,1|是',
                          `message_approval` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '聊天审核 1允许 2阻止',
                          `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
                          `app_id` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'paasAppId',
                          `like` int(11) NULL DEFAULT 0 COMMENT '点赞数',
                          `deleted_at` datetime(0) DEFAULT NULL,
                          `live_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '直播类型 1 互动直播 2 纯直播',
                          `warm_type` tinyint(1) NULL DEFAULT 0 COMMENT '暖场类型| 0：图片 1：视频   ',
                          `warm_vod_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '暖场视频id',
                          `teacher_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '讲师名称',
                          `begin_live_time` datetime(0) DEFAULT NULL COMMENT '直播开始时间',
                          `end_live_time` datetime(0) DEFAULT NULL COMMENT '直播结束时间',
                          `is_open_document` tinyint(1) NOT NULL DEFAULT 0 COMMENT '开启文档>0|未开启,1|已开启',
                          `live_mode` tinyint(1) NOT NULL DEFAULT 0 COMMENT '直播模式',
                          `message_total` int(11) NOT NULL DEFAULT 0 COMMENT '聊天总数',
                          `mode` tinyint(1) NOT NULL DEFAULT 1 COMMENT '模式>1|助理模式,0|普通模式',
                          `limit_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:登录 1:报名 2:默认 3:白名单',
                          `extend` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '配置扩展',
                          PRIMARY KEY (`il_id`) USING BTREE,
                          INDEX `idx_room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '房间表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_attends
-- ----------------------------
DROP TABLE IF EXISTS `room_attends`;
CREATE TABLE `room_attends`  (
                                 `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                 `il_id` int(11) NOT NULL,
                                 `account_id` int(11) NOT NULL COMMENT '用户id',
                                 `watch_account_id` int(11) NOT NULL,
                                 `start_time` datetime(0) DEFAULT NULL,
                                 `end_time` datetime(0) DEFAULT NULL,
                                 `duration` int(100) NOT NULL COMMENT '观看时长,单位秒',
                                 `terminal` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '终端',
                                 `browser` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '浏览器',
                                 `country` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '国家',
                                 `province` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '地域',
                                 `type` tinyint(3) NULL DEFAULT 1 COMMENT '数据来源 1-直播房间数据 2-互动房间数据',
                                 `created_time` datetime(0) DEFAULT NULL COMMENT '统计时间',
                                 `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                 `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                 `deleted_at` datetime(0) NOT NULL,
                                 PRIMARY KEY (`id`) USING BTREE,
                                 INDEX `idx_ilid`(`il_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '互动直播-访问统计表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_extends
-- ----------------------------
DROP TABLE IF EXISTS `room_extends`;
CREATE TABLE `room_extends`  (
                                 `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
                                 `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房间id',
                                 `start_time` datetime(0) DEFAULT NULL COMMENT '开播时间',
                                 `end_time` datetime(0) DEFAULT NULL COMMENT '结束时间',
                                 `start_type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '开播类型1 web 2 app 3 sdk 4 推拉流 5 定时 6 admin后台 7第三方8 助手 ',
                                 `end_type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '结束类型1 web 2 app 3 sdk 4 推拉流 5 定时 6 admin后台 7第三方8 助手 ',
                                 `is_delete` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除>0|否,1|是',
                                 `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                 `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                 `deleted_at` datetime(0) DEFAULT NULL,
                                 PRIMARY KEY (`id`) USING BTREE,
                                 INDEX `index_room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '房间扩展信息表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_supply
-- ----------------------------
DROP TABLE IF EXISTS `room_supply`;
CREATE TABLE `room_supply` (
                               `il_id` int(11) unsigned NOT NULL COMMENT '房间ID',
                               `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'paas 房间ID',
                               `account_id` int(11) unsigned NOT NULL COMMENT '用户id',
                               `custom_tag` text COLLATE utf8_unicode_ci COMMENT '用户自定义简介tag',
                               `mode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1,互动助理模式,0普通模式',
                               `assistant_sign` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '助理口令',
                               `interaction_sign` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '互动口令',
                               `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                               `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                               `deleted_at` datetime DEFAULT NULL,
                               PRIMARY KEY (`il_id`),
                               KEY `room_id` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='直播间信息补充表';

-- ----------------------------
-- Table structure for room_joins
-- ----------------------------
DROP TABLE IF EXISTS `room_joins`;
CREATE TABLE `room_joins`  (
                               `join_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
                               `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房间id',
                               `account_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
                               `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '用户账号',
                               `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户昵称',
                               `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户头像',
                               `role_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色信息，1主持人2观众3助理4嘉宾',
                               `is_banned` tinyint(4) NULL DEFAULT 0 COMMENT '是否禁言，1是0否',
                               `is_kicked` tinyint(4) NULL DEFAULT 0 COMMENT '是否踢出，1是0否',
                               `device_type` int(11) NULL DEFAULT 0 COMMENT '设备类型，0未检测 1手机端 2PC 3SDK',
                               `device_status` int(11) NULL DEFAULT 0 COMMENT '设备状态，0未检测1可以上麦2不可以上麦',
                               `is_signed` tinyint(1) NULL DEFAULT 0 COMMENT '是否签到：1 是 0 否',
                               `is_answered_questionnaire` tinyint(1) NULL DEFAULT 0 COMMENT '是否回答过问卷：1 是 0 否',
                               `is_lottery_winner` tinyint(1) NULL DEFAULT 0 COMMENT '是否已经成为抽奖中奖者：1 是 0 否',
                               `is_answered_vote` tinyint(1) NULL DEFAULT 0 COMMENT '是否投过票：1 是 0 否',
                               `is_answered_exam` tinyint(1) NULL DEFAULT 0 COMMENT '是否回答过试卷：1 是 0 否',
                               `status` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '在线状态：0 离线 1 在线',
                               `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                               `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `deleted_at` datetime(0) DEFAULT NULL,
                               PRIMARY KEY (`join_id`) USING BTREE,
                               UNIQUE INDEX `uniq_room_account`(`room_id`, `account_id`) USING BTREE,
                               INDEX `is_signed`(`is_signed`, `room_id`, `created_at`, `deleted_at`) USING BTREE,
                               INDEX `is_answered_questionnaire`(`is_answered_questionnaire`, `room_id`, `created_at`, `deleted_at`) USING BTREE,
                               INDEX `is_lottery_winner`(`is_lottery_winner`, `room_id`, `created_at`, `deleted_at`) USING BTREE,
                               INDEX `is_answered_exam`(`is_answered_exam`, `room_id`, `created_at`, `deleted_at`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '参会成员表' ROW_FORMAT = Compact;


-- ----------------------------
-- Table structure for room_stats
-- ----------------------------
DROP TABLE IF EXISTS `room_stats`;
CREATE TABLE `room_stats`  (
                               `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                               `il_id` int(10) UNSIGNED NOT NULL COMMENT '互动直播id',
                               `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
                               `flow` int(10) NOT NULL DEFAULT 0 COMMENT '流量/KB',
                               `bandwidth` int(10) NOT NULL DEFAULT 0 COMMENT '带宽/kbps',
                               `pv_num` int(10) NOT NULL DEFAULT 0 COMMENT 'pv量/次',
                               `uv_num` int(10) NOT NULL DEFAULT 0 COMMENT 'uv量/人',
                               `duration` int(10) NOT NULL DEFAULT 0 COMMENT '观看时长/秒',
                               `created_time` datetime(0) DEFAULT NULL COMMENT '统计时间',
                               `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                               `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `deleted_at` datetime(0) DEFAULT NULL,
                               PRIMARY KEY (`id`) USING BTREE,
                               INDEX `idx_ilid`(`il_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '直播-用量统计表' ROW_FORMAT = Compact;

-- -------------------------------------
-- Table structure for room_connect_counts
-- -------------------------------------
DROP TABLE IF EXISTS `room_connect_counts`;
CREATE TABLE `room_connect_counts` (
                                       `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                       `il_id` bigint(11) NOT NULL,
                                       `channel` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '聊天频道',
                                       `count` int(11) unsigned NOT NULL DEFAULT '0',
                                       `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                       `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                       `deleted_at` datetime(0) DEFAULT NULL,
                                       `account_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
                                       `create_time` datetime(0) DEFAULT NULL,
                                       PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='连接数表';

-- ----------------------------
-- Table structure for third_stream
-- ----------------------------
DROP TABLE IF EXISTS `third_stream`;
CREATE TABLE `third_stream`  (
                                 `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                 `url` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '推流地址',
                                 `account_id` int(50) NULL DEFAULT 0,
                                 `app_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
                                 `room_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '房间id',
                                 `status` tinyint(1) NULL DEFAULT 0 COMMENT '1：开始,   2：停止  ',
                                 `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                 `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                 `deleted_at` datetime(0) DEFAULT NULL,
                                 PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '第三方推流' ROW_FORMAT = Compact;
SET FOREIGN_KEY_CHECKS = 1;
