<?php
/**
 * 对疑似的运单进行实际查询跟踪，看是否有数据，如果有，写跟踪记录，没有的话标记和记录跟踪次数
 */

// 允许跨域访问
Header ( "Access-Control-Allow-Origin: * " );
// 设置不要超时
set_time_limit ( 0 );
// 实例化数据库
include_once '../../conf/MySqlDB/mysqlDb.php';
include_once '../../plugin/Http/HttpServer.php';
include_once '../../conf/config.php';
include_once '../../plugin/File/time.php';
include_once '../../plugin/File/address.php';

// 实例化数据库
$mysql = new DB ();
// 实例化网络请求
$http = new HttpRequest ();
// 实例化地址解析
$address = new address ();
// 批量取出单号去查询轨迹数据和次数，只取状态为20的，前面已经用绿蚂蚁查询过，这里用快递100补刀
$requit_url1 = "http://www.kuaidi100.com/query?type=";
$requit_url2 = "&postid=";
$requit_url3 = "&id=1&valicode=&temp=0.007023492";
$sql = "SELECT waybill_no,company,times FROM `sd_0000` where status='00' limit 4000,1000";
$data = $mysql->get_all_simple ( $sql );
//开启事务
$sql_begin="begin;";
$sql_commit="commit;";
$mysql->query($sql_begin);
$i=0;
// var_dump($data);
foreach ( $data as $smart ) {
	$i++;
	if ($i%50==0) {
		//提交事务，并开启新事务
		$mysql->query($sql_commit);
		$mysql->query($sql_begin);
	
	}
	$waybill_no = $smart ['waybill_no'];
	$company = $smart ['company'];
	$times = $smart ['times'];
	$timesadd=$times+1;
	// tesing
	//$waybill_no = "881443775034378914";
	//$company = "yuantong";
	// 构造请求URL
	// print $waybill_no.$company.$times;
	$url = $requit_url1 . $company . $requit_url2 . $waybill_no . $requit_url3;
	//print $url . '<hr>';
	$ret = $http->post_URL ( $url, "" );
	//print $ret . '<hr>';
	// {"status":"201","message":"快递公司参数异常：单号不存在或者已经过期"}
	// 如果是错的，返回201的状态,如果是正确的，status是200，message是ok
	$ret = json_decode ( $ret, true );
	//print $ret ['status'] . $ret ['message'] . $ret ['state'];
	// state=3表示已经签收，只把已经签收的记录下来，有效，但是没有签收的，只是改状态就好了，第二天再跟踪，不写跟踪记录表
	if ($ret ['status'] == "201") {
		// 状态为201，没有找到;更新状态为没有找到20
		$update_time = date ( "Y-m-d H:i:s" );
		$sql_update = "UPDATE `sd_0000` SET `status` = '20',`last_update_time`='$update_time',`times`='$timesadd' WHERE `waybill_no`='$waybill_no'";
		$mysql->query ( $sql_update );
	} elseif ($ret ['state'] == '3') {
		// 已经签收完毕，插入轨迹表，另外插入关键信息表
		$update_time = date ( "Y-m-d H:i:s" );
		$sql_update = "UPDATE `sd_0000` SET `status` = '10',`last_update_time`='$update_time',`times`='$timesadd' WHERE `waybill_no`='$waybill_no'";
		 $mysql->query($sql_update);
		// 解析json内容，更新关键信息表和插入跟踪记录
		// 取得data的数组长度
		$tract_len = count ( $ret ['data'] );
		// 把所有跟踪记录插入跟踪表中
		foreach ( $ret ['data'] as $track ) {
			$time = $track ['time'];
			$ftime = $track ['ftime'];
			$context = $track ['context'];
			// print $time.$ftime.$context;
			// 插入轨迹表
			// 对运动和内容求MD5
			$needmd5 = $waybill_no . $time . $context;
			$contextmd5 = MD5 ( $needmd5 );
			print $contextmd5;
			$sql_insert = "INSERT INTO `track_0000` (`id`, `nun`, `company`, `content`, `time`, `log_time`, `status`, `unique`, `send_status`) VALUES (NULL, '$waybill_no', '$company', '$context', CURRENT_TIMESTAMP, '$time', NULL, '$contextmd5', '0');";
			 $mysql->query($sql_insert);
		}
		
		// 插入关键表，用于后期分析数据用
		// 取得收件时的时间和内容
		$pickup_time = $ret ['data'] [$tract_len - 1] ['time'];
		$context_pickup = $ret ['data'] [$tract_len - 1] ['context'];
		// 从接货内容中解析出来省市区
		list ( $pickup_province, $pickup_city ) = $address->getaddress_p_c_a ( $context_pickup );
		// var_dump($pickup_province) ;
		// var_dump($pickup_city) ;
		// 取得签收时间和内容
		$sign_time = $ret ['data'] [0] ['time'];
		$context_sign = $ret ['data'] [0] ['context'];
		$int = strtotime ( $sign_time ) - strtotime ( $pickup_time );
		// int strtotime ( string time [, int now])
		list ( $sign_province, $sign_city ) = $address->getaddress_p_c_a ( $context_sign );
		// 对于已经签收运单，再插入关键表
		print $pickup_time . '<hr>' . $sign_time . '<hr>' . $int;
		$update_time = date ( "Y-m-d H:i:s" );
		$sql_ruest_insert = "INSERT INTO `result_0000` (`id`, `waybill_no`, `company`, `pickup_time`, `sign_time`, `pickup_province`, `pickup_city`, `arrived_province`, `arrived_city`, `insert_time`, `used_time`) VALUES (NULL, '$waybill_no', '$company', '$pickup_time', '$sign_time', '$pickup_province', '$pickup_city', '$sign_province', '$sign_city', CURRENT_TIMESTAMP, '$int');";
		$mysql->query ( $sql_ruest_insert );
	} else {
		// 订单存在，但是没有签收，标记状态，但是不插入轨迹和关键信息表
		$update_time = date ( "Y-m-d H:i:s" );
		$sql_update = "UPDATE `sd_0000` SET `status` = '15',`last_update_time`='$update_time' WHERE `waybill_no`='$waybill_no'";
		$mysql->query ( $sql_update );
	}
	// 提交事务
	$mysql->query($sql_commit);
	echo $sucess;
}
?>