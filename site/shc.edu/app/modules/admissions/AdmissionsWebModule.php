<?php

class AdmissionsWebModule extends ContentWebModule {
    protected $configModule = 'admissions';

        // overrides function in Module.php
        protected function loadFeedData($group=null) {  
                if(!$group){
                        $items = $this->getModuleSections('feeds');            
                        foreach ($items as $key => &$item) {
                                if (isset($item['GROUP']) && strlen($item['GROUP'])) {
                                        $groupData = $this->getDataForGroup($item['GROUP']);
                                        if (!isset($item['TITLE']) && isset($groupData['TITLE'])) {
                                                $item['TITLE'] = $groupData['TITLE'];
                                        }
                                        $item['url'] = $this->buildBreadcrumbURL('group', array('group'=>$item['GROUP']));
                                }else{
                                        if(isset($item['URL'])){
                                                $content_type = isset($item['CONTENT_TYPE']) ? $item['CONTENT_TYPE'] : '';
                                                switch($content_type){
                                                        case 'email':
                                                                $item['url'] = 'mailto:' . $item['URL'];
                                                                $item['class'] = 'email';
                                                                break;
                                                        case 'phone':
                                                                $item['url'] = 'tel:' . $item['URL'];
                                                                $item['class'] = 'phone';
                                                                break;
                                                        case 'url':
                                                                $item['url'] = $item['URL'];
                                                                $item['class'] = 'external';
                                                                break;
                                                        default:
                                                                throw new Exception("Invalid content type $content_type");
                                                }
                                        }else{
                                                $item['url'] = $this->buildBreadcrumbURL('page', array('page'=>$key));
                                        }
                                }
                                $item['title'] = $item['TITLE'];
                        }
                }else{
                        $items = $this->getItemsForGroup($group);
                        foreach($items as $key => &$item){
                                if (!isset($item['TITLE']) && isset($groupData['TITLE'])) {
                                        $item['title'] = $groupData['TITLE'];
                                }else{
                                        $item['title'] = $item['TITLE'];
                                }
                                $item['url'] = $this->buildBreadcrumbURL('page', array('group' => $group, 'page'=>$key));
                        }
                }
                
        return $items;
    }
}
