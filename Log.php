<?php

class Log
{
    /** @var false|resource */
    protected $logfile;

    /**
     * Log constructor.
     * @param $filename
     */
    public function __construct($filename)
    {
        $file = $filename;
        $this->logfile = fopen($file, 'ab+');
        $this->message('Starting log');
    }

    /**
     * @param $message
     * @return false|int
     */
    public function message($message)
    {
        $message = '[' . date('Y-m-d / H:i:s') . '] @MESSAGE' . ' - ' . $message;
        $message .= "\n";
        return fwrite($this->logfile, $message);
    }

    /**
     * @param $message
     * @return false|int
     */
    public function error($message)
    {
        $message = '[' . date('Y-m-d / H:i:s') . '] @ERROR' . ' - ' . $message;
        $message .= "\n";
        return fwrite($this->logfile, $message);
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->message("Finishing log\n-----------------------");
        fclose($this->logfile);
    }

    /**
     * @param mixed $message
     * @return string
     */
    public static function decode($message)
    {
        if (is_string($message)) {
            return $message;
        }

        if (is_array($message)) {
            return implode('|', $message);
        }

        if ($message instanceof SimpleXMLElement) {
            return (string) $message;
        }

        return '';
    }
}

