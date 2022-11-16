-- ----------------------------
-- Table structure for export
-- ----------------------------
DROP TABLE IF EXISTS `export`;
CREATE TABLE `export` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `il_id` int(11) NOT NULL COMMENT '直播id',
  `account_id` int(11) NOT NULL COMMENT '操作人id',
  `source_id` varchar(32) NOT NULL DEFAULT '' COMMENT '三方资源标识id 问卷 考试 投票 问答 等',
  `file_name` varchar(128) NOT NULL DEFAULT '' COMMENT '文件名',
  `title` text NOT NULL COMMENT '标题',
  `export` varchar(32) NOT NULL DEFAULT '' COMMENT '导出模块',
  `params` text NOT NULL COMMENT '参数',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '执行状态',
  `ext` varchar(32) NOT NULL DEFAULT '' COMMENT '文件类型',
  `oss_file` varchar(128) default '' not null comment 'oss存储地址',
  `callback` varchar(64)  default '' not null comment '导出函数,命名规则,服务名:方法名',
  `created_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime(0) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;