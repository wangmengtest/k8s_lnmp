-- ----------------------------
-- Table structure for rebroadcast
-- ----------------------------
DROP TABLE IF EXISTS `rebroadcast`;
CREATE TABLE `rebroadcast`  (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `room_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '房间id',
  `source_room_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '被转播房间id',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前状态1转播中0结束',
  `start_time` datetime(0) DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime(0) DEFAULT NULL COMMENT '结束时间',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_room_id`(`room_id`) USING BTREE,
  INDEX `idx_source_room_id`(`source_room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = latin1 COLLATE = latin1_swedish_ci COMMENT = '活动转播拉流记录' ROW_FORMAT = Compact;