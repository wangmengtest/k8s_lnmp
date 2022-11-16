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
