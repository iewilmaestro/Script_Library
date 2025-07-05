<?php

class Tesseract {
	
	private $isWindows, $imgGif , $img, $frame, $out1, $out2, $cleaned, $outputFile;
	
	public function __construct($title)
	{
		$this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
		$this->imgGif = "data/$title/img.gif";
		$this->img = "data/$title/img.png";
		$this->frame = "data/$title/frame.png";
		$this->out1 = "data/$title/out1.png";
		$this->out2 = "data/$title/out2.png";
		$this->cleaned = "data/$title/cleaned.png";
		$this->outputFile = "data/$title/hasil";
		$this->checkModul();
	}
	
	private function isModulAvailable($command) {
		$null = $this->isWindows ? '2>NUL' : '2>/dev/null';
		$check = shell_exec("$command $null");
		return !empty($check);
	}

	private function checkModul() {
		
		if (!$this->isModulAvailable("tesseract -v")) {
			Display::Error("Tesseract not installed!\n");
			if ($this->isWindows) {
				Display::Info("download & install:");
				Display::Info("https://github.com/UB-Mannheim/tesseract/wiki");
			} else {
				Display::Info("command:");
				Display::Info("pkg install tesseract");
			}
			Display::line();
			exit;
		}
		$magickCmd = $this->isWindows ? 'magick' : 'convert';
		if ($magickCmd == 'magick' && !$this->isModulAvailable("magick -version")) {
			Display::Error("ImageMagick not installed!\n");
			Display::Info("download & install:");
			Display::Info("https://imagemagick.org/script/download.php#windows");
			Display::line();
			exit;
		}
		if ($magickCmd == 'convert' && !$this->isModulAvailable("convert -version")) {
			Display::Error("ImageMagick not installed!\n");
			Display::Info("command:");
			Display::Info("pkg install imagemagick");
			Display::line();
			exit;
		}
	}
	private function silent_exec($cmd)
	{
		$null = $this->isWindows ? '2>NUL' : '2>/dev/null';
		shell_exec("$cmd $null");
	}
	private function levenshtein(array $data)
	{
		$filtered = array_filter($data, function ($item) {
			return preg_match('/^\d{4}$/', $item);
		});
		$counts = array_count_values($filtered);
		arsort($counts);
		$mostFrequent = array_key_first($counts);
		return $mostFrequent;
	}
	public function Feytop($base64Image, $threshold = 50)
	{
		$start = microtime(true);
		file_put_contents($this->imgGif, base64_decode($base64Image));
		$magickCmd = $this->isWindows ? 'magick' : 'convert';
		$text = [];
		for ($i = 0; $i < 5; $i++) {
			$imgPath = escapeshellarg($this->imgGif."[$i]");
			$framePath = escapeshellarg($this->frame);
			$cleanedPath = escapeshellarg($this->cleaned);
			$outputPath = escapeshellarg($this->outputFile);
			$this->silent_exec("$magickCmd $imgPath -resize 400% -colorspace Gray $framePath");
			$this->silent_exec("$magickCmd $framePath -morphology Close Octagon -blur 1x1 -threshold $threshold% $cleanedPath");
			$this->silent_exec("tesseract $cleanedPath $outputPath -l eng --psm 7");
			$text[] = file_get_contents($this->outputFile . '.txt');
			unlink($this->frame);
			unlink($this->cleaned);
			unlink($this->outputFile . '.txt');
		}
		unlink($this->imgGif);
		$hasil = $this->levenshtein($text);
		$duration = microtime(true) - $start;
		Display::Cetak("Solved",round($duration * 1000, 2) . " ms");
		return htmlspecialchars(trim($hasil));
	}
	public function Firefaucet($base64Image)
	{
		$start = microtime(true);
		file_put_contents($this->img, base64_decode($base64Image));
		$magickCmd = $this->isWindows ? 'magick' : 'convert';
		$text = [];
		$imgPath = escapeshellarg($this->img);
		$framePath = escapeshellarg($this->frame);
		$out1 = escapeshellarg($this->out1);
		$out2 = escapeshellarg($this->out2);
		$cleanedPath = escapeshellarg($this->cleaned);
		$outputPath = escapeshellarg($this->outputFile);
		$this->silent_exec("magick $imgPath -colorspace Gray $framePath");
		$this->silent_exec("magick $out1 -contrast-stretch 10%x90% -threshold 50% $out2");
		$this->silent_exec("magick $framePath -morphology Close Octagon -blur 1x1 $cleanedPath");
		$this->silent_exec("tesseract $cleanedPath $outputPath -l eng --psm 7");
		$hasil = file_get_contents($this->outputFile.'.txt');
		unlink($this->frame);
		unlink($this->out1);
		unlink($this->out2);
		unlink($this->cleaned);
		unlink($this->outputFile . '.txt');
		unlink($this->img);
		$duration = microtime(true) - $start;
		Display::Cetak("Solved",round($duration * 1000, 2) . " ms");
		return htmlspecialchars(trim($hasil));
	}
}