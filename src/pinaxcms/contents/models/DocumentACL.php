<?php
// TODO
// implementare il tag relation nel compiler del model ed elimare questo provvisioro

class pinaxcms_contents_models_DocumentACL extends pinax_dataAccessDoctrine_ActiveRecord {

    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);
        $this->setTableName('documents_tbl', pinax_dataAccessDoctrine_DataAccess::getTablePrefix($connectionNumber));
        
        $sm = new pinax_dataAccessDoctrine_SchemaManager($this->connection);
        $sequenceName = $sm->getSequenceName($this->getTableName());
        $this->setSequenceName($sequenceName);
        
        $fields = $sm->getFields($this->getTableName());
        
        foreach ($fields as $field) {
            $this->addField($field);
        }
        
        $this->addRelation(array('type' => 'joinTable', 'name' => 'rel_aclEdit', 'className' => 'pinax.models.JoinDoctrine', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => '__aclEdit', 'objectName' => ''));
        $this->addRelation(array('type' => 'joinTable', 'name' => 'rel_aclView', 'className' => 'pinax.models.JoinDoctrine', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => '__aclView', 'objectName' => ''));
        $this->setProcessRelations(true);
    }
}
