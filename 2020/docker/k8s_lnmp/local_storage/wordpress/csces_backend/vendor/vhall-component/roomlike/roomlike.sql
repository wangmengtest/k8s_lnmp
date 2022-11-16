-- ----------------------------
-- Table structure for room_likes
-- ----------------------------
DROP TABLE IF EXISTS `room_likes`;
CREATE TABLE `room_likes`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` varbinary(32) NULL DEFAULT NULL COMMENT '房间id',
  `account_id` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `created_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;
