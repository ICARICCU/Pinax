<?php
class pinax_oaipmh_controllers_GetRecord extends pinax_rest_core_CommandRest
{
    use \pinax_oaipmh_core_SetHelperTrait;
    use \pinax_oaipmh_core_ParamsTrait;
    use \pinax_oaipmh_core_XmlOutputTrait;

    /**
     * @return string
     */
    function execute()
    {
        $identifier = $this->getParam('identifier', true, pinax_oaipmh_core_ParamsType::TYPE_GENERIC);
        $metadataSets = $this->getParam('metadataPrefix', true, pinax_oaipmh_core_ParamsType::TYPE_METADATA_PREFIX);

        $adapter = $this->application->getAdapter();
        /** @var pinax_oaipmh_models_VO_IdentifierVO $identifierVO */
        $identifierVO = $adapter->parseIdentifier($identifier);
        /** @var pinax_oaipmh_core_SetInterface $set */
        $set = $this->getSet($metadataSets, $identifierVO->setSpec);
        $setInfo = $set->getSetInfo();
        /** @var pinax_oaipmh_models_VO_RecordVO $recordVO */
        $recordVO = $adapter->findById($set->getSetInfo(), $identifierVO);

        $output =  '<record>'.
                        '<header'.($recordVO->deleted ? ' status="deleted"' : '').'>'.
                            '<identifier>'.$this->encodeXmlText($identifier).'</identifier>'.
                            '<datestamp>'.$this->encodeXmlText($this->formatDatestamp($recordVO->datestamp)).'</datestamp>'.
                            '<setSpec>'.$this->encodeXmlText($setInfo['setSpec']).'</setSpec>'.
                        '</header>'.
                        '<metadata>'.
                            $set->getRecord($recordVO).
                        '</metadata>'.
                    '</record>';

        return $output;
    }

}
