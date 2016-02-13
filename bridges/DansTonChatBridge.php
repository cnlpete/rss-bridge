<?php
class DansTonChatBridge extends BridgeAbstract{

	public function loadMetadatas() {

		$this->maintainer = "Astalaseven";
		$this->name = "DansTonChat Bridge";
		$this->uri = "http://danstonchat.com/latest.html";
		$this->description = "Returns latest quotes from DansTonChat.";
		$this->update = "2014-05-25";

	}

    public function collectData(array $param){
        $html = '';
        $link = 'http://danstonchat.com/latest.html';

        $html = file_get_html($link) or $this->returnError('Could not request DansTonChat.', 404);

        foreach($html->find('div.item') as $element) {
                $item = new \Item();
                $item->uri = $element->find('a', 0)->href;
                $item->title = 'DansTonChat '.$element->find('a', 1)->plaintext;
                $item->content = $element->find('a', 0)->innertext;
                $this->items[] = $item;
        }
    }

    public function getName(){
        return 'DansTonChat';
    }

    public function getURI(){
        return 'http://danstonchat.com';
    }

    public function getCacheDuration(){
        return 21600; // 6 hours
    }
}
