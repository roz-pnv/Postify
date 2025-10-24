<?php

namespace App\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    private string $logFile;

    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
    }

    public function log($level, $message, array $context = []): void
    {
        $date = date('Y-m-d H:i:s');
        $interpolated = $this->interpolate($message, $context);
        $formatted = "[$date] $level: $interpolated" . PHP_EOL;

        file_put_contents($this->logFile, $formatted, FILE_APPEND);
    }

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    private function interpolate(string $message, array $context): string
    {
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $message = str_replace('{' . $key . '}', (string) $value, $message);
        }

        return $message;
    }
}
