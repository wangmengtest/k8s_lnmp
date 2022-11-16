-- 用户列表-添加字段
ALTER TABLE `accounts` ADD COLUMN `org` VARCHAR(200) DEFAULT '' NOT NULL COMMENT '组织ID' AFTER `deleted_at`, ADD COLUMN `org_name` VARCHAR(200) DEFAULT '' NOT NULL COMMENT '组织名称' AFTER `org`, ADD COLUMN `user_type` TINYINT(1) DEFAULT 1 NOT NULL COMMENT '1:外部 2:内部' AFTER `org_name`, ADD COLUMN `dept` VARCHAR(200) DEFAULT '' NOT NULL COMMENT '部门ID' AFTER `user_type`, ADD COLUMN `role_id` TINYINT(1) DEFAULT 1 NOT NULL COMMENT '1管理员 2普通用户' AFTER `dept`;
-- 用户表自增ID从100000开始 避免与OrgID重复
ALTER TABLE accounts AUTO_INCREMENT = 1000000;

-- 用户组织表
CREATE TABLE `account_org` (
                               `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
                               `code` varchar(200) NOT NULL DEFAULT '' COMMENT '组织编码',
                               `name` varchar(200) NOT NULL DEFAULT '' COMMENT '组织名称',
                               `parent_org` varchar(200) NOT NULL DEFAULT '' COMMENT '上级组织id',
                               `org` varchar(200) NOT NULL DEFAULT '' COMMENT '组织id',
                               `org_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '类型0组织 1部门',
                               `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
                               `org_id` int(11) DEFAULT '0' COMMENT '中建自增ID',
                               `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
                               `created_at` datetime DEFAULT NULL COMMENT '创建时间',
                               PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=273 DEFAULT CHARSET=utf8


-- 用户组织表加user_id
ALTER TABLE `accounts` ADD COLUMN `user_id` INT(11) DEFAULT 0 NOT NULL COMMENT '中建user_id' AFTER `role_id`;

-- 添加密码字段
ALTER TABLE `accounts` ADD COLUMN `password` VARCHAR(100) DEFAULT '' NOT NULL COMMENT '密码' AFTER `user_id`;

-- room 添加字段
ALTER TABLE `rooms` ADD COLUMN `show_duration` INT(11) DEFAULT 0 NOT NULL COMMENT '预告直播时长' AFTER `extend`, ADD COLUMN `notice_time` DATETIME NULL COMMENT '通知时间' AFTER `show_duration`, ADD COLUMN `show_chat` TINYINT(1) DEFAULT 1 NOT NULL COMMENT '聊天和公告 1:打开 2:关闭' AFTER `notice_time`;
ALTER TABLE `rooms` CHANGE `limit_type` `limit_type` TINYINT(4) DEFAULT 0 NOT NULL COMMENT '0:登录 1:报名 2:默认/公开模式 3:白名单 4:内部模式';

-- 互动直播-邀请的关系表
CREATE TABLE `room_invited` (
                                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                `il_id` int(11) NOT NULL,
                                `account_id` int(11) NOT NULL COMMENT '用户id',
                                `room_role` tinyint(2) NOT NULL COMMENT '会议角色：1--主持人；2--观众；3--助理 4：嘉宾 5：飞手',
                                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                `deleted_at` datetime NOT NULL,
                                PRIMARY KEY (`id`) USING BTREE,
                                KEY `idx_ilid` (`il_id`) USING BTREE,
                                KEY `idx_accountid` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT COMMENT='互动直播-邀请的关系表'

-- room加组织标识
ALTER TABLE `rooms` ADD COLUMN `org` VARCHAR(100) DEFAULT '' NOT NULL COMMENT '组织ID' AFTER `show_chat`, ADD COLUMN `dept` VARCHAR(100) DEFAULT '' NOT NULL COMMENT '部门ID' AFTER `org`, ADD COLUMN `org_level` TINYINT(4) DEFAULT 0 NOT NULL COMMENT '部门等级' AFTER `dept`;
-- 同级及以下部门
ALTER TABLE `account_org` ADD COLUMN `depts` VARCHAR(50) DEFAULT '' NOT NULL COMMENT '同级及以下部门' AFTER `created_at`;
-- 同级及以下部门
ALTER TABLE `account_org` ADD COLUMN `orgs` VARCHAR(400) DEFAULT '' NOT NULL COMMENT '同级组织及以下组织' AFTER `depts`;
-- 会议表修改字段
-- 会议表修改字段
ALTER TABLE rooms modify column org INT(11) DEFAULT 0 NOT NULL COMMENT '组织ID';
ALTER TABLE rooms modify column dept INT(11) DEFAULT 0 NOT NULL COMMENT '部门ID';
-- rooms添加索引
ALTER TABLE `rooms` ADD INDEX `idx_account_id` (`account_id`), ADD INDEX `idx_org` (`org`), ADD INDEX `idx_dept` (`dept`);
-- account_org添加字段
ALTER TABLE `account_org` ADD COLUMN `depts` VARCHAR(200) DEFAULT '' NOT NULL COMMENT '同级及以下部门' AFTER `created_at`, ADD COLUMN `orgs` VARCHAR(400) DEFAULT '' NOT NULL COMMENT '同级及以下组织' AFTER `depts`;

-- 2021-9-7
-- 创建人姓名
ALTER TABLE `rooms` ADD COLUMN `account_name` VARCHAR(50) DEFAULT '' NOT NULL COMMENT '创建人姓名' AFTER `account_id`;
-- room添加索引
ALTER TABLE `rooms` ADD INDEX `idx_createdat` (`created_at`);
-- room添加索引
ALTER TABLE `rooms` ADD INDEX `idx_begin_stamp` (`begin_time_stamp`);
-- 全量-访问表
CREATE TABLE `room_attends_all` (
                                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                    `il_id` int(11) unsigned NOT NULL COMMENT '互动直播id',
                                    `account_id` int(11) unsigned NOT NULL COMMENT '用户id',
                                    `record_id` varchar(32) NOT NULL DEFAULT '' COMMENT '回放id',
                                    `watch_account_id` int(11) NOT NULL COMMENT '观众用户id',
                                    `start_time` datetime DEFAULT NULL COMMENT '进入时间',
                                    `end_time` datetime DEFAULT NULL COMMENT '离开时间',
                                    `duration` int(100) NOT NULL COMMENT '观看时长/秒',
                                    `terminal` varchar(100) NOT NULL DEFAULT '' COMMENT '终端',
                                    `browser` varchar(100) NOT NULL DEFAULT '' COMMENT '浏览器',
                                    `country` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '国家',
                                    `province` varchar(100) NOT NULL DEFAULT '' COMMENT '地域',
                                    `type` tinyint(3) DEFAULT '1' COMMENT '数据来源 1-直播房间数据 2-互动房间数据',
                                    `attend_type` tinyint(3) DEFAULT '1' COMMENT '数据来源 1-直播 2-回放',
                                    `created_time` datetime DEFAULT NULL COMMENT '统计时间',
                                    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
                                    `deleted_at` datetime DEFAULT NULL,
                                    PRIMARY KEY (`id`) USING BTREE,
                                    KEY `idx_ilid` (`il_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='全量-访问统计表';

-- 加索引
ALTER TABLE `d_csces`.`room_joins` ADD INDEX `idx_updatedat` (`updated_at`);
-- notice扩大内容字段
ALTER TABLE `notices` CHANGE `content` `content` VARCHAR(800) CHARSET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容';
-- 用户添加索引
ALTER TABLE `accounts` ADD INDEX `idx_username` (`username`);
-- 添加索引
ALTER TABLE `accounts` ADD INDEX `idx_org` (`org`), ADD INDEX `idx_dept` (`dept`);

-- 2021-09-23
-- ALTER TABLE `accounts` DROP COLUMN `depts`, DROP COLUMN `orgs`;

ALTER TABLE `accounts` ADD COLUMN `pro_id` INT(11) DEFAULT 0 NOT NULL COMMENT '项目ID' AFTER `password`;


-- 2021-10-19
ALTER TABLE `accounts` ADD COLUMN `c_user_id` VARCHAR(50) DEFAULT '' NOT NULL COMMENT '中建c_user_id验证密码时候用' AFTER `pro_id`;
-- 2021-12-31
ALTER TABLE `account_org` CHANGE `orgs` `orgs` TEXT CHARSET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '同级组织及以下组织';

