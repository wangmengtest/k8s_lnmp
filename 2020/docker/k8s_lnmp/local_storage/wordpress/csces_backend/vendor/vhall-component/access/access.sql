-- ----------------------------
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
