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
