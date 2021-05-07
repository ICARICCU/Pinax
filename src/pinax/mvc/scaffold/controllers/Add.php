<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_mvc_scaffold_controllers_Add extends pinax_mvc_scaffold_controllers_Show
{
	function executeLater()
	{
		if ( $this->submit )
		{
			if ($this->view->validate())
			{
				$isNewRecord = $this->id == 0;
				$ar = pinax_ObjectFactory::createModel( $this->modelName );
				$this->id = $ar->save(__Request::getAllAsArray(), $isNewRecord);

				$this->redirect( $isNewRecord );
			}
		}
	}

	protected function redirect( $isNewRecord )
	{
		$this->logAndMessage( __T( 'Informazioni salvate con successo' ) );
		if ( !$this->refreshPage )
		{
			if ( !$isNewRecord )
			{
				$this->goHere();
			}
			else
			{
				$this->changePage( 'linkChangeAction', array( 'pageId' => $this->pageId, 'action' => 'add' ), array( 'id' => $this->id ) );
			}
		}
		else
		{
			$this->changePage( 'link', array( 'pageId' => $this->pageId ) );
		}
	}
}
