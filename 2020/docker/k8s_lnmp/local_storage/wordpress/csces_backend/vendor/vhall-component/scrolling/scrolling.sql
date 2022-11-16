-- ----------------------------
-- Table structure for scrolling
-- ----------------------------
DROP TABLE IF EXISTS `scrolling`;
CREATE TABLE `scrolling`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '直播房间id',
  `scrolling_open` tinyint(2) NOT NULL DEFAULT 0 COMMENT '开启状态 0:关闭 1:开启',
  `text` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文本内容',
  `text_type` tinyint(2) NOT NULL DEFAULT 1 COMMENT '文本类型 1：固定文本  2:固定文本+观看者id昵称',
  `alpha` mediumint(3) NOT NULL DEFAULT 100 COMMENT '文本不透明度 百分比',
  `size` smallint(3) NOT NULL DEFAULT 20 COMMENT '文字大小',
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#ffffff' COMMENT '文字颜色',
  `interval` smallint(3) NOT NULL DEFAULT 20 COMMENT '显示间隔时间   时长/秒',
  `speed` int(10) NOT NULL DEFAULT 6000 COMMENT '文字移动速度:  10000: 慢,  6000:中,  3000:快',
  `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否开启 : 0:关闭 1:开启',
  `position` tinyint(3) NOT NULL COMMENT '位置 1:随机 2:高 3:中 4:低',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '跑马灯' ROW_FORMAT = Compact;