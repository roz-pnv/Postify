<?php
namespace App\core;

class Logger
{
    private string $logFile;

    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
    }

    public function log(string $level, string $message): void
    {
        $date = date('Y-m-d H:i:s');
        $formatted = "[{$date}] {$level}: {$message}" . PHP_EOL;

        if (getenv('APP_DEBUG') === 'true') {
            echo nl2br($formatted);
        }

        file_put_contents($this->logFile, $formatted, FILE_APPEND);
    }

    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }

    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }

    public function warning(string $message): void
    {
        $this->log('WARNING', $message);
    }
}
