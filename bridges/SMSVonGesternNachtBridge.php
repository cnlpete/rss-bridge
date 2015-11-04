<?php
/**
*
* @name SMSVonGesternNachtBridge
* @homepage http://www.smsvongesternnacht.de/
* @description The Unofficial SMSVonGesternNacht Bridge
* @update 04/11/2015
* @maintainer cnlpete
*/
class SMSVonGesternNachtBridge extends BridgeAbstract {

    public function collectData(array $param) {
        $html = file_get_html('http://www.smsvongesternnacht.de/') or $this->returnError('Could not request SMSVonGesternNacht.', 404);

        foreach($html->find('article.sms-thread') as $element) {
            $contents = [];
            foreach($element->find('div.field-items', 0)->find('.sms-participant') as $entry) {
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
