<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_ajax_SaveDraft extends pinaxcms_contents_controllers_pageEdit_ajax_Save
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;

        $status = __Request::get('status');

        $reload = $status!=pinaxcms_contents_views_components_PageEdit::STATUS_DRAFT;
        $result = $this->save($data, true);
        if ($result===true && $reload) {
            return array(
                'evt' => 'pinaxcms.pageEdit',
                'message' => array('menuId' => $this->menuId.'&status='.pinaxcms_contents_views_components_PageEdit::STATUS_DRAFT)
                );
        }

        return $result;
    }
}



