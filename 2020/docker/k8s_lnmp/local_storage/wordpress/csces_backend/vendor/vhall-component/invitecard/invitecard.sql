-- ----------------------------
-- Table structure for invite_cards
-- ----------------------------
DROP TABLE IF EXISTS `invite_cards`;
CREATE TABLE `invite_cards`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '房间id',
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `date` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '活动时间',
  `company` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '主办方',
  `desciption` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '简介',
  `location` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '地点',
  `welcome_txt` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '欢迎语',
  `img` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图片地址',
  `show_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '展现方式',
  `img_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '图片类别0默认上传否则标示系统素材1-9',
  `is_show_watermark` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否隐藏水印0显示水印1关闭水印',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_webinar`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '活动-邀请卡' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_invites
-- ----------------------------
DROP TABLE IF EXISTS `room_invites`;
CREATE TABLE `room_invites`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '房间ID',
  `invite_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '邀请id',
  `be_invited_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '被邀请id',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `webinar_invites_webinar_id_index`(`room_id`) USING BTREE,
  INDEX `webinar_invites_invite_id_index`(`invite_id`, `be_invited_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '活动邀请详情' ROW_FORMAT = Compact;
