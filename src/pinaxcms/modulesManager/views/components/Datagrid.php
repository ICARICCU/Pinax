<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_modulesManager_views_components_Datagrid extends pinax_components_Component
{
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('cssClass', 		false,	'odd,even',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('label', 		false,	'',		COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	public function render($outputMode = NULL, $skipChilds = false)
	{
// TODO tradurre le label

		$pageId = $this->_application->getPageId();
		$output = '';
		$output .= '<table id="'.$this->getId().'" class="js-modulesManager '.$this->getAttribute('cssClass').'">';
		if ($this->getAttribute('label')!='') $output .= '<caption>'.$this->getAttribute('label').'</caption>';

		// disegna le colonne
		$output .= '<thead>';
		$output .= '<tr>';
		$output .= '<th class="name">Plugin</th>';
		$output .= '<th>Descrizione</th>';
		$output .= '<th class="actions"></th>';
		$output .= '</tr>';

		$output .= '<tfoot><tr><td colspan="3"></td></tr></tfoot>';

		$output .= '<tbody>';
		$origCssClass 		= explode(',', $this->getAttribute('cssClass'));
		$tempCssClass = array();
		$modulesState = pinax_Modules::getModulesState();

		$modules = pinax_Modules::getModules();
		$this->sort( $modules );

		foreach( $modules as $m )
		{
			// $moduleDescription = pinax_ObjectFactory::createObject( 'pinax.ModuleDescription', $m );
			// if ( !empty( $m->pageType ) ) continue;
			$isEnabled = !isset( $modulesState[ $m->id ] ) || $modulesState[ $m->id ];

			if (!count($tempCssClass)) $tempCssClass = $origCssClass;
			$cssClass = array_shift($tempCssClass);
			$cssClass .= ' '.( $isEnabled ? 'enabled' : 'disabled' );

			$output .= '<tr class="'.$cssClass.'">'.
						'<td class="name">'.__T($m->name).'</td>'.
						'<td>'.
							'<p class="description">'.$m->description.'</p>'.
							'<p class="info">Versione '.$m->version.' | '.
								__Link::makeLink2( null, array( 'label' => $m->author, 'title' => 'Visita il sito dell\'autore',  'url' => $m->authorUrl, 'rel' => 'external' ) ).' | '.
								__Link::makeLink2( null, array( 'label' => 'Visita il sito del plugin',  'url' => $m->pluginUrl, 'rel' => 'external'  ) ).
							'</p>'.
						'</td>'.
						'<td class="actions">'.
							( !$isEnabled ? '<a href="" data-action="enable" data-id="'.$m->id.'" class="js-modulesManagerAction action">abilita</a>' : '' ).
							( $isEnabled ? '<a href="" data-action="disable" data-id="'.$m->id.'" class="js-modulesManagerAction action">disabilita</a>' : '' ).
							' '.( $m->canDuplicated ? '<a href="'.__Routing::makeUrl('pinaxcmsModuleManagerDuplicate', array('pageId' => $pageId, 'id' => $m->id)).'" class="action">duplica</a>' : '' ).
						'</td>'.
						'</tr>';
		}
		$output .= '</tbody>';
		$output .= '</table>';
		$output .= <<<EOD
<script type="text/javascript">
jQuery(document).ready(function() {

	$("table.js-modulesManager a.js-modulesManagerAction ").click( function( e ){
		e.stopPropagation();
		if ( jQuery( this ).data( "action" ) == "uninstall" )
		{
			if ( !confirm( "Sei sicuro di voler rimuovere il plugin?") )
			{
				return false;
			}
		}

		// jQuery.modal('<div></div>', {
		// 	close: false,
		// 	overlayCss:{
		// 		backgroundColor:"#000"
		// 	},
		// 	overlayClose: false
		// });

		jQuery.ajax( { url: Pinax.ajaxUrl+jQuery( this ).data( "action" ),
					type: 'POST',
					data: { id: jQuery( this ).data( "id" ) },
					success: function( response, r, a ) {
						location.reload();
					}});
		return false;
	});

});
</script>
EOD;

		$this->addOutputCode($output);
	}


	private function sort( &$arr, $f='strnatcasecmp')
	{
        return usort($arr, create_function('$a, $b', "return $f(\$a->name, \$b->name);"));
	}
}
