<?php

namespace mgcode\commandLogger;

use mgcode\helpers\TimeHelper;

/**
 * Class MsgTrait
 * Usually this trait is used for CLI commands.
 * You can simply disable message output by calling in class: $this->msgLoggingEnabled = false
 * @property bool $msgLoggingEnabled
 */
trait LoggingTrait
{
    /** @var bool Whether to output logging messages */
    protected $_msgLoggingEnabled = true;

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
    public function msg($message, $params = array())
    {
        if (!$this->getMsgLoggingEnabled()) {
            return;
        }

        $p = [];
        foreach ((array) $params as $name => $value) {
            $p['{'.$name.'}'] = $value;
        }

        $message = ($p === []) ? $message : strtr($message, $p);
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
     * @param $msg
     */
    public function logError($msg)
    {
        $this->msg($msg);
        $msg = get_class($this).' Error: '.$msg;
        \Yii::error($msg, 'app\modules\crawler');
    }

    /**
     * Logs and outputs exception
     * Usually needed for CLI commands, when execution should continue after some error.
     * @param \Exception $exception
     */
    public function logException($exception)
    {
        $this->msg($exception->getMessage());
        $msg = get_class($this).' exception: '.$exception->getMessage()." ".PHP_EOL.$exception->getTraceAsString();
        \Yii::error($msg, 'app\modules\crawler');
    }
}
