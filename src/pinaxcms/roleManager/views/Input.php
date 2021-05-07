<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_roleManager_views_Input extends pinax_components_Input
{
	function render($outputMode = NULL, $skipChilds = false)
	{
		parent::render($outputMode, $skipChilds);

		$rootComponent = &$this->getRootComponent();
		if (!pinax_ObjectValues::get('pinax.JS.textext', 'add', false)) {
			pinax_ObjectValues::set('pinax.JS.textext', 'add', true);

			$corePath = __Paths::get('PINAX_CMS_STATIC_DIR');
	        $jQueryPath = $corePath.'jquery/';

			$this->addOutputCode( pinax_helpers_JS::linkJSfile( $jQueryPath.'select2/select2.min.js' ), 'head');
			$this->addOutputCode( pinax_helpers_CSS::linkCSSfile( $jQueryPath.'select2/select2.css' ), 'head');
		}

		$id = $this->getId();

		$content = $this->_content ? json_encode($this->_content) : '[]';
		$ajaxUrl = 'ajax.php?pageId='.__Request::get('pageId').'&ajaxTarget='.$this->getId();
		$output = <<<EOD
<script type="text/javascript">
$(function(){
	$('#$id').val('');
    $('#$id').select2({
        multiple: true,
        ajax: {
            url: '$ajaxUrl',
            dataType: 'json',
            quietMillis: 100,
            data: function(term, page) {
                return {
                    q: term,
                };
            },
            results: function(data, page ) {
                return { results: data }
            }
        },
    });

    $('#$id').select2('data', $content);
});
</script>
EOD;
		$rootComponent->addOutputCode($output, 'head');
	}

	function process_ajax() {
		$mode = $this->getAttribute('mode');
		$q = __Request::get('q');
		$result = array();
		if ($mode == 'users') {
			$it = pinax_ObjectFactory::createModelIterator('pinaxcms.userManager.models.User', 'all');
            $it->setOrFilters(array(
                                "user_firstName" => $q,
                                "user_lastName" => $q,
                                "user_loginId" => $q,
                             ));
			foreach ($it as $ar) {
                $result[] = array('id' => $ar->user_id, 'text' => $ar->user_loginId);
			}
		} else if ($mode == 'groups') {
			$it = pinax_ObjectFactory::createModelIterator('pinaxcms.groupManager.models.UserGroup', 'all',
					array('filters' => array('usergroup_name' => $q) )
				);
			foreach ($it as $ar) {
                $result[] = array('id' => $ar->usergroup_id, 'text' => $ar->usergroup_name);
			}
		} else {
			$it = pinax_ObjectFactory::createModelIterator('pinaxcms.roleManager.models.Role', 'all',
					array('filters' => array('role_name' => $q) )
				);
			foreach ($it as $ar) {
                $result[] = array('id' => $ar->role_id, 'text' => $ar->role_name);
			}
		}
		return $result;
	}
}
