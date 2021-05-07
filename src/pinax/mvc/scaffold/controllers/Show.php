<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_mvc_scaffold_controllers_Show extends pinax_mvc_scaffold_controllers_AbstractCommand
{
	protected $ar;

	function execute()
	{
		if ( !$this->submit )
		{
			if ( is_numeric( $this->id ) )
			{
				if ( $this->id > 0 )
				{
					$this->ar = pinax_ObjectFactory::createModel( $this->modelName );
					if ($this->ar->load( $this->id )) {
						__Request::setFromArray( $this->ar->getValuesAsArray() );
					}
				}
			}
			else
			{
				$this->changePage( 'link', array( 'pageId' => $this->pageId ) );
			}
		}
	}
}
