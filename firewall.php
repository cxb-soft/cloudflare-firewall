<?php
    function _postgo($url="",$method="GET",$data=array(),$apikey,$email) {
		
		$headers = array(
			'Content-Type: ' . "application/json" ,
			"X-Auth-Key: " . "$apikey" ,
			"X-Auth-Email: " . "$email"
		);
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 360000); //设置超时
		if(0 === strpos(strtolower($url), 'https')) {
			//https请求
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //对认证证书来源的检查
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //从证书中检查SSL加密算法是否存在
		}
		curl_setopt( $ch, CURLOPT_POST, false);
		if($method == "DELETE"){
			curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
		if($method == "POST"){
			curl_setopt( $ch, CURLOPT_POST, true);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        if($method == "PATCH"){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
		//curl_setopt( $ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		$resp = curl_exec ( $ch );
		curl_close ( $ch );
		return $resp;
    }
    function getzone($apikey,$email){
		$url = "https://api.cloudflare.com/client/v4/zones/";
		$response = _postgo($url=$url,$method="GET",$data="",$apikey = $apikey,$email=$email);
		return $response;
	}

    function readfile_content($filepath){
        $filesizeof = filesize(rtrim($filepath));
        if($filesizeof == 0){
            return "";
        }
        else{
            $f = fopen("$filepath","r");
			$content = fread($f,$filesizeof);
			fclose($f);
            return $content;
        }
    }
    function domain_connect($domains){
        $domain_str = "";
        $domainsize = sizeof($domain_str);
        $x = 0;
        foreach($domains as $domain){
            $x += 1;
            if($x ==  $domainsize){
                $domain_str .= $domain;
            }
            else{
                $domain_str .= $domain . ".";
            }
        }
    }
    $apikey = readfile_content("/www/server/panel/plugin/cloudflare/config/apikey.txt");
    $email = readfile_content("/www/server/panel/plugin/cloudflare/config/email.txt");
    echo($apikey."\n".$email."\n");
    $list = scandir('/www/wwwlogs/');
    foreach($list as $logfile){
                
        $thissize = filesize("/www/wwwlogs/$logfile");
        $past_array[] = $thissize;
        $firsttime = true;
        //$now_array["$logfile"] = 
    }
    while(true){
        $x=0;
        if(true){  
            echo("Protecting...\n");
            $now_array = array();
            $attack = false;
            //print_r($past_array);
            foreach($list as $logfile){
                
                $thissize = filesize("/www/wwwlogs/$logfile");
                $pastsize = $past_array[$x];
                //echo($thissize - $pastsize."\n");
                if($thissize - $pastsize >= 20000){
                    if($firsttime == true){
                        $firsttime = false;
                    }
                    else{
                        $attack=true;
                        
                        echo("Attack!\n");
                        
                    }
                }
                //echo(filesize("/www/wwwlogs/log.bsot.cn.log")."\n");
                $now_array[] = $thissize;
                //echo($logfile . "$thissize\n");
                $x += 1;
                //$now_array["$logfile"] = 
            }
            if($attack == true){
                echo("Find Attack!\nStart 'Under attack' mode\n");
                $zones = getzone($apikey,$email);
                //echo($zones."hello");
                $zones = json_decode($zones,true)['result'];
                foreach($zones as $zone){
                    $id = $zone['id'];
                    $url = "https://api.cloudflare.com/client/v4/zones/$id/settings/security_level";
                    //echo($url."\n");
                    _postgo($url=$url,$method="PATCH",$data=array("value"=>"under_attack"),$apikey=$apikey,$email=$email);

                }
            }
            $x = 0;
            //print_r($now_array);
            $past_array = $now_array;
            
        }
        sleep(5);
    }
    

?>