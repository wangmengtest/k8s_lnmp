# vss-laravel

> 使用 laravel 框架重构组件

## 组件回收开发流程

1. 拉取组件代码
2. 初始化组件开发环境

```shell
php artisan component:develop {componentName}
```

该命令主要执行如下操作:

- 创建组件开发相关目录和文件，不需要的可以删除
- 关联组件开发 GIT 仓库 和分支(需要输入)

3. 关联 controller 路由入口，注册 service 和 model

3. 组件开发
4. 组件回收，当开发完毕，当开发测试完毕，回收到组件仓库时执行

```shell
php artian component:recycling {componentName}
```

该命令主要执行如下操作:

- 将在 app/Component 目录下开发的组件 copy 到 vendor/vhall-component 目录下
- 修改组件代码命名空间
- 创建 composer.json, 需要输入相关信息
- 初始化 GIT 仓库

5. 检查并提交代码

- 进入 vendor/vhall-component/{componentName} 目录下检查代码
- 绑定 GIT 仓库(申请仓库)
- 提交并推送代码

6. 组件测试验收阶段，bug 修复

- 可以直接在 vendor/vhall-component/{componentName} 目录下修改代码
- 修改完提交即可

***注意:*** 可以执行如下命令，将程序入口指向 vendor 下的组件

```shell
# 生成 controller, ServiceTrait, ModelTrait 文件
php artisan generator:publish

# 如果存在 config.php 时，执行该命令，向对应的组件插入代码
php artisan generator:build
```





# 部署

1. 部署代码，安装 `composer` 包, 配置 `nginx`
2. 启动队列, 并使用 `supervisor` 管理

```shell
# 详情查看 laravel 文档
laravel队列脚本
[program:queue-vss-laravel]
process_name=queue-vss-laravel
command=php /DIR/projectApp/vss-laravel/artisan queue:work --sleep=1
autostart=true
autorestart=true
user=**
numprocs=1
redirect_stderr=true
stdout_logfile=**
stopwaitsecs=**


# 并发控制命令脚本
[program:queue-vss-laravel]
process_name=queue-vss-laravel
command=php /DIR/projectApp/vss-laravel/artisan perfctl:queue --sleep=1
autostart=true
autorestart=true
user=**
numprocs=1
redirect_stderr=true
stdout_logfile=**
stopwaitsecs=**

```

3. 启动定时任务，只需要把下面的 Cron 条目添加到你的服务器中

```shell
* * * * * php /DIR/projectApp/vss-laravel/artisan schedule:run >> /dev/null 2>&1
```
