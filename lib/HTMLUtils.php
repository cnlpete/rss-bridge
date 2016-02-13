<?php
class HTMLUtils {

	public static function getHelperButtonFormat($value, $name){
		return '<button type="submit" name="format" value="' . $value . '">' . $name . '</button>';
	}

	public static function getHelperButtonsFormat($formats){
		$buttons = '';
			foreach( $formats as $name => $infos )
			{
				if ( isset($infos['name']) )
				{
					$buttons .= HTMLUtils::getHelperButtonFormat($name, $infos['name']) . PHP_EOL;
				}
			}
		return $buttons;
	}

	public static function displayBridgeCard($bridgeName, $formats, $isActive = true)
	{

		$bridgeElement = Bridge::create($bridgeName);
		if($bridgeElement == false) {
			return "";
		}
		$bridgeElement->loadMetadatas();

		$name = '<a href="'.$bridgeElement->uri.'">'.$bridgeElement->name.'</a>';
		$description = $bridgeElement->description;

		$card = <<<CARD
		<section id="bridge-{$bridgeName}" data-ref="{$bridgeName}">
			<h2>{$name}</h2>
			<p class="description">
				{$description}
			</p>
CARD;

		// If we don't have any parameter for the bridge, we print a generic form to load it.
		if(count($bridgeElement->parameters) == 0) {

			$card .= '<form method="GET" action="?">
					<input type="hidden" name="action" value="display" />
					<input type="hidden" name="bridge" value="' . $bridgeName . '" />' . PHP_EOL;

			if ($isActive)
			{
				$card .= HTMLUtils::getHelperButtonsFormat($formats);
			}
			else
			{
				$card .= '<span style="font-weight: bold;">Inactive</span>';
			}
			$card .= '</form>' . PHP_EOL;

		}

		$hasGlobalParameter = array_key_exists("global", $bridgeElement->parameters);
		if($hasGlobalParameter) {
			$globalParameters = json_decode($bridgeElement->parameters['global'], true);
		}
		
		foreach($bridgeElement->parameters as $parameterName => $parameter)
		{

			$parameter = json_decode($parameter, true);

			if(!is_numeric($parameterName) && $parameterName == "global") {

				continue;
				
			}
			
			if($hasGlobalParameter) {

				$parameter = array_merge($parameter, $globalParameters);

			}

			if(!is_numeric($parameterName)) {
				$card .= '<h5>'.$parameterName.'</h5>' . PHP_EOL;
			}
			$card .= '<form method="GET" action="?">
						<input type="hidden" name="action" value="display" />
						<input type="hidden" name="bridge" value="' . $bridgeName . '" />' . PHP_EOL;


			foreach($parameter as $inputEntry) {

				$additionalInfoString = "";
				if(isset($inputEntry['required'])) {

					$additionalInfoString .= " required=\"required\"";

				}
				if(isset($inputEntry['pattern'])) {

					$additionalInfoString .= " pattern=\"".$inputEntry['pattern']."\"";

				}
                if(isset($inputEntry['title'])) {
                    
                    $additionalInfoString .= " title=\"" .$inputEntry['title']."\"";
                    
                }
				if(!isset($inputEntry['exampleValue'])) $inputEntry['exampleValue'] = "";

				$idArg = 'arg-' . urlencode($bridgeName) . '-' . urlencode($parameterName) . '-' . urlencode($inputEntry['identifier']);

				$card .= '<label for="' .$idArg. '">' .$inputEntry['name']. ' : </label>' . PHP_EOL;

				if(!isset($inputEntry['type']) || $inputEntry['type'] == 'text') {
					$card .= '<input '.$additionalInfoString.' id="' . $idArg . '" type="text" value="" placeholder="' . $inputEntry['exampleValue'] . '" name="' . $inputEntry['identifier'] . '" /><br />' . PHP_EOL;
				} else if($inputEntry['type'] == 'number') {
					$card .= '<input '.$additionalInfoString.' id="' . $idArg . '" type="number" value="" placeholder="' . $inputEntry['exampleValue'] . '" name="' . $inputEntry['identifier'] . '" /><br />' . PHP_EOL;
				} else if($inputEntry['type'] == 'list') {
					$card .= '<select '.$additionalInfoString.' id="' . $idArg . '" name="' . $inputEntry['identifier'] . '" >';
					foreach($inputEntry['values'] as $listValues) {

						$card .= "<option $additionalInfoString value='" . $listValues['value'] . "'>" . $listValues['name'] . "</option>";

					}
					$card .= '</select><br >';
				} else if($inputEntry['type'] == 'checkbox') {

					$card .= '<input id="' . $idArg . '" type="checkbox" name="' . $inputEntry['identifier'] . '" /><br />' . PHP_EOL;

				}

			}
			if ($isActive)
			{
				$card .= HTMLUtils::getHelperButtonsFormat($formats);
			}
			else
			{
				$card .= '<span style="font-weight: bold;">Inactive</span>';
			}
			$card .= '</form>' . PHP_EOL;

		}

		$card .= '<span class="maintainer">'.$bridgeElement->maintainer.'</span>';
		$card .= '</section>';

		return $card;
	}


}

class HTMLSanitizer {


	var $tagsToRemove;
	var $keptAttributes;
	var $onlyKeepText;


	public static $DEFAULT_CLEAR_TAGS = ["script", "iframe"];
	public static $KEPT_ATTRIBUTES = ["title", "href", "src"];

	const ONLY_TEXT = null;

	function __construct($tags_to_remove = HTMLSanitizer::DEFAULT_CLEAR_TAGS, $kept_attributes = HTMLSanitizer::KEPT_ATTRIBUTES, $only_keep_text = HTMLSanitizer::ONLY_TEXT) {

		$this->tagsToRemove = $tags_to_remove;
		$this->keptAttributes = $kept_attributes;
		$this->onlyKeepText = $only_keep_text;

	}

	function sanitize($textToSanitize) {

		$htmlContent = str_get_html($textToSanitize);

		foreach($htmlContent->find('*[!j_ai_pas_trouve_comment_tout_demander]') as $element) {
			if(in_array($element->tag, $this->onlyKeepText)) {
				$element->outertext = $element->plaintext;
			} else if(in_array($element->tag, $this->tagsToRemove)) {
				$element->outertext = '';
			} else {
				foreach($element->getAllAttributes() as $attributeName => $attribute) {
					if(!in_array($attributeName, $this->keptAttributes)) $element->removeAttribute($attributeName);
				}
			}
		}

		return $htmlContent;

	}
	public static function defaultImageSrcTo($content, $server) {
        foreach($content->find('img') as $image) {
            if(strpos($image->src, '/')==0) {
                $image->src = $server.$image->src;
            }
        }
    }

}
?>
