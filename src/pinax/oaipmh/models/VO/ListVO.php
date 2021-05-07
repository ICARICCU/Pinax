<?php
class pinax_oaipmh_models_VO_ListVO
{
    /** @var pinax_oaipmh_models_VO_RecordVO[] $records */
    public $records;
    public $numRows;

    /**
     * @param array $params
     * @return pinax_oaipmh_models_VO_ListVO
     */
    public static function create($records, $numRows)
    {
        $self = new self;
        $self->records = $records;
        $self->numRows = $numRows;

        return $self;
    }
}
