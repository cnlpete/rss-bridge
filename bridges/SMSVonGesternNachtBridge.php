<?php
class SMSVonGesternNachtBridge extends BridgeAbstract {

  public function loadMetadatas() {
		$this->maintainer = "cnlpete";
		$this->name = "SMSVonGesternNachtBridge";
		$this->uri = "http://www.smsvongesternnacht.de/";
		$this->description = "The Unofficial SMSVonGesternNacht Bridge";
		$this->update = "2016-02-13";
	}

  public function collectData(array $param) {
    $html = file_get_html('http://www.smsvongesternnacht.de/') or $this->returnError('Could not request SMSVonGesternNacht.', 404);

    foreach($html->find('article.sms-thread') as $element) {
      $contents = [];

      foreach($element->find('div.field', 0)->find('.sms-participant') as $entry) {
        $contents[] = $entry;
      }

      $item = new Item();
      $item->uri = $element->find('a.sms-fb-url', 0)->href;
      $item->content = join(' ', $contents);
      $item->title = $element->find('.node-title', 0)->plaintext;
      $this->items[] = $item;
    }
  }

  public function getName() {
    return 'SMSVonGesternNacht';
  }

  public function getURI() {
    return 'http://www.smsvongesternnacht.de/';
  }

  public function getDescription() {
    return 'SMSVonGesternNacht via rss-bridge';
  }

  public function getCacheDuration() {
    return 1 * 3600; // 1 hour
  }
}
