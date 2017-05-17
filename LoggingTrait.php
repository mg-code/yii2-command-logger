<?php

namespace mgcode\commandLogger;

use mgcode\helpers\TimeHelper;

/**
 * Class LoggingTrait
 * Usually this trait is used for CLI commands.
 * You can simply disable message output by calling in class: $this->msgLoggingEnabled = false
 * @property bool $msgLoggingEnabled
 * @property string $loggingCategory the category of logging messages.
 */
trait LoggingTrait
{
    /** @var bool Whether to output logging messages */
    protected $_msgLoggingEnabled = true;

    /** @var string */
    protected $_loggingCategory = 'application';

    public function setLoggingCategory($value)
    {
        $this->_loggingCategory = $value;
    }

    /**
     * @return bool
     */
    public function getLoggingCategory()
    {
        return $this->_loggingCategory;
    }

    /**
     * @return bool
     */
    public function getMsgLoggingEnabled()
    {
        return $this->_msgLoggingEnabled;
    }

    /**
     * You can simply disable message output by calling in class: $this->msgLoggingEnabled = false
     * @param bool $value
     */
    public function setMsgLoggingEnabled($value)
    {
        $this->_msgLoggingEnabled = (bool) $value;
    }

    /**
     * Send message to client
     * @param string $message
     * @param array $params
     */
    public function msg($message, $params = [])
    {
        if (!$this->getMsgLoggingEnabled()) {
            return;
        }

        $message = $this->_buildMessage($message, $params);
        $memory = round(memory_get_usage() / 1024 / 1024, 1);;
        echo '['.TimeHelper::getTime().']'.' ['.$memory.'MB] '.$message."\r\n";
    }

    /**
     * Sleep for some time
     * @param $seconds
     * @param null|int $secondsTo If set, will be used random between numbers.
     */
    public function sleep($seconds, $secondsTo = null)
    {
        if ($secondsTo) {
            $seconds = mt_rand($seconds, $secondsTo);
        }
        $this->msg('Sleep for {s} seconds', ['s' => $seconds]);
        sleep($seconds);
    }

    /**
     * Logs and outputs error
     * Usually needed for CLI commands, when execution should continue after some error.
     * @param string $msg
     * @param array $params
     */
    public function logError($msg, $params = [])
    {
        $msg = get_class($this).' Error: '.$this->_buildMessage($msg, $params);
        \Yii::error($msg, $this->_loggingCategory);
        $this->msg($msg);
    }

    /**
     * Logs and outputs exception
     * Usually needed for CLI commands, when execution should continue after some error.
     * @param \Exception $exception
     * @param boolean $includeTrace
     */
    public function logException($exception, $includeTrace = true)
    {
        $msg = get_class($this).' exception: '.$this->getMsgFromException($exception, $includeTrace);
        \Yii::error($msg, $this->_loggingCategory);
        $this->msg($msg);
    }

    /**
     * Generates error message from exception.
     * @param \Exception $exception
     * @param bool $includeTrace
     * @return string
     */
    public function getMsgFromException(\Exception $exception, $includeTrace = false)
    {
        $msg = $exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().')';
        if ($includeTrace) {
            $msg .= PHP_EOL.$exception->getTraceAsString();
        }
        return $msg;
    }

    /**
     * Returns memory usage string.
     * @return string
     */
    public function getMemoryUsageMsg()
    {
        $peakUsage = round(memory_get_peak_usage() / 1024 / 1024, 1);
        $memoryUsage = round(memory_get_usage() / 1024 / 1024, 1);
        return "Memory usage: {$memoryUsage}MB (peak: {$peakUsage}MB) ";
    }

    /**
     * Internal function that builds message.
     * @param $message
     * @param array $params
     * @return string
     */
    private function _buildMessage($message, $params = [])
    {
        $p = [];
        foreach ((array) $params as $name => $value) {
            $p['{'.$name.'}'] = $value;
        }

        $message = ($p === []) ? $message : strtr($message, $p);
        return $message;
    }
}
