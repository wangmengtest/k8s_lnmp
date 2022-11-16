-- ----------------------------
-- Table structure for order_detail
-- ----------------------------
DROP TABLE IF EXISTS `order_detail`;
CREATE TABLE `order_detail`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `amount` double(10, 2) NOT NULL DEFAULT 0.00 COMMENT '金额',
  `room_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `account_id` int(11) NULL DEFAULT 0 COMMENT '用户id',
  `app_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `status` tinyint(2) NULL DEFAULT 0 COMMENT '0:收入 ，1：支出',
  `channel` tinyint(1) NULL DEFAULT 1 COMMENT '支付方式：1：微信   2：支付包',
  `source` tinyint(3) NULL DEFAULT 0 COMMENT '来源| 0：充值红包  1：礼物   2：打赏   3：提现',
  `trade_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '流水订单号',
  `created_at` datetime(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for income
-- ----------------------------
DROP TABLE IF EXISTS `income`;
CREATE TABLE `income`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户id',
  `total` double(10, 2) NOT NULL DEFAULT 0.00 COMMENT '总额',
  `app_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '应用id',
  `balance` double(10, 2) NOT NULL DEFAULT 0.00 COMMENT '余额',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1:提现完成 ，0：正常',
  `created_at` datetime(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime(0) NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '收入' ROW_FORMAT = Compact;
