<?php
/**
 * Logger - A singleton class for application-wide logging
 * Provides daily log rotation and supports multiple log levels
 */
class Logger
{
    // Singleton instance
    private static ?Logger $instance = null;

    // Log levels
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';

    // Log directory
    private string $logDir;
    
    // Current log file path
    private string $currentLogFile;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct()
    {
        $this->logDir = __DIR__ . '/../logs';
        $this->ensureLogDirectoryExists();
        $this->currentLogFile = $this->getLogFilePath();
    }

    /**
     * Get the singleton instance
     * 
     * @return Logger The singleton instance
     */
    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log a debug message
     * 
     * @param string $message The message to log
     * @return void
     */
    public function debug(string $message): void
    {
        $this->log(self::DEBUG, $message);
    }

    /**
     * Log an info message
     * 
     * @param string $message The message to log
     * @return void
     */
    public function info(string $message): void
    {
        $this->log(self::INFO, $message);
    }

    /**
     * Log a warning message
     * 
     * @param string $message The message to log
     * @return void
     */
    public function warning(string $message): void
    {
        $this->log(self::WARNING, $message);
    }

    /**
     * Log an error message
     * 
     * @param string $message The message to log
     * @return void
     */
    public function error(string $message): void
    {
        $this->log(self::ERROR, $message);
    }

    /**
     * Write a log entry with the specified level
     * 
     * @param string $level The log level
     * @param string $message The message to log
     * @return void
     */
    private function log(string $level, string $message): void
    {
        // Check if current date is different than the log file date
        $currentLogFile = $this->getLogFilePath();
        if ($this->currentLogFile !== $currentLogFile) {
            $this->currentLogFile = $currentLogFile;
        }

        // Format the log message
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp][$level] $message" . PHP_EOL;
        
        // Write to log file
        $this->writeToLogFile($formattedMessage);
    }

    /**
     * Write message to log file in a thread-safe way
     * 
     * @param string $message The formatted message to write
     * @return void
     */
    private function writeToLogFile(string $message): void
    {
        try {
            // Open file with exclusive lock for writing
            $file = fopen($this->currentLogFile, 'a');
            if ($file) {
                // Acquire an exclusive lock
                if (flock($file, LOCK_EX)) {
                    fwrite($file, $message);
                    // Release the lock
                    flock($file, LOCK_UN);
                }
                fclose($file);
            }
        } catch (Exception $e) {
            // If we can't log, there's not much we can do
            error_log("Failed to write to log file: " . $e->getMessage());
        }
    }

    /**
     * Ensure the log directory exists, create it if it doesn't
     * 
     * @return void
     */
    private function ensureLogDirectoryExists(): void
    {
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * Get the current log file path based on the current date
     * 
     * @return string The log file path
     */
    private function getLogFilePath(): string
    {
        $date = date('Y-m-d');
        return $this->logDir . "/$date.log";
    }
}
