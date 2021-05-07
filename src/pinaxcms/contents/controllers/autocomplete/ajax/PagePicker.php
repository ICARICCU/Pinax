<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_autocomplete_ajax_PagePicker extends pinax_mvc_core_CommandAjax
{
	use pinax_mvc_core_AuthenticatedCommandTrait;

    function execute($term, $id, $protocol, $menutype, $pagetype)
    {
	    $this->checkPermissionForBackend();
        $this->directOutput = true;

        $filters = ['menuType' => $menutype, 'pageType' => $pagetype];
        $speakingUrlManager = $this->application->retrieveProxy('pinaxcms.speakingUrl.Manager');
        if ($id && !$term) {
            $result = array();
            $json = json_decode($id);
            $id  = is_array($json) ? $json : array($id);
            foreach($id as $v) {
                $tempResult = $speakingUrlManager->searchDocumentsByTerm('', $v, $protocol, $filters);
                if ($tempResult && is_array($tempResult)) {
                    $result[] = $tempResult[0];
                }
            }

        } else {
            $result = $speakingUrlManager->searchDocumentsByTerm($term, '', $protocol, $filters);

        }
        return $result;
    }
}
