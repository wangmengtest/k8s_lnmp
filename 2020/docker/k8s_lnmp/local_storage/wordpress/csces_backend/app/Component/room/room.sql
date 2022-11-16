-- ----------------------------
-- Table structure for dibbling
-- ----------------------------
DROP TABLE IF EXISTS `dibbling`;
CREATE TABLE `dibbling`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '\'PAAS直播房间id\'',
  `vod_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '点播ID',
  `start_time` datetime(0) DEFAULT NULL COMMENT '开始时间',
  `is_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否失效 0 未失效 1 已失效',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `deleted_at` datetime(0) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_room`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '点播转直播表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for inav_stats
-- ----------------------------
DROP TABLE IF EXISTS `inav_stats`;
CREATE TABLE `inav_stats`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `il_id` int(11) UNSIGNED NOT NULL COMMENT '互动直播id',
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `flow` int(11) NOT NULL DEFAULT 0 COMMENT '流量/KB',
  `pv_num` int(11) NOT NULL DEFAULT 0 COMMENT 'pv量/次',
  `uv_num` int(11) NOT NULL DEFAULT 0 COMMENT 'uv量/人',
  `duration` int(11) NOT NULL DEFAULT 0 COMMENT '互动时长/秒',
  `created_time` datetime(0) DEFAULT NULL COMMENT '统计时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_ilid`(`il_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '互动统计表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for rooms
-- ----------------------------
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms`  (
  `il_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '房间id',
  `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'PAAS直播房间id',
  `subject` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房间标题',
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `inav_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'PAAS互动房间id',
  `channel_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'PAAS频道ID',
  `nify_channel` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'PAAS通知频道ID',
  `record_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '默认回放id',
  `start_time` datetime(0) DEFAULT NULL COMMENT '预计开始时间',
  `begin_time_stamp` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间时间戳',
  `introduction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '直播介绍',
  `category` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属列表',
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面图片地址',
  `topics` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签,多个逗号隔开',
  `layout` tinyint(4) NOT NULL DEFAULT 1 COMMENT '布局>1|为单视频,2|音频+文档,3|文档+视频',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态>0|待直播/预约,1|直播中,2|直播结束',
  `is_delete` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除>0|否,1|是',
  `message_approval` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '聊天审核 1允许 2阻止',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
  `app_id` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'paasAppId',
  `like` int(11) NULL DEFAULT 0 COMMENT '点赞数',
  `deleted_at` datetime(0) DEFAULT NULL,
  `live_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '直播类型 1 互动直播 2 纯直播',
  `warm_type` tinyint(1) NULL DEFAULT 0 COMMENT '暖场类型| 0：图片 1：视频   ',
  `warm_vod_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '暖场视频id',
  `teacher_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '讲师名称',
  `begin_live_time` datetime(0) DEFAULT NULL COMMENT '直播开始时间',
  `end_live_time` datetime(0) DEFAULT NULL COMMENT '直播结束时间',
  `is_open_document` tinyint(1) NOT NULL DEFAULT 0 COMMENT '开启文档>0|未开启,1|已开启',
  `live_mode` tinyint(1) NOT NULL DEFAULT 0 COMMENT '直播模式',
  `message_total` int(11) NOT NULL DEFAULT 0 COMMENT '聊天总数',
  `mode` tinyint(1) NOT NULL DEFAULT 1 COMMENT '模式>1|助理模式,0|普通模式',
  `limit_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:登录 1:报名 2:默认 3:白名单',
  `extend` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '配置扩展',
  PRIMARY KEY (`il_id`) USING BTREE,
  INDEX `idx_room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '房间表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_attends
