<?php

class Cloudflare {
	function __construct(){
		$this->python = " aW1wb3J0IG9zLCBzeXMsIHRpbWUsIGpzb24KZnJvbSBzZWxlZHJvaWQgaW1wb3J0IHdlYmRyaXZlcgpmcm9tIHNlbGVkcm9pZC53ZWJkcml2ZXIuY29tbW9uLmJ5IGltcG9ydCBCeQoKZHJpdmVyID0gd2ViZHJpdmVyLkNocm9tZShndWk9RmFsc2UpCmhvc3QgPSBzeXMuYXJndlsxXQoKZGVmIENsb3VkZmxhcmUoKToKCXRpdGxlID0gZHJpdmVyLnRpdGxlCglpZiBhbnkoc3ViLmxvd2VyKCkgaW4gdGl0bGUubG93ZXIoKSBmb3Igc3ViIGluIFsiY2xvdWRmbGFyZSIsImp1c3QgYSBtb21lbnQuLi4iXSk6CgkJdGltZS5zbGVlcCgxMCkKCQlyZXR1cm4gRmFsc2UKCWVsc2U6CgkJcmV0dXJuIFRydWUKCnRyeToKCWRyaXZlci5nZXQoaG9zdCkKCXdoaWxlIG5vdCBDbG91ZGZsYXJlKCk6CgkJdGltZS5zbGVlcCgzKQoJCgljZl9jbGVhcmFuY2UgPSBkcml2ZXIuZ2V0X2Nvb2tpZSgiY2ZfY2xlYXJhbmNlIikKCXVzZXJfYWdlbnQgPSBkcml2ZXIudXNlcl9hZ2VudApleGNlcHQgRXhjZXB0aW9uIGFzIGU6CglwcmludChmIntlfSIpCmZpbmFsbHk6Cgl0aXRsZSA9IGRyaXZlci50aXRsZQoJaWYgYW55KHN1Yi5sb3dlcigpIGluIHRpdGxlLmxvd2VyKCkgZm9yIHN1YiBpbiBbImNsb3VkZmxhcmUiLCJqdXN0IGEgbW9tZW50Li4uIl0pOgoJCWRhdGEgPSB7CgkJImNmX2NsZWFyYW5jZSIgOiBGYWxzZSwKCQkidXNlci1hZ2VudCIgOiB1c2VyX2FnZW50CgkJfQoJZWxzZToKCQlkYXRhID0gewoJCSJjZl9jbGVhcmFuY2UiIDogY2ZfY2xlYXJhbmNlLnNwbGl0KCI9IilbMV0sCgkJInVzZXItYWdlbnQiIDogdXNlcl9hZ2VudAoJCX0KCXdpdGggb3BlbignY2YuanNvbicsICd3JykgYXMgZmlsZToKCQlqc29uLmR1bXAoZGF0YSwgZmlsZSwgaW5kZW50PTQpCglkcml2ZXIuY2xvc2UoKQo=";
		$this->JsonFile = "config.json";
		$this->pythonFile = "cf.py";
		$this->bypassFile = "cf.json";
	}
	private function getOriConfig(){
		$config = json_decode(file_get_contents($this->JsonFile), 1);
		$cookie = $config['cookie'];
		$user_agent = $config['user_agent'];
		return [$cookie, $user_agent];
	}
	public function BypassCf($host){
		$file = file_put_contents($this->pythonFile,base64_decode($this->python));
		sleep(2);
		system("python {$this->pythonFile} ".$host);
		sleep(2);
		unlink($this->pythonFile);
		return $this->editConfig();
	}
	private function editConfig(){
		$getOriConfig = $this->getOriConfig();
		$new_data = json_decode(file_get_contents($this->bypassFile),1);
		$new_cf_clearance = $new_data["cf_clearance"];
		unlink($this->bypassFile);
		$cf_clearance_ori = explode(";",explode("cf_clearance=", $getOriConfig[0])[1])[0];
		$data["cookie"] = str_replace($cf_clearance_ori, $new_cf_clearance, $getOriConfig[0]);
		$data["user-agent"] = $new_data["user-agent"];
		return $data;
	}
}
?>