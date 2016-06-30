<?php
/**
 * 对疑似的运单进行实际查询跟踪，看是否有数据，如果有，写跟踪记录，没有的话标记和记录跟踪次数
 * 这里调用的是绿蚂蚁的接口，快递100不太稳定
 */
header("Content-Type: text/html; charset=UTF-8");
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
// 批量取出单号去查询轨迹数据和次数，只取状态为00的
$requit_url = "http://www.lvmae.com/chaxun/express/doquery.html";
//发送请求试试看看
//$waybill_no = "881443775034378914";
//$ret = $http->post_URL ( $requit_url,$waybill_no);
//print $ret . '<hr>';

//list($status,$re)=$http->http_curl_data_lvmayi($requit_url, "id=881443775034378914");
//$re=json_decode($re,true);


$sql = "SELECT waybill_no,company,times FROM `sd_0000` where status='00' limit 500";
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
	// 构造请求URL和请求参数
	$post_msg="id=".$waybill_no;
	//由于绿蚂蚁不是接口，是需要模拟from表单提交，所以不能用post_URL
	list($status,$re) = $http->http_curl_data_lvmayi($requit_url, $post_msg);
	$re=json_decode($re,true);
	//取得当前是否已经签收,解析关键内容
	$status_code=$re ['code'];//-2为不存在，200为正常
	//取得数组内容长度
	//$re_len=count($re['msg']);
	
	// state=3表示已经签收，只把已经签收的记录下来，有效，但是没有签收的，只是改状态就好了，第二天再跟踪，不写跟踪记录表
	if ($status_code != "200") {
		// 状态为-2或者其他，没有找到;更新状态为没有找到20
		$update_time = date ( "Y-m-d H:i:s" );
		$sql_update = "UPDATE `sd_0000` SET `status` = '20',`last_update_time`='$update_time',`times`='$timesadd' WHERE `waybill_no`='$waybill_no'";
		$mysql->query ( $sql_update );
	} else {
		//有数据，取得数组长度
		$re_len=count($re['msg']);
		//判断是否已经签收
		$sign_yn=$re['msg'][0]['status'];
		if ($sign_yn==1) {
		//已经签收
		
		// 已经签收完毕，插入轨迹表，另外插入关键信息表
		$update_time = date ( "Y-m-d H:i:s" );
		$sql_update = "UPDATE `sd_0000` SET `status` = '10',`last_update_time`='$update_time',`times`='$timesadd' WHERE `waybill_no`='$waybill_no'";
		$mysql->query($sql_update);
		// 解析json内容，更新关键信息表和插入跟踪记录
		// 取得data的数组长度
		$tract_len = count($re['msg']);
		// 把所有跟踪记录插入跟踪表中
		foreach ( $re ['msg'] as $track ) {
			$time = $track ['log_time'];
			$status = $track ['status'];
			$context = $track ['content'];
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
		$pickup_time = $re ['msg'] [$tract_len - 1] ['log_time'];
		$context_pickup = $re ['msg'] [$tract_len - 1] ['content'];
		// 从接货内容中解析出来省市区
		list ( $pickup_province, $pickup_city ) = $address->getaddress_p_c_a ( $context_pickup );
		// var_dump($pickup_province) ;
		// var_dump($pickup_city) ;
		print $pickup_province.$pickup_city;
		// 取得签收时间和内容
		$sign_time = $re ['msg'] [0] ['log_time'];
		$context_sign = $re ['msg'] [0] ['content'];
		$arrive_context=$re['msg'][1]['content'];//包含到达网点的消息
		$int = strtotime ( $sign_time ) - strtotime ( $pickup_time );
		// int strtotime ( string time [, int now])
		list ( $sign_province, $sign_city ) = $address->getaddress_p_c_a ( $arrive_context );
		print $sign_province.$sign_city;
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
	}
	// 提交事务


}
$mysql->query($sql_commit);
echo $sucess;
//echo  $re;
//Host: www.lvmae.com
//User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0
//Accept: application/json, text/javascript, */*; q=0.01
//Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3
//Accept-Encoding: gzip, deflate
//Content-Type: application/x-www-form-urlencoded
//X-Requested-With: XMLHttpRequest
//Referer: http://www.lvmae.com/chaxun/express.html?id=881443775034378914
//Content-Length: 21
//Cookie: __cfduid=d2d1fb07348569202dcc847eec815c99d1465889526; Hm_lvt_113a0efbcb8d4e12dac632a3de270881=1465889584,1467101177; CNZZDATA1256389723=257366427-1465886663-http%253A%252F%252Fwww.baidu.com%252F%7C1467098354; CNZZDATA1254113276=1217370556-1465886013-http%253A%252F%252Fwww.lvmae.com%252F%7C1467098598; Hm_lpvt_113a0efbcb8d4e12dac632a3de270881=1467102374; openPutAway=0
//Connection: keep-alive
?>