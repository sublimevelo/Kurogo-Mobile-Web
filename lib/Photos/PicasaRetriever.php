<?php

class PicasaRetriever extends URLDataRetriever {
    protected $DEFAULT_PARSER_CLASS = 'PicasaDataParser';

    protected function init($args) {

        parent::init($args);

        $url = "https://picasaweb.google.com/data/feed/api/";

        if (isset($args['USER'], $args['ALBUM'])) {
            $url .= sprintf("user/%s/albumid/%s", $args['USER'], $args['ALBUM']);
        } else {
            throw new KurogoConfigurationException("USER and ALBUM values must be set for Picasa albums");
        }
                
        $this->setBaseURL($url);
        $this->addFilter('kind', 'photo');
        $this->addFilter('thumbsize', '72c');
        $this->addFilter('alt', 'json');
    }

}

