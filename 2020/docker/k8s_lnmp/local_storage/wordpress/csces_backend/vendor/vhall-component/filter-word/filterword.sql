-- ----------------------------
-- Table structure for filter_words
-- ----------------------------
DROP TABLE IF EXISTS `filter_words`;
CREATE TABLE `filter_words` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `keyword` varchar(100) NOT NULL COMMENT '敏感词',
    `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
    `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
    `account_id` int(11) NOT NULL COMMENT '商户id',
    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1-正常，0-删除',
    `il_id` int(11) DEFAULT '0' COMMENT '房间id',
    `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建者id',
    PRIMARY KEY (`id`),
    KEY `index_account_id_il_id` (`account_id`,`il_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='敏感词库';

-- ----------------------------
-- Table structure for filter_words_log
-- ----------------------------
DROP TABLE IF EXISTS `filter_words_log`;
CREATE TABLE `filter_words_log` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `account_id` int(11) NOT NULL COMMENT '发送用户id',
    `il_id` int(11) DEFAULT '0' COMMENT '房间id',
    `content` varchar(1000) NOT NULL COMMENT '敏感词',
    `live_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型 1直播中 2回放',
    `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
    `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
    `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`),
    KEY `index_il_id` (`il_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发送敏感词记录表';



-- 添加菜单
INSERT INTO `menues` (`name`, `url`, `pid`, `sort`, `updated_at`, `created_at`, `deleted_at`)
VALUES
('敏感词管理', 'SENSITIVE', 3, 0, now(), now(), NULL);



-- 添加方法使用
INSERT INTO `actions` ( `controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', "",0, '敏感词控制器', now(), now(), NULL);


INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'listAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-列表', now(), now(), NULL);

INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'createAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-新增', now(), now(), NULL);

INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'updateAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-修改', now(), now(), NULL);

INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'deleteAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-删除', now(), now(), NULL);


INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'templateAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-模版下载', now(), now(), NULL);


INSERT INTO `actions` (`controller_name`, `action_name`, `pid`, `desc`, `updated_at`, `created_at`, `deleted_at`)
VALUES ('FilterwordsController', 'importAction', (select action_id  from (select * from actions) as a where a.`controller_name`='FilterwordsController' and a.pid =0), '敏感词-批量导入', now(), now(), NULL);


