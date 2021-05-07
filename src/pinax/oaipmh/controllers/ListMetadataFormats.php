<?php
class pinax_oaipmh_controllers_ListMetadataFormats extends pinax_rest_core_CommandRest
{
    use \pinax_oaipmh_core_XmlOutputTrait;

    /**
     * @return string
     */
    function execute()
    {
        $output = '';
        $metadataFormats = $this->application->getMetadataFormat();
        /** @var pinax_oaipmh_models_VO_MetadataVO $metadataVO */
        foreach($metadataFormats as $metadataVO) {
            $output .= '<metadataFormat>'.
                            '<metadataPrefix>'.$this->encodeXmlText($metadataVO->prefix).'</metadataPrefix>'.
                            '<schema>'.$this->encodeXmlText($metadataVO->schema).'</schema>'.
                            '<metadataNamespace>'.$this->encodeXmlText($metadataVO->namespace).'</metadataNamespace>'.
                        '</metadataFormat>';
        }

        return $output;
    }
}
