<?php

class HtmlScrap {
	protected $captcha;
	protected $input;
	protected $limit;
	protected $option;
	
    function __construct() {
        $this->captcha = '/class=["\']([^"\']+)["\'][^>]*data-sitekey=["\']([^"\']+)["\'][^>]*>/i';
        $this->input = '/<input[^>]*name=["\'](.*?)["\'][^>]*value=["\'](.*?)["\'][^>]*>/i';
        $this->limit = '/(\d{1,})\/(\d{1,})/';
		$this->option = '/<option\s+value=["\']([^"\']+)["\'][^>]*>/i';
    }

    private function scrap($pattern, $html) {
        preg_match_all($pattern, $html, $matches);
        return $matches;
    }

    private function getCaptcha($html) {
        $data = []; // Inisialisasi untuk menghindari undefined
        $scrap = $this->scrap($this->captcha, $html);
        for ($i = 0; $i < count($scrap[1]); $i++) {
            $data[$scrap[1][$i]] = $scrap[2][$i];
        }
        return $data;
    }
	
	private function getOptionValues($html) {
		$data = [];
		$scrap = $this->scrap($this->option, $html);
		foreach ($scrap[1] as $val) {
			$data[] = $val;
		}
		return $data;
	}

    private function getInput($html, $form = 1) {
        $data = [];
        $forms = explode('<form', $html);
        if (!isset($forms[$form])) {
            return $data; // form tidak ditemukan
        }
        $formContent = $forms[$form];
        $scrap = $this->scrap($this->input, $formContent);
        for ($i = 0; $i < count($scrap[1]); $i++) {
            $data[$scrap[1][$i]] = $scrap[2][$i];
        }
        return $data;
    }

    public function Result($html, $form = 1) {
        $data = []; // ← inisialisasi untuk menghindari warning

        // Title
        if (str_contains($html, '<title>')) {
            $titlePart = explode('<title>', $html);
            if (isset($titlePart[1])) {
                $data['title'] = explode('</title>', $titlePart[1])[0] ?? '';
            }
        } else {
            $data['title'] = '';
        }

        $data['cloudflare'] = str_contains($html, 'Just a moment...');
        $data['firewall']   = str_contains($html, 'Firewall');
        $data['locked']     = str_contains($html, 'Locked');
        $data['captcha']    = $this->getCaptcha($html);
		$data['options']	= $this->getOptionValues($html);
		
        // Input
        $input = $this->getInput($html, $form);
        $data['input'] = !empty($input) ? $input : $this->getInput($html, 2);
        $data['faucet'] = $this->scrap($this->limit, $html);

        // Success Message
        $data["response"] = [
            "success" => false,
            "warning" => null,
            "unset"   => false,
            "exit"    => false
        ];

        if (str_contains($html, "icon: 'success',")) {
            $suksesPart = explode("icon: 'success',", $html);
            if (isset($suksesPart[1])) {
                $successHtml = explode("html: '", $suksesPart[1])[1] ?? null;
                if ($successHtml) {
                    $data["response"]["success"] = strip_tags(explode("'", $successHtml)[0] ?? '');
                }
            }
        } else {
            $warning = null;

            if (str_contains($html, "html: '")) {
                $warningPart = explode("html: '", $html);
                $warning = explode("'", $warningPart[1])[0] ?? null;
            }

            $data["response"]["warning"] = "Not Found"; // default
            $data["response"]["exit"] = false;
            $data["response"]["unset"] = false;

            if (str_contains($html, 'Your account')) {
                $ban = explode('</div>', explode('<div class="alert text-center alert-danger"><i class="fas fa-exclamation-circle"></i> Your account', $html)[1] ?? '')[0] ?? false;
                if ($ban) {
                    $data["response"]["warning"] = $ban;
                    $data["response"]["exit"] = true;
                }
            } elseif (str_contains($html, 'invalid amount')) {
                $data["response"]["warning"] = "You are sending an invalid amount";
                $data["response"]["unset"] = true;
			} elseif (str_contains($html, 'Invalid API Key used')) {
                $data["response"]["warning"] = "Invalid API Key used";
                $data["response"]["unset"] = true;
            } elseif (str_contains($html, 'Shortlink in order to claim from the faucet!') || 
				str_contains($html, 'Shortlink must be completed')) {
                $data["response"]["warning"] = $warning ?? "Shortlink required";
                $data["response"]["exit"] = true;
            } elseif (str_contains($html, 'sufficient funds')) {
                $data["response"]["warning"] = "Sufficient funds";
                $data["response"]["unset"] = true;
            } elseif (str_contains($html, 'Daily claim limit')) {
                $data["response"]["warning"] = "Daily claim limit";
                $data["response"]["unset"] = true;
            } elseif ($warning) {
                $data["response"]["warning"] = $warning;
            }
        }

        return $data;
    }
}
?>