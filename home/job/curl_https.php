<?php 
/** curl 获取 https 请求 
* @param String $url 请求的url 
* @param Array $data 要發送的數據 
* @param Array $header 请求时发送的header 
* @param int $timeout 超时时间，默认30s 
*/ 
function curl_https($url, $data=array(), $header=array(), $timeout=30){ 
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
curl_setopt($ch, CURLOPT_POST, true); 
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 

$response = curl_exec($ch); 

if($error=curl_error($ch)){ 
die($error); 
} 

curl_close($ch); 

return $response; 

} 

// 调用 
$url = 'https://sp0.baidu.com/9_Q4sjW91Qh3otqbppnN2DJv/pae/channel/data/asyncqury'; 
$data = array('appid'=>'4001','com'=>'yuantong','nu'=>'881443775034378914'); 
$header = array('User-Agent'=>'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0','Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8','Accept-Language'=>'zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3','Accept-Encoding'=>'gzip, deflate, br','Cookie'=>'BDUSS=M2QXJTaTF6bHYxclpCN1c0djkxZ3NQflJrLX5LT3BTOVhNMGxhYVMtTkdvRTlYQVFBQUFBJCQAAAAAAAAAAAEAAACT5IcDbmV0aWRvbAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEYTKFdGEyhXam; __cfduid=d7582651e2e71046e342fdc8675ea64301464445517; BAIDUID=60939A920B49FA9F37C52039B5786F0D:FG=1; BIDUPSID=E5603DC18E69E5ED02049D4A32804BC2; PSTM=1466056400; H_PS_PSSID=1440_20516_18281_20415_15350_11927; BDRCVFR[Fc9oatPmwxn]=srT4swvGNE6uzdhUL68mv3','Connection'=>'keep-alive'); 

$response = curl_https($url, $data, $header, 5); 

echo $response; 
?>