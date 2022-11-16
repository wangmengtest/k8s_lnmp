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
