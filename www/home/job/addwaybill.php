<?php
// 允许跨域访问
Header ( "Access-Control-Allow-Origin: * " );
// 设置20秒超时
set_time_limit ( 20 );
// 实例化数据库
// 批量往运单仓库插入运单
include_once '../../conf/MySqlDB/mysqlDb.php';
// 实例化数据库
$mysql = new DB();
// 获取当前的最大运单号
$sql = "SELECT MAX(waybill_no) as MAX_waybill_no FROM `dw_0000`;";
$data = $mysql->get_one ( $sql );
var_dump ( $data );
//echo $data ['MAX_waybill_no'];//OK返回类型为数值
//开启事务
$sql_begin="begin;";
$sql_commit="commit;";
$mysql->query($sql_begin);
$i=0;
while ($i<2000) {
	$i++;
	if ($i%200==0) {
		//提交一次事务
		$mysql->query($sql_commit);
		//再开启事务
		$mysql->query($sql_begin);
	}
	$waybillnu=$data ['MAX_waybill_no']+$i;
	try {
		$sql_insert="INSERT INTO `dw_0000` (`id`, `waybill_no`, `times`, `status`, `last_update_time`, `timestamp`) VALUES (NULL, '$waybillnu', '0', '00', '0000-00-00 00:00:00.000000', CURRENT_TIMESTAMP); ";
		$mysql->query($sql_insert);
	} catch (Exception $e) {
		print $e;
	}
	;
}
//提交事务
$mysql->query($sql_commit);	
//返回结果
echo $sucess;
?>