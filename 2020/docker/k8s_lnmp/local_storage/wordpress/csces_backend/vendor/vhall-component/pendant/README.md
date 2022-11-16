# 挂件组件技术文档

[toc]

## 基础说明

### 需求背景

#### 功能介绍

运营者可以通过挂件组件功能提供配置直播间相关挂件，对挂件相关操作统计。

#### 使用目的

运营者可实现在直播间推送挂件，对直播间的挂件的操作进行统计。

### 使用场景

直播

### 安装组件

若初始化代码包中未选择考试组件，可以手动安装组件：

#### composer包加载

> composer require vhall-component/pendant

#### 生成框架组件代码

框架提供脚手架功能用于框架生成组件使用代码:
> php app/bin/console model:component --component={pendant}

## 术语

- **挂件推屏**：指主播端或者助理端将挂件信息推送到观众端，以小卡片信息展示观众屏幕上。

## 需求

## 功能点

### 控制台

- 挂件管理
    - 新增挂件
    - 修改挂件
    - 挂件查看
    - 删除挂件
    - 设置固定默认挂件
- 数据统计
    - 直播间挂件相关数据统计展示

### 直播间主持人端（以及助理端）

- 直播间绑定挂件列表展示
- 将挂件信息推屏到观众端

### 观众端

- 直播间固定挂件展示
- 挂件推屏时，展示挂件信息小卡片

## 系统交互

### 时序图

概览

![时序图.png](时序图.png)

### 流程图

#### 主播端发起推送挂件

![流程图.png](流程图.png)

## 开发说明

### 挂件组件文件目录

### UML类简略图

### 错误码

| 错误码        | 说明           |
| ------------- |:-------------:| 
| 56001      | 设置固定悬浮失败！ |
| 56002      | 挂件推屏失败！ |

## 数据库设计

### 关联模型

![关联模型.png](关联模型.png)

### 数据字典

#### 挂件表

```pendant```

| 字段名 | 类型 | 是否为空 | 默认值 | 说明 | 
|------------- |-------------:| -----:|---------:|----------:| 
|`id` |bigint(20) | false| |主键 |
|`account_id` |int(10) | false|0 |商家ID |
|`name` |varchar(100) | false|"" |挂件名称 |
|`pic` |varchar(1024) | false|"" |挂件图片 | 
|`icon` |varchar(1024) | false|"" |挂件图标 | 
|`pendant_url` |varchar(1024) | false|"" |挂件链接 | 
|`type`|tinyint(2) | false|"1" |类型；1=推屏挂件，2=固定挂件 | 
|`status`|tinyint(2) | false|"1" |-1删除,1正常 | 
|`is_default`|tinyint(2) | false|"1" |是否是默认固定挂件，-1=否，1=是 | 
|`created_at` |timestamp | false|"0000-00-00 00:00:00" |创建时间 | 
|`updated_at` |timestamp | false|"0000-00-00 00:00:00" |修改时间 |
|`deleted_at` |timestamp | false|null |删除时间 |

#### 直播挂件-统计表

```pendant_stats```

| 字段名 | 类型 | 是否为空 | 默认值 | 说明 | 
| ------------- |:-------------:| -----:|---------:|----------:| 
|`id` | int(10)|false | |主键 | 
|`pendant_id` | bigint(20)| false|0 |挂件ID | 
|`il_id` | int(10)| false|0 |直播间id | 
|`pv_num` | int(10)| false|0 |点击次数 |
|`uv_num` | int(10)| false|0 |点击人数 | 
|`push_screen_num` | int(10)| false|0 |推屏总次数 | 
|`duration` | int(10)| false|0 |推屏总时长/秒 | 
|`date` | varchar(16)| false|"" |日期 | 
|`created_at` |timestamp | false|"0000-00-00 00:00:00" |创建时间 | 
|`updated_at` |timestamp | false|"0000-00-00 00:00:00" |修改时间 | 
|`deleted_at` |timestamp | false|null |删除时间 |

#### 直播挂件-观众操作记录表

```pendant_operate_record```

| 字段名 | 类型 | 是否为空 | 默认值 | 说明 | 
| ------------- |:-------------:| -----:|---------:|----------:| 
|`id` | bigint(20)|false| |主键 | 
|`pendant_id` | bigint(20)| false|0 |挂件ID | 
|`il_id` | int(10)| false|0 |直播间id | 
|`account_id` |int(10) | false|0|操作人用户id | 
|`type` |int(10) | false|1 |操作类型，1=点击 | 
|`date` | varchar(16)| false|"" |日期 | 
|`created_at` |timestamp | false|"0000-00-00 00:00:00" |创建时间 | 
|`updated_at` |timestamp | false|"0000-00-00 00:00:00" |修改时间 | 
|`deleted_at` |timestamp | false|null |删除时间 |

## 缓存设计

| 缓存名称        | 数据类型  | 过期时间(秒)  | 缓存key    | 说明    |
| ------------- |------:| -----:| -----:| -----:|
|房间绑定挂件首页数据缓存 |string |10800 |liveGoods:list:{房间id} |- |
|观众端挂件列表入口开关缓存 |string |86400 |liveGoods:entranceSwith:{房间id} |- |

## 部署说明

### 定时脚本

#### 更新绑定挂件统计pv和uv

> 更新绑定挂件统计pv和uv
>
> */20 * * * *   /bin/bash /app/bin/cron.sh   cron/livegoods/sync-stats

### 队列

无