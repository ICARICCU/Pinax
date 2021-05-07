<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_mvc_scaffold_controllers_Delete extends pinax_mvc_scaffold_controllers_AbstractCommand
{
	function execute()
	{
		if ( $this->id > 0 )
		{
			$this->logAndMessage( __T( 'Record cancellato' ) );
			$ar = pinax_ObjectFactory::createModel( $this->modelName );
			$ar->delete( $this->id );
			$this->changePage( 'link', array( 'pageId' => $this->pageId ) );
		}
	}
}
