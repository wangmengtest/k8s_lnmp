-- ----------------------------
-- Table structure for exam_answers
-- ----------------------------
DROP TABLE IF EXISTS `exam_answers`;
CREATE TABLE `exam_answers`  (
  `answer_id` bigint(20) NOT NULL,
  `join_id` int(11) NOT NULL COMMENT '参会id',
  `room_id` varchar(32) NOT NULL COMMENT '房间id',
  `exam_id` int(11) NOT NULL COMMENT '问卷id',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '答卷开始时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '答卷结束时间',
  `elect_score` int(10) NOT NULL DEFAULT '0' COMMENT '客观题分数（自动计算）',
  `answerer_score` int(10) NOT NULL DEFAULT 0 COMMENT '用户分数',
  `is_graded` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否评分 0-未评分 1-已评分',
  `graded_mark` text COMMENT '评分批阅记录',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '业务扩展字段',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '答题者昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '答题者头像',
  `operator_account_id` int(11) unsigned NOT NULL COMMENT '批阅用户ID',
  `operator_nickname` varchar(200) NOT NULL DEFAULT '' COMMENT '昵称',
  PRIMARY KEY (`answer_id`) USING BTREE,
  INDEX `idx_room_id`(`room_id`) USING BTREE,
  INDEX `idx_exam_id`(`exam_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '考试提交' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for exams
-- ----------------------------
DROP TABLE IF EXISTS `exams`;
CREATE TABLE `exams`  (
  `exam_id` int(11) NOT NULL,
  `title` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `desc` varchar(2048) NOT NULL DEFAULT '' COMMENT '简介',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '业务端扩展字段',
  `account_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户id',
  `app_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '应用id',
  `is_public` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否是公开考卷，1是0否，默认是',
  `source_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '来源id',
  `score` int(10)  NOT NULL DEFAULT '0' COMMENT '试卷总分',
  `question_num` tinyint(4) NOT NULL DEFAULT '0' COMMENT '试题数量',
  `limit_time` int(11) NOT NULL DEFAULT '0' COMMENT '考试限时时长 默认0 为不限时',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '试卷类型 0：试卷库 1:考试',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`exam_id`) USING BTREE,
  INDEX `uni_account_id_and_app_id`(`account_id`, `app_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '考试试卷表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for room_exam_lk
-- ----------------------------
DROP TABLE IF EXISTS `room_exam_lk`;
CREATE TABLE `room_exam_lk`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL COMMENT '问卷id',
  `room_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '房间id',
  `publish` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否发布，1是0否',
  `publish_time` int(11) NOT NULL DEFAULT '0' COMMENT '试卷发布时间',
  `bind` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否绑定，1是0否',
  `answer` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否有答案，1是0否',
  `is_finish` tinyint(1) NOT NULL DEFAULT 0 COMMENT '考试是否结束 0-未结束 1-已结束',
  `finish_time` int(11) NOT NULL DEFAULT 0 COMMENT '结束时间',
  `is_grade` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否评分 0-未评分 1-已评分',
  `is_push_grade` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否发布评分 0-未发布 1-发布',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '扩展业务字段',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  `account_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uni_exam_id`(`exam_id`) USING BTREE,
  INDEX `idx_room_id`(`room_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '房间试卷关联' ROW_FORMAT = Compact;
