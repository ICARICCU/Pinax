<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_components_FormEditWithDocumentStatus extends pinaxcms_views_components_FormEdit
{
    protected $statusToEdit = '';
    protected $availableStatus = [];

    /**
     * @param boolean $hasPublishedVersion
     * @param boolean $hasDraftVersion
     * @return void
     */
    public function setAvailableStatus($hasPublishedVersion, $hasDraftVersion)
    {
        $this->availableStatus = [];
        $this->availableStatus[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED] = $hasPublishedVersion;
        $this->availableStatus[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT] = $hasDraftVersion;
    }

    /**
     * @param string $status
     * @return void
     */
    public function setStatusToEdit($status)
    {
        $this->statusToEdit = $status;
    }

    /**
     * @return void
     */
    public function render_html_onStart()
    {
        parent::render_html_onStart();

        $this->renderStatusSwicth();
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return 'ajax.php?pageId='.$this->_application->getPageId().'&ajaxTarget='.$this->getId().
                '&status='.$this->statusToEdit.
                '&statusReleadUrl='.urlencode(__Link::addParams(['status'=>''])).
                '&action=';
    }

    /**
     * @return void
     */
    private function renderStatusSwicth()
    {
        if (!$this->availableStatus ||
            (!$this->availableStatus[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED] && !$this->availableStatus[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT]) ||
            ($this->availableStatus[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED] && !$this->availableStatus[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT])) return;

        $label = __T('PNX_RECORD_STATUS_EDIT_LABEL');
        $linkPublished = !$this->availableStatus[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED] ? '' : __Link::makeSimpleLink(
                                                    __T('PNX_RECORD_STATUS_PUBLISHED'),
                                                    __Link::addParams(array('status' => pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED)),
                                                    '',
                                                    $this->statusToEdit==pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_PUBLISHED ? 'active' : ''
        );

        $linkDraft = !$this->availableStatus[pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT] ? '' :__Link::makeSimpleLink(
                                                    __T('PNX_RECORD_STATUS_DRAFT'),
                                                    __Link::addParams(array('status' => pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT)),
                                                    '',
                                                    $this->statusToEdit==pinax_dataAccessDoctrine_ActiveRecordDocument::STATUS_DRAFT ? 'active' : ''
        );

        $html = <<<EOD
<ul class="pnxFormEdit status-swicth {$this->statusToEdit}">
    <li>$label</li>
    <li>$linkPublished</li>
    <li>$linkDraft</li>
</ul>
EOD;

        $this->addOutputCode($html);
    }
}
