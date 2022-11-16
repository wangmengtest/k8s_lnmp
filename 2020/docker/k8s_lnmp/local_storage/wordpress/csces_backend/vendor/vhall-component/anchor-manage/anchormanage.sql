-- ----------------------------
-- Table structure for tag
-- ----------------------------
DROP TABLE IF EXISTS `anchor_manage`;
CREATE TABLE `anchor_manage`  (
                                   `anchor_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主播id',
                                   `account_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联账户id',
                                   `nickname` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
                                   `real_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '真实姓名',
                                   `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
                                   `avatar` varchar(200) NOT NULL DEFAULT '' COMMENT '头像',
                                   `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                   `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                                   `deleted_at` datetime(0) DEFAULT NULL,
                                   PRIMARY KEY (`anchor_id`) USING BTREE,
                                   UNIQUE INDEX `phone_unique`(`phone`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '主播表' ROW_FORMAT = Compact;