-- ----------------------------
DROP TABLE IF EXISTS `room_attends`;
CREATE TABLE `room_attends`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `il_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL COMMENT '用户id',
  `watch_account_id` int(11) NOT NULL,
  `start_time` datetime(0) DEFAULT NULL,
  `end_time` datetime(0) DEFAULT NULL,
  `duration` int(100) NOT NULL COMMENT '观看时长,单位秒',
  `terminal` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '终端',
  `browser` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '浏览器',
  `country` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '国家',
  `province` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '地域',
  `type` tinyint(3) NULL DEFAULT 1 COMMENT '数据来源 1-直播房间数据 2-互动房间数据',
  `created_time` datetime(0) DEFAULT NULL COMMENT '统计时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `deleted_at` datetime(0) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_ilid`(`il_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '互动直播-访问统计表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_extends
-- ----------------------------
DROP TABLE IF EXISTS `room_extends`;
CREATE TABLE `room_extends`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房间id',
  `start_time` datetime(0) DEFAULT NULL COMMENT '开播时间',
  `end_time` datetime(0) DEFAULT NULL COMMENT '结束时间',
  `start_type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '开播类型1 web 2 app 3 sdk 4 推拉流 5 定时 6 admin后台 7第三方8 助手 ',
  `end_type` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '结束类型1 web 2 app 3 sdk 4 推拉流 5 定时 6 admin后台 7第三方8 助手 ',
  `is_delete` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除>0|否,1|是',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `index_room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '房间扩展信息表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_supply
-- ----------------------------
DROP TABLE IF EXISTS `room_supply`;
CREATE TABLE `room_supply` (
  `il_id` int(11) unsigned NOT NULL COMMENT '房间ID',
  `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'paas 房间ID',
  `account_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `custom_tag` text COLLATE utf8_unicode_ci COMMENT '用户自定义简介tag',
  `mode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1,互动助理模式,0普通模式',
  `assistant_sign` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '助理口令',
  `interaction_sign` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '互动口令',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`il_id`),
  KEY `room_id` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='直播间信息补充表';

-- ----------------------------
-- Table structure for room_joins
-- ----------------------------
DROP TABLE IF EXISTS `room_joins`;
CREATE TABLE `room_joins`  (
  `join_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `room_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '房间id',
  `account_id` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '用户账号',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户头像',
  `role_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色信息，1主持人2观众3助理4嘉宾',
  `is_banned` tinyint(4) NULL DEFAULT 0 COMMENT '是否禁言，1是0否',
  `is_kicked` tinyint(4) NULL DEFAULT 0 COMMENT '是否踢出，1是0否',
  `device_type` int(11) NULL DEFAULT 0 COMMENT '设备类型，0未检测 1手机端 2PC 3SDK',
  `device_status` int(11) NULL DEFAULT 0 COMMENT '设备状态，0未检测1可以上麦2不可以上麦',
  `is_signed` tinyint(1) NULL DEFAULT 0 COMMENT '是否签到：1 是 0 否',
  `is_answered_questionnaire` tinyint(1) NULL DEFAULT 0 COMMENT '是否回答过问卷：1 是 0 否',
  `is_lottery_winner` tinyint(1) NULL DEFAULT 0 COMMENT '是否已经成为抽奖中奖者：1 是 0 否',
  `is_answered_vote` tinyint(1) NULL DEFAULT 0 COMMENT '是否投过票：1 是 0 否',
  `is_answered_exam` tinyint(1) NULL DEFAULT 0 COMMENT '是否回答过试卷：1 是 0 否',
  `status` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '在线状态：0 离线 1 在线',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`join_id`) USING BTREE,
  UNIQUE INDEX `uniq_room_account`(`room_id`, `account_id`) USING BTREE,
  INDEX `is_signed`(`is_signed`, `room_id`, `created_at`, `deleted_at`) USING BTREE,
  INDEX `is_answered_questionnaire`(`is_answered_questionnaire`, `room_id`, `created_at`, `deleted_at`) USING BTREE,
  INDEX `is_lottery_winner`(`is_lottery_winner`, `room_id`, `created_at`, `deleted_at`) USING BTREE,
  INDEX `is_answered_exam`(`is_answered_exam`, `room_id`, `created_at`, `deleted_at`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '参会成员表' ROW_FORMAT = Compact;


-- ----------------------------
-- Table structure for room_stats
-- ----------------------------
DROP TABLE IF EXISTS `room_stats`;
CREATE TABLE `room_stats`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `il_id` int(10) UNSIGNED NOT NULL COMMENT '互动直播id',
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户id',
  `flow` int(10) NOT NULL DEFAULT 0 COMMENT '流量/KB',
  `bandwidth` int(10) NOT NULL DEFAULT 0 COMMENT '带宽/kbps',
  `pv_num` int(10) NOT NULL DEFAULT 0 COMMENT 'pv量/次',
  `uv_num` int(10) NOT NULL DEFAULT 0 COMMENT 'uv量/人',
  `duration` int(10) NOT NULL DEFAULT 0 COMMENT '观看时长/秒',
  `created_time` datetime(0) DEFAULT NULL COMMENT '统计时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_ilid`(`il_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '直播-用量统计表' ROW_FORMAT = Compact;

-- -------------------------------------
-- Table structure for room_connect_counts
-- -------------------------------------
DROP TABLE IF EXISTS `room_connect_counts`;
CREATE TABLE `room_connect_counts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `il_id` bigint(11) NOT NULL,
  `channel` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '聊天频道',
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  `account_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `create_time` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='连接数表';

-- ----------------------------
-- Table structure for third_stream
-- ----------------------------
DROP TABLE IF EXISTS `third_stream`;
CREATE TABLE `third_stream`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '推流地址',
  `account_id` int(50) NULL DEFAULT 0,
  `app_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `room_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '房间id',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '1：开始,   2：停止  ',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '第三方推流' ROW_FORMAT = Compact;
