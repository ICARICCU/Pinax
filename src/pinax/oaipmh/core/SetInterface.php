<?php
interface pinax_oaipmh_core_SetInterface
{
    /**
     * @return array
     */
    public function getSetInfo();

    /**
     * @param pinax_oaipmh_models_VO_RecordVO $recordVO
     * @return string
     */
    public function getRecord(pinax_oaipmh_models_VO_RecordVO $recordVO);
}
