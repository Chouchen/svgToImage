<?
class Log {

	protected $logfile;
	
    function __construct($filename) {
		$file 			= $filename;
		$this->logfile 	= fopen($file, 'a+');
		$this->message('Starting log');
    }

    function message($message) {
		$message 		= '['. date("Y-m-d / H:i:s") . '] @MESSAGE'.' - '.$message;
        $message 		.= "\n";
        return fwrite( $this->logfile, $message );
    }
	
	function error($message) {
		$message 		= '['. date("Y-m-d / H:i:s") . '] @ERROR'.' - '.$message;
        $message 		.= "\n";
        return fwrite( $this->logfile, $message );
    }
	
	function __destruct(){
		$this->message("Finishing log\n-----------------------");
		return fclose( $this->logfile );
	}
}

