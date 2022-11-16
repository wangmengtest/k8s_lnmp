-- ----------------------------
-- Table structure for pendant
-- ----------------------------
DROP TABLE IF EXISTS `pendant`;
CREATE TABLE `pendant`
(
    `id`          bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `account_id`  int(10) unsigned NOT NULL COMMENT '商家ID',
    `name`        varchar(100)  NOT NULL DEFAULT '' COMMENT '挂件名称',
    `pic`         varchar(1024) NOT NULL DEFAULT '' COMMENT '挂件图片',
    `icon`        varchar(1024) NOT NULL DEFAULT '' COMMENT '挂件图标',
    `pendant_url` varchar(1024) NOT NULL DEFAULT '' COMMENT '挂件链接',
    `type`        tinyint(2) unsigned not null DEFAULT '1' COMMENT '类型；1=推屏挂件，2=固定挂件',
    `is_default`  tinyint(2) not null DEFAULT '-1' COMMENT '是否是默认固定挂件，-1=否，1=是',
    `status`      tinyint(2) not null DEFAULT '1' COMMENT '-1删除,1正常',
    `created_at`  timestamp     NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at`  timestamp     NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`  timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY           `idx_account_name` (`account_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='挂件表';


-- ----------------------------
-- Table structure for pendant_stats
-- ----------------------------
DROP TABLE IF EXISTS `pendant_stats`;
CREATE TABLE `pendant_stats`
(
    `id`              int(10) unsigned NOT NULL AUTO_INCREMENT,
    `il_id`           int(10) unsigned NOT NULL COMMENT '直播ID',
    `pendant_id`      bigint(20) unsigned NOT NULL COMMENT '挂件ID',
    `pendant_type`    tinyint(2) unsigned not null DEFAULT '1' COMMENT '类型；1=推屏挂件，2=固定挂件',
    `pv_num`          int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击次数',
    `uv_num`          int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击人数',
    `push_screen_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推屏总次数',
    `duration`        int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推屏总时长/秒',
    `date`            varchar(16) NOT NULL DEFAULT '0000-00-00',
    `created_at`      timestamp   NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at`      timestamp   NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`      timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY               `idx_il_pendant` (`il_id`,`pendant_id`),
    KEY               `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='直播挂件统计表';

-- ----------------------------
-- Table structure for pendant_operate_record
-- ----------------------------
DROP TABLE IF EXISTS `pendant_operate_record`;
CREATE TABLE `pendant_operate_record`
(
    `id`         int(10) unsigned NOT NULL AUTO_INCREMENT,
    `il_id`      int(10) unsigned NOT NULL COMMENT '直播ID',
    `account_id` int(20) unsigned NOT NULL COMMENT '用户ID',
    `pendant_id` bigint(20) unsigned NOT NULL COMMENT '挂件ID',
    `type`       tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '操作类型，1=点击',
    `date`       varchar(16) NOT NULL DEFAULT '0000-00-00',
    `created_at` timestamp   NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` timestamp   NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY          `idx_il_pendant` (`il_id`,`pendant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='直播挂件操作记录表';