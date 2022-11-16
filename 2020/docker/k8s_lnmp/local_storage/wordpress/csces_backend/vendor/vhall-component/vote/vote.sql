-- ----------------------------
-- Table structure for room_vote_lk
-- ----------------------------
DROP TABLE IF EXISTS `room_vote_lk`;
CREATE TABLE `room_vote_lk`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_id` int(11) NOT NULL COMMENT '投票id',
  `room_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '房间id',
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `finish_time` datetime(0) NULL DEFAULT NULL COMMENT '结束时间',
  `publish` tinyint(4) NULL DEFAULT 0 COMMENT '是否发布，1是0否',
  `bind` tinyint(4) NULL DEFAULT 0 COMMENT '是否绑定，1是0否',
  `is_release` tinyint(1) NULL DEFAULT 0 COMMENT '是否发布评分 0-未发布 1-发布',
  `is_finish` tinyint(1) NULL DEFAULT 0 COMMENT '投票是否结束 0-未结束 1-已结束',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '扩展业务字段',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uni_vote_id_and_room_id`(`vote_id`, `room_id`) USING BTREE,
  INDEX `idx_room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '房间投票关联表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for vote_answers
-- ----------------------------
DROP TABLE IF EXISTS `vote_answers`;
CREATE TABLE `vote_answers`  (
  `answer_id` bigint(20) NOT NULL,
  `join_id` int(11) NOT NULL COMMENT '参会id',
  `room_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '房间id',
  `vote_id` int(11) NOT NULL COMMENT '投票id',
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '业务扩展字段',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`answer_id`) USING BTREE,
  INDEX `idx_room_id`(`room_id`) USING BTREE,
  INDEX `idx_vote_id`(`vote_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '投票提交' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for vote_option_count
-- ----------------------------
DROP TABLE IF EXISTS `vote_option_count`;
CREATE TABLE `vote_option_count`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `option_id` bigint(20) NULL DEFAULT 0 COMMENT '表单选项id',
  `question_id` bigint(20) NOT NULL COMMENT '表单问题id',
  `rvlk_id` bigint(20) NOT NULL COMMENT '房间投票关联id ',
  `option` char(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '投票选项 A-T',
  `count` int(11) NULL DEFAULT 0 COMMENT '选项投票数',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_rvlk_id_and_question_id_and_option`(`rvlk_id`, `question_id`, `option`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '投票数量表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for votes
-- ----------------------------
DROP TABLE IF EXISTS `votes`;
CREATE TABLE `votes`  (
  `vote_id` int(11) NOT NULL,
  `title` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '业务端扩展字段',
  `account_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户id',
  `app_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '应用id',
  `is_public` tinyint(4) NULL DEFAULT 0 COMMENT '是否是公开投票，1是0否，默认是',
  `source_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '来源id',
  `limit_time` int(11) NULL DEFAULT 0 COMMENT '投票限时时长 默认0 为不限时',
  `option_num` tinyint(4) NULL DEFAULT 1 COMMENT '可选选项数量 默认单选',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`vote_id`) USING BTREE,
  INDEX `idx_account_id_and_app_id`(`account_id`, `app_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '投票信息表' ROW_FORMAT = Compact;
