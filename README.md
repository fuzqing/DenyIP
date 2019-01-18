# Typecho 管理禁访IP插件 DenyIP

## 插件简介

禁止IP访问网站，此插件是直接使用Nginx的`deny ip;`目前仅适用于Nginx，需要与Nginx配合使用。

建议使用 [typecho-plugin-Access](https://github.com/kokororin/typecho-plugin-Access) 来查看网站访问日志，可以在访问日志界面批量禁访IP。

## 本人博客环境

PHP 版本 7.1.5

Typecho 版本 1.2 (18.1.29)

## 安装方法

1. 到插件目录`/web/usr/plugins/`执行`git clone https://github.com/fuzqing/DenyIP.git`，或者自己下载zip压缩包解压改名`DenyIP`；

2. 检查`denyip.conf`和`denyip.json`是否有读写权限，如果没有请增加；

3. 后台激活插件；

4. 配置nginx：

   1. 在网站配置文件里增加

      ````nginx
      server{
         #网站相关配置
         #在server块里面添加以下配置
         #禁访IP
         include /web/usr/plugins/DenyIP/denyip.conf;
         #禁止直接访问.conf后缀的文件
         location ~* \.(conf)$ {  
            return 404;
         }
      }
      ````

   2. 增加开机启动脚本，可以写在`/etc/rc.d/rc.local`

      ````bash
      #!/bin/bash
      #此脚本是监控denyip.conf这个文件是否变化，变化的话就重载nginx的配置
      #需要用到inotifywait，没有安装的自行安装
      #nginx命令路径，改成你服务器对应的路径
      nginx_command=/usr/bin/nginx
      while EVENT=$(inotifywait -e modify --format '%e' /web/usr/plugins/DenyIP/denyip.conf);do
      if [ "$EVENT" = "MODIFY" ]; then
      	${nginx_command} -s reload
      fi;
      done
      ````

## 操作方式

![denyip.gif](https://huangweitong.com/usr/uploads/2019/01/3146303440.gif)
