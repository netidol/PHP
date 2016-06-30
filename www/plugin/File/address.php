<?php
//传入一段地址，解析出来省，市，地址

header("Content-type:text/html;Charset=utf-8");
class address{
	
	private $myprovince;
	private $mycity;
	/**
	 * @param $address 传入的地址
	 * @param $return 返回的为一个数组，可以用list($a,$b)接收
	 */
	function getaddress_p_c_a($address) {
		//对地址进行分割，找省是否能找到
		$province=explode('省',$address);
		$len=strlen($province[0])*1;
		if ($len>0&&$province[0]!=$address) {
			//找到省
			$myprovince=$province[0];
			//找市
			$city=explode('市',$province[1]);
			$mycity=$city[0];
			//return array($myprovince, $mycity);
			$myprovince=str_replace("【", "", $myprovince);
			$mycity=str_replace("【", "", $mycity);
			return array($myprovince, $mycity);
		}
		else
		{
		//找不到省，找市吧,这些是直辖市，省市全部是市即可
			$city=explode('市',$address);
			$myprovince=$city[0];
			$mycity=$city[0];
			$myprovince=str_replace("【", "", $myprovince);
			$mycity=str_replace("【", "", $mycity);
			return array($myprovince, $mycity);
		}
		
		
	}
	//test
	
	
}
?>

