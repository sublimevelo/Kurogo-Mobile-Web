<?php

class SiteEmergencyWebModule extends EmergencyWebModule
{
	
	private $cache_key_emerg = "emergency-notice-available";
	private $cache_ttl;
	
    protected function getModuleNavigationData($moduleNavData) {
		
		$this->cache_ttl = 60 * 15; // 15 minutes
		
		// if cache entry does not exist, retrieve value and cache it
		if (!$is_emergency = Kurogo::getCache($this->cache_key_emerg)) {
			$this->initialize();
			$is_emergency = $this->getHomeScreenAlert() ? 'yes': 'no';
			//KurogoDebug::debug("cache miss: " . $is_emergency, $halt=false);
			Kurogo::setCache($this->cache_key_emerg, $is_emergency, $ttl = $this->cache_ttl);
		}
		
		// 	$is_emergency should now have a value from cache OR from retrieval,
		//	update navigation data
		if ($is_emergency=='yes') {
			$moduleNavData['img'] = '/modules/emergency/images/emergency-on.png';
			$moduleNavData['title'] = 'Emergency!';
			$moduleNavData['class'] = 'active';
		}
        //you must return the updated array
        return $moduleNavData;
    }
}
