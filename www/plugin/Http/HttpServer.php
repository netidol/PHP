<?php
//封装常见的HTTP请求类
class HttpRequest{
	/**
	 * 使用file_get_contents发送请求,适合单纯发送请求，不适合交互
	 * @param $post_url 接收请求的URL
	 * @param $msg 需要发送的内容,类型为string
	 * @return 返回结果
	 */
	function post_URL($post_url,$msg)
	{
		//将关键词请求URL		
		$url=$post_url.$msg;
		//利用file_get_contents函数获取接口数据到变量$ret,请求URL可以，如果是文件地址，会导致服务器CPU飙升
		//用PHP获取到的变量含有html字符，下面过滤掉，否则请求写入的时候，由于数据类型不符，无法写入数据库，通过firefox，看消息响应内容可以看到
		//echo $url;
		$ret = file_get_contents($url);
		return $ret;
	}
	
	/**
	 * 使用curl发送请求,适合复杂交互和场景，可以发送json和xml等
	 * @param $url 接收请求的URL
	 * @param $data_string 需要发送的内容,类型为string
	 * @return 返回结果需要用list接收，如list($a,$b);
	 */
   	function http_curl_data($url, $data_string) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8',
			'Content-Length: ' . strlen($data_string))
		);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();

        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array($return_code, $return_content);
    }
    
    
    /**
     * 使用curl发送请求,适合复杂交互和场景，可以发送json和xml等
     * @param $url 接收请求的URL
     * @param $data_string 需要发送的内容,类型为string
     * @return 返回结果需要用list接收，如list($a,$b);
     */
    function http_curl_data_lvmayi($url, $data_string) {
    
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    			'Content-Type: application/x-www-form-urlencoded',
    			'Content-Length: ' . strlen($data_string),'Cookie: __cfduid=d2d1fb07348569202dcc847eec815c99d1465889526; Hm_lvt_113a0efbcb8d4e12dac632a3de270881=1465889584,1467101177; CNZZDATA1256389723=257366427-1465886663-http%253A%252F%252Fwww.baidu.com%252F%7C1467098354; CNZZDATA1254113276=1217370556-1465886013-http%253A%252F%252Fwww.lvmae.com%252F%7C1467098598; Hm_lpvt_113a0efbcb8d4e12dac632a3de270881=1467102374; openPutAway=0')
    			);
    	ob_start();
    	curl_exec($ch);
    	$return_content = ob_get_contents();
    	ob_end_clean();
    
    	$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    	return array($return_code, $return_content);
    }
    
    
    /**
     * 使用curl发送请求,适合复杂交互和场景，可以发送json和xml等
     * @param $url 接收请求的URL
     * @param $data_string 需要发送的内容,类型为string
     * @return 返回结果需要用list接收，如list($a,$b);
     */
    function http_curl_data_baidu($url, $data_string) {
    
    	 $ch = curl_init(); // 启动一个CURL会话
    curl_setopt($ch, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    			'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
    			'Accept-Encoding: gzip, deflate, br' ,'Cookie: BDUSS=M2QXJTaTF6bHYxclpCN1c0djkxZ3NQflJrLX5LT3BTOVhNMGxhYVMtTkdvRTlYQVFBQUFBJCQAAAAAAAAAAAEAAACT5IcDbmV0aWRvbAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEYTKFdGEyhXam; __cfduid=d7582651e2e71046e342fdc8675ea64301464445517; BAIDUID=60939A920B49FA9F37C52039B5786F0D:FG=1; BIDUPSID=E5603DC18E69E5ED02049D4A32804BC2; PSTM=1466056400; H_PS_PSSID=1440_20516_18281_20415_15350_11927; BDRCVFR[Fc9oatPmwxn]=srT4swvGNE6uzdhUL68mv3')
    			);
    	ob_start();
    	curl_exec($ch);
    	$return_content = ob_get_contents();
    	ob_end_clean();
    
    	$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    	return array($return_code, $return_content);
    }
    
    /**
     * [curl 带重试次数]
     * @param  [type]  $url     [访问的url]
     * @param  [type]  $post    [$POST参数]
     * @param  integer $retries [curl重试次数]
     * @return [type]           [description]
     */
    function curl_retry($url, $post = null, $retries = 3){
    	$curl = curl_init($url);
    
    	if(is_resource($curl) === true){
    		curl_setopt($curl, CURLOPT_FAILONERROR, true);
    		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    		if(isset($post) === true){
    			curl_setopt($curl, CURLOPT_POST, true);
    			curl_setopt($curl, CURLOPT_POSTFIELDS, (is_array($post) === true) ? http_build_query($post, "", "&"): $post);
    		}
    
    		$result = false;
    
    		while(($result === false) && (--$retries > 0)){
    			$result = curl_exec($curl);
    		}
    
    		curl_close($curl);
    	}
    
    	return $result;
    }
	
    /**
     * @param 请求，获得浏览器的cookie
     * @return boolean
     */
    public function login() {
    	$url = "https://sp0.baidu.com/9_Q4sjW91Qh3otqbppnN2DJv/pae/channel/data/asyncqury";
    	$post["nu"] = "5086018095";
    	$post["com"] = "debangwuliu";
    	$post["appid"] = "4001";
    	$re = $this->lea->submit($url, $post);
    
    	// 保存cookie
    	$this->cookie = $re['cookie'];
    	file_put_contents($this->cookiePath, $this->cookie);
    
    	// 得到token
    	$this->getWebToken($re['body']);
    
    	return true;
    }
    
    /**
     * 获取cookies
     */
    public function getcookie($url,$dataStr) {
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    // $oldReferer = "https://mp.weixin.qq.com/";
    // $referer = "https://mp.weixin.qq.com/cgi-bin/singlemsgpage";
    // 腾讯接口变化2013-10-30
    $referer = "https://sp0.baidu.com";
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Referer:$referer"));
    
    if($isPost) {
    	curl_setopt($curl, CURLOPT_POST, 0); // 发送一个常规的Post请求
    	curl_setopt($curl, CURLOPT_POSTFIELDS, $dataStr); // Post提交的数据包
    }
    
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 1); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    // curl_setopt($curl, CURLOPT_COOKIEFILE, 'cookie.txt');
    // curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');
    // curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');
    // curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt');
    
    if($cookie) {
    	curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    
    $tmpInfo = curl_exec($curl); // 执行操作
    return $tmpInfo;
    }
	
}