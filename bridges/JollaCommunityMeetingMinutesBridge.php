<?php
/**
* JollaCommunityMeetingMinutesBridge
* Retrieve logs from jolla meeting minutes
* 2016-02-13
*/
class JollaCommunityMeetingMinutesBridge extends BridgeAbstract{

  public function loadMetadatas() {
		$this->maintainer = "cnlpete";
		$this->name = "JollaCommunityMeetingMinutesBridge";
		$this->uri = "http://merproject.org/meetings/mer-meeting/";
		$this->description = "Returns the Jolla/Mer community meeting minutes";
		$this->update = "2016-02-13";

		$this->parameters["Format"] =
		'[
			{
				"name" : "Filetype",
				"identifier" : "type",
        "type": "list",
        "values" : [
					{
						"name" : "HTML",
						"value" : "html"
					},
					{
						"name" : "text",
						"value" : "txt"
					}
				]
			},
			{
				"name" : "Size",
				"identifier" : "size",
        "type": "list",
        "values" : [
					{
						"name" : "Log",
						"value" : "log"
					},
					{
						"name" : "Full",
						"value" : "full"
					}
				]
			}
		]';
	}

  public function collectData(array $param) {
    $host = 'http://merproject.org/meetings/mer-meeting/';
    $regex = '(\d\d\d\d-\d\d-\d\d)-(\d\d)\.(\d\d)';
    if ($param['size'] == 'log') {
      $regex .= '\.log';
    }
    $regex .= '\.'.$param['type'];
    $regex = '/'.$regex.'/';

    $html = file_get_html($host) or $this->returnError('Could not request Mer Meeting Minutes.', 404);
    foreach($html->find('td a') as $element) {

      if ($element->plaintext != 'Parent Directory') {
        $htmlYear = file_get_html($host.$element->href) or $this->returnError('Could not request Mer Meeting Minutes for '.$element->href, 404);

        foreach($htmlYear->find('td a') as $elementYear) {

          if (preg_match($regex, $elementYear->plaintext, $matches)) {
            $date = new DateTime($matches[1]);
            if ($matches.length >= 3) {
              $date->setTime($matches[2], $matches[3]);
            }

            //$content = file_get_html($host.$element->href.$elementYear->href) or $this->returnError('Could not request Mer Meeting Minutes for '.$elementYear->href, 404)->plaintext;
            $content = $host.$element->href.$elementYear->href;

            $item = new \Item();
            $item->id = $host.$element->href.$elementYear->href;
            $item->uri = $host.$element->href.$elementYear->href;
            $item->content = $content;
            $item->title = $elementYear->plaintext;
            $item->timestamp = $date->getTimestamp();

            $this->items[] = $item;
          }
        }
      }
    }
    
    usort($this->items, function($a, $b) {
      return $b->timestamp - $a->timestamp;
    });
  }

  public function getName(){
    return 'JollaCommunityMeetingMinutesBridge';
  }

  public function getURI(){
    return 'http://merproject.org/meetings/mer-meeting/';
  }

  public function getCacheDuration(){
    return 3600*24; // 8 hours
  }
}
