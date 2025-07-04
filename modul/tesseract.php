<?php

class Tesseract {
	
	private $frame, $cleaned, $outputFile;
	
	public function __construct($title)
	{
		$this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
		$this->img = "data/$title/img.gif";
		$this->frame = "data/$title/frame.png";
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
	public function Feytop($base64Image, $threshold = 50)
	{
		$start = microtime(true);

		// Simpan gambar sementara
		file_put_contents($this->img, base64_decode($base64Image));

		// Deteksi OS dan pilih command
		
		$magickCmd = $this->isWindows ? 'magick' : 'convert';

		$text = [];

		for ($i = 0; $i < 5; $i++) {
			$imgPath = escapeshellarg($this->img."[$i]");
			$framePath = escapeshellarg($this->frame);
			$cleanedPath = escapeshellarg($this->cleaned);
			$outputPath = escapeshellarg($this->outputFile);

			// Ekstrak frame
			$this->silent_exec("$magickCmd $imgPath -resize 400% -colorspace Gray $framePath");
			
			// Bersihkan noise dan threshold
			$this->silent_exec("$magickCmd $framePath -morphology Close Octagon -blur 1x1 -threshold $threshold% $cleanedPath");

			// OCR
			$this->silent_exec("tesseract $cleanedPath $outputPath -l eng --psm 7");
			
			// Ambil hasil
			$text[] = file_get_contents($this->outputFile . '.txt');
			
			// Hapus file temporary
			$this->hapus();
		}
		unlink($this->img);
		
		$hasil = $this->levenshtein($text);
		$duration = microtime(true) - $start;

		Display::Cetak("Solved",round($duration * 1000, 2) . " ms");
		return htmlspecialchars(trim($hasil));
	}
	private function silent_exec($cmd) {
		$null = $this->isWindows ? '2>NUL' : '2>/dev/null';
		shell_exec("$cmd $null");
	}
	private function levenshtein(array $data)
	{
		// 1. Filter hanya item yang terdiri dari 4 digit angka
		$filtered = array_filter($data, function ($item) {
			return preg_match('/^\d{4}$/', $item);
		});

		// 2. Hitung frekuensi kemunculan
		$counts = array_count_values($filtered);

		// 3. Urutkan berdasarkan frekuensi tertinggi
		arsort($counts);

		// 4. Ambil nilai yang paling sering muncul
		$mostFrequent = array_key_first($counts);

		return $mostFrequent;
	}
	private function hapus()
	{
		unlink($this->frame);
		unlink($this->cleaned);
		unlink($this->outputFile . '.txt');
	}
}