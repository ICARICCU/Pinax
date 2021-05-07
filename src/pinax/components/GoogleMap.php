<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_GoogleMap extends pinax_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('cssClass', false, null, COMPONENT_TYPE_STRING);
		$this->defineAttribute('label', false, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:required', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:requiredMessage', false, null, COMPONENT_TYPE_STRING);
		$this->defineAttribute('height', false, '800px', COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function process()
	{
		$this->_content = $this->_parent->loadContent($this->getId());
	}


	function render_html()
	{
		$this->_render_html();
		$this->addOutputCode($this->_content );
	}


	function getContent()
	{
		$this->_render_html();
		return $this->_content;
	}


	function _render_html()
	{
		if (!empty($this->_content)) {
			if (!pinax_ObjectValues::get('pinax.application', 'pdfMode')) {
				$this->_addJsCode();

				$id = $this->getOriginalId() . '_initialize';
				$height = $this->getAttribute('height');
				$values = $this->_content;
				$this->_content = <<<EOD
<script type="text/javascript">
// <![CDATA[
$(function() {
	var pos = ("$values").split(",");
	if ( pos.length < 3 )
	{
		pos = [51.500152, -0.126236, 15];
	}
	var myLatlng = new google.maps.LatLng(pos[0], pos[1]);
	var myOptions = {
	  zoom: parseInt(pos[2]),
	  center: myLatlng,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	var marker = new google.maps.Marker({
	                    position: myLatlng,
	                    map: map
	                });
});
// ]]>
</script>
<div id="map_canvas" style="height:$height"></div>
EOD;
			} else {
				list($la, $lo, $z) = explode(',', $this->_content);
				$this->_content = '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . $la . ',' . $lo . '&zoom=' . $z . '&size=400x400&markers=color:red%7C' . $la . ',' . $lo . '&key=' . __Config::get('pinax.maps.google.apiKey') . '" />';
			}

		}
	}

	function _addJsCode()
	{
		if (!pinax_ObjectValues::get('pinax.googleMap', 'add', false)) {
			$rootComponent = $this->getRootComponent();
			$rootComponent->addOutputCode(pinax_helpers_JS::linkJSfile('http://maps.google.com/maps/api/js?key=' . __Config::get('pinax.maps.google.apiKey')), 'head');
		}
	}

	public static function translateForMode_edit($node)
	{
		$attributes = array();
		$attributes['id'] = $node->getAttribute('id');
		$attributes['label'] = $node->getAttribute('label');
		$attributes['required'] = $node->getAttribute('required');
		$attributes['data'] = 'type=googlemaps';
		$attributes['xmlns:pnx'] = "pinax.components.*";

		if (count($node->attributes)) {
			foreach ($node->attributes as $index => $attr) {
				if ($attr->prefix == "adm") {
					$attributes[$attr->name] = $attr->value;
				}
			}
		}

		return pinax_helpers_Html::renderTag('pnx:Input', $attributes);
	}
}
