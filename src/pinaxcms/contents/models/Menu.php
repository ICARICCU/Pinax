<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_models_Menu extends pinax_dataAccessDoctrine_ActiveRecord {

    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);
        $this->setTableName('menus_tbl', pinax_dataAccessDoctrine_DataAccess::getTablePrefix($connectionNumber));

        $sm = new pinax_dataAccessDoctrine_SchemaManager($this->connection);
        $sequenceName = $sm->getSequenceName($this->getTableName());
        $this->setSequenceName($sequenceName);

        $fields = $sm->getFields($this->getTableName());

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }
}
