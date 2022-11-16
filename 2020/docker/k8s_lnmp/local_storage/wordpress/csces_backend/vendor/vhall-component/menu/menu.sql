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
