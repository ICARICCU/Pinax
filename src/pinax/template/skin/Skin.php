<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_template_skin_Skin extends PinaxObject
{
	var $filePath;
	var $fileName;
	var $_templClass	= NULL;

	function __construct($fileName, $skinFolders, $defaultHtml='', $language='')
	{
		$fileCacheKey = implode('', $skinFolders).':'.$fileName.':'.$language;
		$compiler = pinax_ObjectFactory::createObject('pinax.compilers.Skin', null, __Config::get('DEBUG') ? 0 : -1);
		$compiledFileName = $compiler->verify($fileCacheKey);

		if (!$compiledFileName) {
			if (empty($defaultHtml)) {
				$foundPath = $this->foundFile($fileName, $skinFolders);
				if (!$foundPath) {
					throw new Exception('Skin file not found: '.$fileName);
				}

				$skinHtml = file_get_contents($foundPath.'/'.$fileName);
			} else {
				$skinHtml = $defaultHtml;
			}

			$compiledFileName = $compiler->compile(array('defaultHtml' => $skinHtml));
		}


		$this->fileName = basename($compiledFileName);
		$this->filePath = dirname($compiledFileName).'/';
	}

	/**
	 * @param  string $fileName
	 * @param  array(string) $skinFolders
	 * @return null|string
	 */
	private function foundFile($fileName, $skinFolders)
	{
		$foundPath = null;
		foreach ($skinFolders as $v) {
			if ($v && file_exists($v.'/'.$fileName)) {
				$foundPath = $v;
				break;
			}
		}

		return $foundPath;
	}
}
