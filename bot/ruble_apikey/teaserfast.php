<?php

const
versi = "0.0.1",
host = "https://teaserfast.ru/",
refflink = "https://teaserfast.ru/a/iewilmaestro",
youtube = "https://youtube.com/@iewil";

class Bot {
	function __construct(){
		Display::Ban(title, versi);
		
		cookie:
		Display::Cetak("Register",refflink);
		Display::Line();
			
		$this->cookie = Functions::setConfig("cookie");
		$this->uagent = Functions::setConfig("user_agent");
		Functions::view();
		
		$this->captcha = new Captcha();
		$this->scrap = new HtmlScrap();
		
		Display::Ban(title, versi);
		
		Display::Ban(title, versi);
        $r = $this->Dashboard();
        if(!$r['Username']) {
            Functions::removeConfig("cookie");
            print Display::Error("Cookie Expired\n");
            goto cookie;
        }
        Display::Cetak("Username",$r['Username']);
        Display::Cetak("Balance",$r['Balance']);
        Display::Line();

        $status = 0;
        while(true){
            if($this->Claim()){
                Functions::removeConfig("cookie");
                goto cookie;
            }
            $status = $this->Extensions($status);
            Functions::Tmr(30);
        }
	}
	private function getExt(){
        $data = "extension=1&version=124&get=submit";
        return json_decode(Requests::Curl(host."extn/get/", $this->headers(), 1, $data)[1],1);
    }
    private function ExtPopup($hash){
        $data = "hash=".$hash."&popup=submit";
        return json_decode(Requests::Curl(host."extn/popup/", $this->headers(), 1,$data)[1],1);
    }
    private function ClaimPopup($hash){
        $data = "hash=".$hash;
        return json_decode(Requests::Curl(host."extn/popup-check/", $this->headers(), 1, $data)[1],1);
    }
    private function ExtTeas($hash){
        $data = "hash=".$hash;
        return json_decode(Requests::Curl(host."extn/status/", $this->headers(), 1, $data)[1],1);
    }
    private function Extensions($status=0){
        $r = $this->getExt();
        if(isset($r['popup'])){
            $status = "Ext Popup";
            $timer = $r['time_out']/1000;
            $hash = explode('/?tzpha=',$r['url'])[1];
            $r = $this->ExtPopup($hash);
            Functions::Tmr($timer);
            $hash = $r['hash'];
            $r = $this->ClaimPopup($hash);
        }elseif(isset($r['hash'])){
            $status = "Ext Ads";
            $timer = $r['timer'];
            Functions::Tmr($timer);
            $hash = $r['hash'];
            $r = $this->ExtTeas($hash);
		}elseif(isset($r['captcha'])){
			print Display::Error("Captcha: https://teaserfast.ru/check-captcha\n");
			//Display::Line();
        }else{
            if(!$status){
                Display::Cetak("Status","Waiting..");
            }
            Functions::Tmr(30);
            if(!$status){
                Display::Line();
                return 1;
            }
        }

        if($r['success']){
            Display::Cetak($status,"earn ".$r['earn']);
            $r = Requests::Curl(host,$this->headers())[1];
            $bal= explode('</span>',explode('">',explode('<span class="int blue" id="basic_balance" title="',$r)[1])[1])[0];
            Display::Cetak("Balance",$bal);
            Display::Line();
            return;
        }
    }
    private function headers($xml=0){
        $h[] = "Host: ".parse_url(host)['host'];
        $h[] = "cookie: ".$this->cookie;
        if($xml){
            $h[] = "X-Requested-With: XMLHttpRequest";
        }
        $h[] = "user-agent: ".$this->uagent;
        return $h;
    }																			

    public function Dashboard(){
        $r = Requests::Curl(host,$this->headers())[1];
        $user = explode('</div>',explode('<div class="main_user_login">',$r)[1])[0];
        $bal= explode('</span>',explode('">',explode('<span class="int blue" id="basic_balance" title="',$r)[1])[1])[0];
        return ["Username"=>$user, "Balance"=>$bal];
    }

    private function Claim(){
        $r = Requests::Curl(host.'task/',$this->headers())[1];
		
		$ids = explode('<div class="it_task task_youtube">',$r);
		if(isset($ids[1])){
			$ids = explode('<a href="/task/',$ids[1]);
		}else{
			
			return;
		}
        foreach($ids as $a => $idc){
            if($a == 0)continue;
            $id = explode('">',$idc)[0];
            $r = Requests::Curl(host.'task/'.$id,$this->headers())[1];
            if(preg_match('/Задание не найдено или в данный момент недоступно./',$r)){
                continue;
            }
            $code = explode("'",explode("data: {dt: '",$r)[1])[0];
            $hd = explode("'",explode("hd: '",$r)[1])[0];
            $rc = explode("'",explode(" rc: '",$r)[1])[0];
            $tmr = explode(';',explode('var timercount = ',$r)[1])[0];
            Functions::Tmr($tmr);

            $data = "dt=".$code;
            $r = json_decode(Requests::Curl(host.'captcha-start/',$this->headers(1), 1, $data)[1],1);
            if(!isset($r['success']))break;
            while(true){
                $data = "yd=$id&hd=$hd&rc=$rc";
                $r = json_decode(Requests::Curl(host.'captcha-youtube/',$this->headers(1), 1, $data)[1],1);
                if(!isset($r['success']))break;
                if($r['сaptcha'] && $r['small']){
                    $cap = $this->captcha->Teaserfast($r['сaptcha'], $r['small']);
                    $x = explode(',',explode('=',$cap)[1])[0];
                    $y = explode('=',$cap)[2];
                    $cap = "$x:$y";
                }else{
                    continue;
                }

                $data = "crxy=".$cap."&dt=".$code;
                $r = json_decode(Requests::Curl(host.'check-youtube/',$this->headers(1), 1, $data)[1],1);
                if(isset($r['captcha'])){
                    print Display::Error("Oops");
                    sleep(3);
                    print "\r                 \r";
                }else{
                    $desc = $r['desc'];
                    if($desc == "Время на прохождение каптчи истекло."){
                        break;
                    }
                    //print_r(explode(' ',explode('Поздравляем! Вы заработали', $desc)[1])[0]);
                    Display::Cetak("Youtube",$desc);
                    $r = Requests::Curl(host,$this->headers())[1];
                    $bal= explode('</span>',explode('">',explode('<span class="int blue" id="basic_balance" title="',$r)[1])[1])[0];
                    Display::Cetak("Balance",$bal);
                    Display::Line();
                    break;
                }
            }
        }
    }
}

new Bot();