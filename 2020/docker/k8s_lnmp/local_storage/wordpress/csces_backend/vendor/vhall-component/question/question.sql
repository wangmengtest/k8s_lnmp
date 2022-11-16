-- ----------------------------
-- Table structure for question_answer_logs
-- ----------------------------
DROP TABLE IF EXISTS `question_answer_logs`;
CREATE TABLE `question_answer_logs`  (
  `answer_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL COMMENT '用户id',
  `answer_id` int(11) NOT NULL COMMENT '微吼云答卷id',
  `question_id` int(11) NOT NULL COMMENT '问卷id',
  `question_log_id` int(11) NOT NULL COMMENT '问卷使用记录表id',
  `q_id` int(11) NOT NULL COMMENT '微吼云问卷id',
  `il_id` int(11) NOT NULL COMMENT '互动直播id',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`answer_log_id`) USING BTREE,
  UNIQUE INDEX `answer_id`(`answer_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '问卷回答记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for question_answers
-- ----------------------------
DROP TABLE IF EXISTS `question_answers`;
CREATE TABLE `question_answers`  (
  `answer_id` bigint(20) NOT NULL,
  `join_id` int(11) NULL DEFAULT NULL COMMENT '参会id',
  `room_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房间id',
  `question_id` int(11) NULL DEFAULT NULL COMMENT '问卷id',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '业务扩展字段',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  PRIMARY KEY (`answer_id`) USING BTREE,
  INDEX `idx_room_id`(`room_id`) USING BTREE,
  INDEX `idx_question_id`(`question_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '问卷提交' ROW_FORMAT = Compact;


-- ----------------------------
-- Table structure for question_logs
-- ----------------------------
DROP TABLE IF EXISTS `question_logs`;
CREATE TABLE `question_logs`  (
  `question_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL COMMENT '用户id',
  `question_id` int(11) NOT NULL COMMENT '问卷id',
  `il_id` int(11) NOT NULL COMMENT '互动直播id',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`question_log_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '问卷使用记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for questions
-- ----------------------------
DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions`  (
  `question_id` int(11) NOT NULL,
  `title` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标题',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '简介',
  `cover` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '封面',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '业务端扩展字段',
  `account_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户id',
  `app_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '应用id',
  `is_public` tinyint(4) NULL DEFAULT 0 COMMENT '是否公共问卷，1是0否，默认是',
  `source_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '来源id',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`question_id`) USING BTREE,
  INDEX `uni_account_id_and_app_id`(`account_id`, `app_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '问卷' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_question_lk
-- ----------------------------
DROP TABLE IF EXISTS `room_question_lk`;
CREATE TABLE `room_question_lk`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NULL DEFAULT NULL COMMENT '问卷id',
  `room_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '房间id',
  `finish_time` datetime(0) NULL DEFAULT NULL COMMENT '结束时间',
  `publish` tinyint(4) NULL DEFAULT 0 COMMENT '是否发布，1是0否',
  `bind` tinyint(4) NULL DEFAULT 0 COMMENT '是否绑定，1是0否',
  `answer` tinyint(4) NULL DEFAULT 0 COMMENT '是否有答案，1是0否',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '扩展业务字段',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uni_question_id_and_room_id`(`question_id`, `room_id`) USING BTREE,
  INDEX `idx_room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '房间问卷关联' ROW_FORMAT = Compact;
