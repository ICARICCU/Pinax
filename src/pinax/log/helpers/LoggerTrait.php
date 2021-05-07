<?php
trait pinax_log_helpers_LoggerTrait
{
    private $logDispatcher;
    private $logGroup;

    /**
     * @param string $message
     * @return void
     */
    public function logDebug(string $message): void
    {
        $this->sendLog($message, PNX_LOG_DEBUG);
    }

    /**
     * @param string $message
     * @return void
     */
    public function logOperation(string $message): void
    {
        $this->sendLog($message, PNX_LOG_SYSTEM);
    }

    /**
     * @param string $message
     * @return void
     */
    public function logAction(string $message): void
    {
        $this->sendLog($message, PNX_LOG_INFO);
    }

    /**
     * @param string $message
     * @return void
     */
    public function logUserError(string $message): void
    {
        $this->sendLog($message, PNX_LOG_WARNING);
    }

    /**
     * @param string $message
     * @return void
     */
    public function logSystemError(string $message): void
    {
        $this->sendLog($message, PNX_LOG_ERROR);
    }

    /**
     * @param \Exception $e
     * @return void
     */
    public function logException(\Exception $e): void
    {
        $trace = $e->getTrace();

        $message = [
            'message' => $e->getMessage(),
            'errorNumber' => $e->getCode(),
            'file' => $trace[0]['file'] . ' : ' . $trace[0]['line'],
            'request' => $_GET + $_POST,
            'url' => $_SERVER['REQUEST_URI']
        ];

        $this->sendLog($message, PNX_LOG_FATAL, '500');
    }

    /**
     * @param string|array $message
     * @param integer $level
     * @return void
     */
    private function sendLog($message, int $level, string $group=null): void
    {
        if (!$this->logDispatcher) {
            $this->logDispatcher = is_a($this, 'PinaxObject') ? $this : \pinax_ObjectValues::get('org.pinax', 'application');
            $this->logGroup = __Config::get('app.log.group');
        }

        $this->logDispatcher->dispatchEventByArray(
            PNX_LOG_EVENT,
            [
                'level' => $level,
                'group' => $group ? : $this->logGroup,
                'message' => $message
            ]);
    }
}
