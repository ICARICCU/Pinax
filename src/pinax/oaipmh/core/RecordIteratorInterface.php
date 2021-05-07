<?php
interface pinax_oaipmh_core_RecordIteratorInterface extends Iterator
{
    /**
     * @return pinax_oaipmh_models_VO_RecordVO
     */
    public function current();
}
