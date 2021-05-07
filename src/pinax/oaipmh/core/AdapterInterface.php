<?php
interface pinax_oaipmh_core_AdapterInterface
{
    /**
     * @param string $sets
     * @param string $from
     * @param string $until
     * @param integer $limitStart
     * @param integer $limitLength
     * @return pinax_oaipmh_models_VO_ListVO
     */
    public function findAll($sets, $from, $until, $limitStart, $limitLength);


     /**
     * @param string $model
     * @param string $id
     * @return string
     */
    public function createIdentifier($model, $id);

    /**
     * @param string $identifier
     * @return pinax_oaipmh_models_VO_IdentifierVO
     */
    public function parseIdentifier($identifier);
}
