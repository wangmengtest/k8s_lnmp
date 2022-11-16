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