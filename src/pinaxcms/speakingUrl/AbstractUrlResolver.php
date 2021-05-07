<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_speakingUrl_AbstractUrlResolver
{
    protected $application;
    protected $languageId;
    protected $editLanguageId;
    protected $type;
    protected $protocol;

    public function __construct()
    {
        $this->application = pinax_ObjectValues::get('org.pinax', 'application');
        $this->languageId = $this->application->getLanguageId();
        $this->editLanguageId = pinax_ObjectValues::get('org.pinax', 'editingLanguageId');
    }


    public function getType()
    {
        return $this->type;
    }

    public function checkProtocol($id)
    {
        $info = $this->extractProtocolAndId($id);
        return $this->protocol == $info->protocol;
    }

    protected function getIdFromLink($id)
    {
        return str_replace($this->protocol, '', $id);
    }

    protected function extractProtocolAndId($id)
    {
        list($protocol, $id, $queryString) = explode(':', $id);
        $queryString = urldecode($queryString);
        $result = new StdClass;
        $result->protocol = $protocol;
        $result->id = $id;
        $result->queryString = $queryString;
        return $result;
    }

}
