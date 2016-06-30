<?php 
//使用mycat中间件的配置，读写分离
    $db_config["hostname"] = "10.10.11.174:3306"; //数据库ip和端口，默认3306
    $db_config["username"] = "ueEbNyrKpv3cwluM"; //用户名
    $db_config["password"] = "peuFVaywBXKLgkEjI"; //密码
    $db_config["database"] = "JlRi2kwOsVALd4jn"; //数据库schema
    $db_config["charset"] = "utf8";//数据编码utf-8
    $db_config["pconnect"] = 0;//开启持续连接
    $db_config["log"] = 0;//是否开启日志，0,1
    $db_config["logfilepath"] = './';//日志目录
?>