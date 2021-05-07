<?php
class pinax_oaipmh_controllers_ListRecords extends pinax_oaipmh_controllers_ListIdentifiers
{
    use \pinax_oaipmh_core_SetHelperTrait;
    use \pinax_oaipmh_core_ParamsTrait;
    use \pinax_oaipmh_core_ResumptionTokenTrait;
    use \pinax_oaipmh_core_XmlOutputTrait;

    /**
     * @param string $identifier
     * @param pinax_oaipmh_models_VO_RecordVO $recordVO
     * @param pinax_oaipmh_core_SetInterface $set
     * @return string
     */
    protected function makeResult($identifier, pinax_oaipmh_models_VO_RecordVO $recordVO, pinax_oaipmh_core_SetInterface $set)
    {
        $output =  '<record>'.
                        parent::makeResult($identifier, $recordVO, $set).
                        '<metadata>'.
                            $set->getRecord($recordVO).
                        '</metadata>'.
                    '</record>';

        return $output;
    }


}
