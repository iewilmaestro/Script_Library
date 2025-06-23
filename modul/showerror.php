<?php

class ShowError {

    private $logFile;
	
    public function __construct(string $logFile = 'data/error.log') {
        $this->logFile = $logFile;
    }

    public function _start() {
		if(file_exists($this->logFile)){
			unlink($this->logFile);
		}
		Display::info("all errors are displayed and logged in data/error.log");
        Display::line();
		sleep(5);
		// Matikan error reporting dan display
		ini_set('display_errors', '0');

		// Pasang error handler custom untuk simpan log saja (tidak ditampilkan)
		set_error_handler([$this, 'handleError']);
		register_shutdown_function([$this, 'handleShutdown']);
    }

    // Fungsi untuk handle error saat runtime
    public function handleError($errno, $errstr, $errfile, $errline) {
		$errfile = basename($errfile);
        $msg = "$errstr | $errfile | $errline";
        $this->log($msg);

        // Jika error serius, hentikan script
        if (in_array($errno, [E_ERROR, E_USER_ERROR])) {
			exit;
        }

        // Jangan lanjutkan ke error handler PHP default
        return true;
    }

    // Handle fatal error saat shutdown
    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null) {
			$message = explode(' in ', $error['message'])[0];
			$errfile = basename($error['file']);
            $msg = "$message | $errfile | {$error['line']}";
			$this->log($msg);
            if (ini_get('display_errors')) {
                echo "\nFatal error: " . $msg . "\n";
            }
        }
    }

    // Simpan log ke file
    private function log(string $message) {
        $date = date('Y-m-d H:i:s');
		print Display::debug($message);
        file_put_contents($this->logFile, "[$date] $message\n", FILE_APPEND);
    }
}

?>