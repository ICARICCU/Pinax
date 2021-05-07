<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_speakingUrl_Manager extends PinaxObject
{
    private static $modules = array();

    public function __construct()
    {
        $application = pinax_ObjectValues::get('org.pinax', 'application');
        if (__Config::get('pinaxcms.speakingUrl') && !$application->isAdmin()) {
            $this->addEventListener(PNX_EVT_START_COMPILE_ROUTING, $this);
        }
    }


    public static function registerResolver($resolver)
    {
        self::$modules[$resolver->getType()] = $resolver;
    }

    public function getResolver($type)
    {
        return isset(self::$modules[$type]) ? self::$modules[$type] : null;
    }

    public function getResolvers()
    {
        return self::$modules;
    }


    public function startCompileRouting()
    {
        $this->compileRouting();
    }


    private function compileRouting()
    {
        $routing = '';

        $it = pinax_ObjectFactory::createModelIterator('org.pinaxcms.speakingUrl.models.SpeakingUrl')
             ->load('all');
        foreach($it as $ar) {
            if (isset(self::$modules[$ar->speakingurl_type])) {
                $routing .= self::$modules[$ar->speakingurl_type]->compileRouting($ar);
            }
        }

        $routing = '<?xml version="1.0" encoding="utf-8"?><pnx:Routing>'.$routing.'</pnx:Routing>';
        $evt = array('type' => PNX_EVT_LISTENER_COMPILE_ROUTING, 'data' => $routing);
        $this->dispatchEvent($evt);
    }

    /**
     * @param string $term
     * @param string $id
     * @param string $protocol
     * @param $filter
     * @return array
     */
    public function searchDocumentsByTerm($term, $id, $protocol='', $filter=[])
    {
        $result = [];

        foreach (self::$modules as $module) {
            $partialResult = $module->searchDocumentsByTerm($term, $id, $protocol, $filter);
            $result = array_merge($result, $partialResult);
        }

        return $result;
    }

    // TODO sostituire i due metodo con resolve
    public function makeUrl($id)
    {
        foreach (self::$modules as $module) {
            $url = $module->makeUrl($id);

            if ($url!==false) {
                return $url;
            }
        }

        return false;
    }

    public function makeLink($id)
    {
        foreach (self::$modules as $module) {
            $url = $module->makeLink($id);

            if ($url!==false) {
                return $url;
            }
        }

        return false;
    }

    public function resolve($id)
    {
        foreach (self::$modules as $module) {
            if (method_exists($module, 'resolve')) {
                $resolveVO = $module->resolve($id);
                if ($resolveVO!==false) {
                    return $resolveVO;
                }
            }
        }

        return false;
    }

    public function onRegister()
    {
    }
}
