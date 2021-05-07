<?php

class pinax_log_helpers_Builder extends PinaxObject
{
    public function __construct()
    {
        $groups = explode(',', __Config::get('app.log'));
        array_walk($groups, function($item) {
            $this->buildLogger($item);
        });

        $this->addEventListener(PNX_EVT_DUMP_404, $this);
        $this->addEventListener(PNX_EVT_DUMP_EXCEPTION, $this);
    }

    /**
     * @param $event
     */
    public function onDump404($event)
    {
        $requestUrl = $_SERVER['REQUEST_URI'];
        $message = [
                'message' => 'Page Not Found: '.$requestUrl,
                'errorNumber' => 404,
                'request' => $_GET + $_POST,
                'url' => $requestUrl
            ];

        $event = [  'type' => PNX_LOG_EVENT,
                    'data' => [
                        'level' => PNX_LOG_ERROR,
                        'group' => '404',
                        'message' => $message
                    ]];

        $this->dispatchEvent($event);
    }

    /**
     * @param $event
     */
    public function onDumpException($event)
    {
        $message = [
                'message' => $event->data['message']['description'],
                'errorNumber' => $event->data['message']['code'],
                'file' => $event->data['message']['file'] . ' : ' . $event->data['message']['line'],
                'request' => $_GET + $_POST,
                'url' => $_SERVER['REQUEST_URI']
            ];

        $event = [  'type' => PNX_LOG_EVENT,
                    'data' => [
                        'level' => PNX_LOG_ERROR,
                        'group' => '500',
                        'message' => $message
                    ]];

        $this->dispatchEvent($event);
    }

    /**
     * @param string $name
     * @return void
     */
    private function buildLogger(string $name): void
    {
        $applicationName = __Config::get('app.log.application.name');
        $level = __Config::get('app.log.level.'.$name);
        $type = __Config::get('app.log.type.'.$name);

        switch ($type) {
            case 'file':
                $applicationLog = __Config::get('app.log.application.path');
                $path = sprintf('%s/%s_%s.log', $applicationLog, $name, date('Ymd'));
                pinax_log_LogFactory::create('File', $path, [], $level, $name);
                break;
            case 'syslog':
                pinax_log_LogFactory::create('Syslog', $name, [
                    'tag' => $applicationName,
                    'useJson' => true,
                    'addLogInfo' => true
                ], $level, $name);
                break;
            case 'db':
                pinax_log_LogFactory::create('DB', [], $level, $name);
                break;
            case '':
            case 'null':
                break;
            default:
                throw new Exception(sprintf('Log format not supported: %s', $type));
                break;
        }
    }
}
