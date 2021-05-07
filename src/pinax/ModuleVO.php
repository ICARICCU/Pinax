<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_ModuleVO
{
	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $classPath;

	/**
	 * @var string
	 */
	public $pageType = '';

	/**
	 * @var string|null
	 */
	public $model = null;

	/**
	 * @var string
	 */
	public $pluginSnippet = '';

	/**
	 * @var bool
	 */
	public $enabled = true;

	/**
	 * @var bool
	 */
	public $unique = true;

	/**
	 * @var bool
	 */
	public $show = true;

	/**
	 * @var bool
	 */
	public $edit = true;

	/**
	 * @var bool
	 */
	public $pluginInPageType = false;

	/**
	 * @var bool
	 */
	public $pluginInModules = false;

	/**
	 * @var bool
	 */
	public $pluginInSearch = false;

    /**
     * @var bool
     */
    public $canDuplicated = false;

	/**
	 * @var bool|string
	 */
	public $path = false;
}
