<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_application_SiteMapIterator extends PinaxObject
{
	/**
	 * @var pinax_application_SiteMap
	 */
	var $_treeManager;

	/**
	 * @var bool
	 */
	var $EOF;

	/**
	 * @var pinax_application_SiteMapNode|null
	 */
	var $_currentNode;

	function __construct(&$parent)
	{
		$this->_treeManager 	= &$parent;
		$this->EOF = false;
		$this->_currentNode = $this->_treeManager->getHomeNode();

	}

	/**
	 * @return pinax_application_SiteMapNode|null
	 */
	public function moveNext()
	{
		$tempNode = null;
		if ($this->_currentNode->hasChildNodes()) {
			$tempNode = $this->_currentNode->firstChild(false, true);
		}

		if (!$tempNode) {
			$tempNode = $this->_currentNode->nextSibling();
		}

		if (!$tempNode) {
			$tempNode = $this->_currentNode;
			while (true)
			{
				$node = $tempNode->parentNode();

				if (!is_null($node))
				{
					$node2 = $node->nextSibling();
					if (!is_null($node2))
					{
						$tempNode = $node2;
						break;
					}
					else
					{
						$tempNode = $node;
					}
				}
				else
				{
					$tempNode = null;
					break;
				}
			}
		}

		$this->_update($tempNode);
		return $this->getNode();
	}

	/**
	 * @return pinax_application_SiteMapNode|null
	 */
	public function movePrevious()
	{
		$tempNode = $this->_currentNode->previousSibling();
		if (is_null($tempNode)) {
			$tempNode = $this->_currentNode->parentNode();
		} else {
			while (true)
			{
				$tempNodeChild = $tempNode->childNodes();
				if (!count($tempNodeChild)) {
					break;
				}
				$tempNode = array_pop($tempNodeChild);
			}
		}

		$this->_update($tempNode);
		return $this->getNode();
	}

	/**
	 * @return pinax_application_SiteMapNode|null
	 */
	public function &getNode()
	{
		return $this->_currentNode;
	}

	/**
	 *
	 * @return pinax_application_SiteMapNode|null
	 */
	public function &getNodeArray()
	{
		return $this->_treeManager->_siteMapArray[$this->_currentNode->id];
	}

	/**
	 * @return void
	 */
	public function setNode($node)
	{
		$this->_currentNode = $node;
	}

	/**
	 * @return void
	 */
	public function reset()
	{
		$this->EOF = false;
		$this->_currentNode = $this->_treeManager->getHomeNode();
	}

	/**
	 * @return void
	 */
	private function _update(&$node)
	{
		$this->EOF = is_null($node);
		$this->_currentNode = $node;
	}


	/**
	 * @return bool
	 */
	public function hasMore()
	{
		return !$this->EOF;
	}
}
