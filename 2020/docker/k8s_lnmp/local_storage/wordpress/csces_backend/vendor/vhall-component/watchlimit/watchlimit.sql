-- ----------------------------
-- Table structure for white_accounts
-- ----------------------------
DROP TABLE IF EXISTS `white_accounts`;
CREATE TABLE `white_accounts`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `whitename` varchar(120) NOT NULL COMMENT '手机号码',
  `whitepaas` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `desc` varchar(255) DEFAULT NULL COMMENT '描述',
  `il_id` int(11) NOT NULL DEFAULT 0,
  `limit_type` tinyint(4) NULL DEFAULT 3 COMMENT '0:登录 1:上报 2:默认登录 3:白名单',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '白名单' ROW_FORMAT = Compact;


-- ----------------------------
-- Table structure for apply
-- ----------------------------
DROP TABLE IF EXISTS `apply`;
CREATE TABLE `apply`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `il_id` int(11) NOT NULL DEFAULT 0,
  `source_id` int(11) NOT NULL DEFAULT 0 COMMENT '表单id',
  `limit_type` tinyint(4) NULL DEFAULT 1 COMMENT '0:登录 1:上报 2:默认登录 3:白名单',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `union_px`(`il_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '报名表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for apply_users
-- ----------------------------
DROP TABLE IF EXISTS `apply_users`;
CREATE TABLE `apply_users`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `answer_id` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '答题的id',
  `il_id` int(11) NOT NULL DEFAULT 0,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `limit_type` tinyint(4) NULL DEFAULT 1 COMMENT '0:登录 1:上报 2:默认登录 3:白名单',
  `apply_id` int(11) NOT NULL DEFAULT 0 COMMENT '报名表id',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `union_px`(`il_id`, `phone`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '参与报名人' ROW_FORMAT = Dynamic;