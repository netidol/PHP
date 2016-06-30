<?php
//允许跨域访问
Header("Access-Control-Allow-Origin: * ");

//设置不要超时
set_time_limit(0);
//实例化数据库
$myprovince="";
$mycity="";
$address="上海市松江区新车公司】 已收件";
		$province=explode('省',$address);
		echo $province[0].'<hr>';
		echo $province[1].'<hr>';
		$len=strlen($province[0])*1;
		echo $len.'<hr>';
		if ($len>0 &&$province[0]!=$address) {
			//找到省
			$myprovince=$province[0];
			//找市
			$city=explode('市',$province[1]);
			$mycity=$city[0];
			//return array($myprovince, $mycity);
			$myprovince=str_replace("【", "", $myprovince);
			$mycity=str_replace("【", "", $mycity);
			echo $myprovince.'<hr>'.$mycity;
		}
		else  {
			echo "我是else";
			$city=explode('市',$address);
			$myprovince=$city[0];
			$mycity=$city[0];
			$myprovince=str_replace("【", "", $myprovince);
			$mycity=str_replace("【", "", $mycity);
			echo $myprovince.'<hr>'.$mycity;
		}
		
?>