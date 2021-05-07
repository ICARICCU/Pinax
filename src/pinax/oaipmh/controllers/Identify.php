<?php
class pinax_oaipmh_controllers_Identify extends pinax_rest_core_CommandRest
{
    use \pinax_oaipmh_core_XmlOutputTrait;

    /**
     * @return string
     */
    function execute()
    {
        $output =   '<repositoryName>'.$this->encodeXmlText(__Config::get('oaipmh.title')).'</repositoryName>'.
                    '<baseURL>'.$this->encodeXmlText(pinax_Routing::scriptUrl(true)).'</baseURL>'.
                    '<protocolVersion>'.$this->encodeXmlText(__Config::get('oaipmh.protocolVersion')).'</protocolVersion>'.
                    '<adminEmail>'.$this->encodeXmlText(__Config::get('oaipmh.adminEmail')).'</adminEmail>'.
                    '<earliestDatestamp>'.$this->encodeXmlText(__Config::get('oaipmh.earliestDatestamp')).'</earliestDatestamp>'.
                    '<deletedRecord>no</deletedRecord>'.
                    '<granularity>'.$this->encodeXmlText(__Config::get('oaipmh.granularity')).'</granularity>';
        return $output;
    }
}
