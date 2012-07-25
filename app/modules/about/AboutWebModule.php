<?php
/**
  * @package Module
  * @subpackage About
  */

/**
  * @package Module
  * @subpackage About
  */
class AboutWebModule extends WebModule {
  protected $id = 'about';

  protected function getPhraseForDevice() {
    switch($this->platform) {
      case 'iphone':
        return $this->pagetype == 'tablet' ? 'iPad' : 'iPhone';
        
      case 'android':
        return 'Android '.($this->pagetype == 'tablet' ? 'tablets' : 'phones');
        
      default:
        switch ($this->pagetype) {
          case 'tablet':
            return 'tablet computers';
          
          case 'compliant':
            return 'touchscreen phones';
          
          case 'basic':
          default:
            return 'non-touchscreen phones';
        }
    }
  }
  
  protected function initializeForPage() {
    switch ($this->page) {
      case 'index':
        $this->loadPageConfigFile('index', 'aboutPages');
        break;
        
      case 'about_site':
        $this->assign('devicePhrase', $this->getPhraseForDevice()); // TODO: this should be more generic, not part of this module
        break;

      case 'help':
      case 'credits':
      case 'credits_html': // Used by AboutAPIModule to build credits html
      case 'about':
      case 'pane':
        break;

      default:
        $this->redirectTo('index');
        break;
    }
  }
}
