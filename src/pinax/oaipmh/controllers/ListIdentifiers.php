<?php
class pinax_oaipmh_controllers_ListIdentifiers extends pinax_rest_core_CommandRest
{
    use \pinax_oaipmh_core_SetHelperTrait;
    use \pinax_oaipmh_core_ParamsTrait;
    use \pinax_oaipmh_core_ResumptionTokenTrait;
    use \pinax_oaipmh_core_XmlOutputTrait;

    /**
     * @return string
     */
    function execute()
    {
        /** @var pinax_oaipmh_models_VO_ResumptionInfoVO $resumptionInfoVO */
        $resumptionInfoVO = $this->resumptionInfoOrParams();
        $limitLength = __Config::get('oaipmh.maxIds');

        $output = '';
        $modelsMap = $this->modelsMap($resumptionInfoVO->metadataSets);
        /** @var pinax_oaipmh_core_AdapterInterface $adapter */
        $adapter = $this->application->getAdapter();

        $sets = $resumptionInfoVO->metadataSets;
        if ($resumptionInfoVO->set) {
            $sets = array_filter($sets, function($item) use ($resumptionInfoVO){
                $setInfo = $item->getSetInfo();
                return $setInfo['setSpec'] == $resumptionInfoVO->set;
            });

            if (!count($sets)) {
                throw pinax_oaipmh_core_Exception::cannotDisseminateFormat($resumptionInfoVO->set);
            }
        }


        /** @var pinax_oaipmh_models_VO_ListVO $result */
        $result = $adapter->findAll($sets, $resumptionInfoVO->from, $resumptionInfoVO->until, $resumptionInfoVO->limitStart, $limitLength);

        /** @var pinax_oaipmh_models_VO_RecordVO $doc */
        foreach ($result->records as $doc) {
            $output .= $this->makeResult($adapter->createIdentifier($modelsMap[$doc->setSpec], $doc->id), $doc, $modelsMap[$doc->setSpec]);
        }

        $resumptionInfoVO->prefix = 'ListIdentifiers';
        $resumptionInfoVO->numRows = $result->numRows;
        $resumptionInfoVO->limitEnd = $resumptionInfoVO->limitStart + $limitLength;

        return $output.$this->createResumptionToken($resumptionInfoVO);
    }

    /**
     * @param string $identifier
     * @param pinax_oaipmh_models_VO_RecordVO $recordVO
     * @param pinax_oaipmh_core_SetInterface $set
     * @return string
     */
    protected function makeResult($identifier, pinax_oaipmh_models_VO_RecordVO $recordVO, pinax_oaipmh_core_SetInterface $set)
    {
        $setInfo = $set->getSetInfo();
        $deletedAttribute = $recordVO->deleted ? ' status="deleted"' : '';
        $output  = '<header'.$deletedAttribute.'>'.
                        '<identifier>'.$this->encodeXmlText($identifier).'</identifier>'.
                        '<datestamp>'.$this->encodeXmlText($this->formatDatestamp($recordVO->datestamp)).'</datestamp>'.
                        '<setSpec>'.$this->encodeXmlText($setInfo['setSpec']).'</setSpec>'.
                    '</header>';

        return $output;
    }


}
