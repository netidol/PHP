<?php
/**
 * 查询这些单号是否有对应的物流快递公司
 */
//允许跨域访问
Header("Access-Control-Allow-Origin: * ");
//设置不要超时
set_time_limit(0);
include_once '../../conf/MySqlDB/mysqlDb.php';
include_once '../../plugin/Http/HttpServer.php';
include_once '../../conf/config.php';
// 实例化数据库
$mysql = new DB();
//实例化网络请求
$http=new HttpRequest();
$request_url="http://www.kuaidi100.com/autonumber/autoComNum?text=";
//每次取出2000没有查询过的个单号，对于状态为90的，另外线程去重新跑，频率可以低
$sql="SELECT waybill_no,times FROM `dw_0000` WHERE status='00' LIMIT 1000;";
$data=$mysql->get_all_simple($sql);
//开启事务
$sql_begin="begin;";
$sql_commit="commit;";
$mysql->query($sql_begin);
$i=0;
foreach ($data as $value) {
	$i++;
	if ($i%50==0) {
		//提交事务，并开启新事务
		$mysql->query($sql_commit);
		$mysql->query($sql_begin);
		
	}
	$waybill_no=$value['waybill_no'];
	$times=$value['times']+1;
	//构造URL
	//$url=$request_url.$waybill_no;
	//请求快递100，查询这个单号是否有对应的物流快递公司
	$ret=$http->post_URL($request_url, $waybill_no);
	$ret=json_decode($ret,true);
	//print  $ret['num']."|".$ret['auto']['0']['comCode'].'<hr>';//输出结果ok
	$company=$ret['auto']['0']['comCode'];
	if ($company!="") {
		
	//var_dump($ret['num']);
	$update_time=date("Y-m-d H:i:s");
	//构造更新运单仓库的SQL
	$sql_update="update dw_0000 set status='10',last_update_time='$update_time',times='$times' where waybill_no='$waybill_no';";
	$mysql->query($sql_update);
	//往可疑数据表插入数据
	$sql_insert= "INSERT INTO `sd_0000` (`id`, `waybill_no`, `company`, `status`, `times`, `last_update_time`, `create_time`) VALUES (NULL, '$waybill_no', '$company', '00', '0', '$update_time', CURRENT_TIMESTAMP);";
	$mysql->query($sql_insert);
	}
	else {
		//没找到物流快递公司
	$sql_update="update dw_0000 set status='90',last_update_time='$update_time',times='$times' where waybill_no='$waybill_no';";
	$mysql->query($sql_update);
	}
}
//提交事务
$mysql->query($sql_commit);
//输出成功的状态
//$sucess=json_decode($sucess);
echo $sucess;
?>