<?php

class BasePlacemark implements Placemark
{
    protected $id;
    protected $url;
    protected $title;
    protected $address;
    protected $subtitle; // defaults to address if not present
    protected $geometry;
    protected $style = null;
    protected $fields = array();
    protected $categories = array();
    protected $urlParams = array();
    
    public function __construct(MapGeometry $geometry) {
        $this->geometry = $geometry;
        $this->style = new MapBaseStyle();
    }

    public function getId() {
        return $this->id;
    }

    public function getAddress() {
        return $this->address;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function filterItem($filters) {
        foreach ($filters as $filter=>$value) {
            switch ($filter) {
                case 'search':
                    if (stripos($this->getTitle(), $value) === FALSE && stripos($this->getSubTitle(), $value) === FALSE) {
                        return false;
                    }
                    break;
                case 'min':
                    if (!isset($center)) {
                        $center = $this->getGeometry()->getCenterCoordinate();
                    }
                    if ($center['lat'] < $value['lat'] || $center['lon'] < $value['lon']) {
                        return false;
                    }
                    break;
                case 'max':
                    if (!isset($center)) {
                        $center = $this->getGeometry()->getCenterCoordinate();
                    }
                    if ($center['lat'] > $value['lat'] || $center['lon'] > $value['lon']) {
                        return false;
                    }
                    break;
            }
        }   
        
        return true;     
    }

    public function setGeometry(MapGeometry $geometry)
    {
        $this->geometry = $geometry;
    }

    public function getURL() {
        return $this->url;
    }
    
    // MapListElement interface
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getSubtitle() {
        if (isset($this->subtitle)) {
            return $this->subtitle;
        }
        return $this->address;
    }

    public function getCategoryIds() {
        return $this->categories;
    }

    public function addCategoryId($id) {
        if ($id && !in_array($id, $this->categories)) {
            $this->categories[] = $id;
        }
    }

    // Placemark interface

    public function getURLParams() {
        $result = $this->urlParams;
        if (isset($this->id)) {
            $result['featureindex'] = $this->getId();
        } else {
            $geometry = $this->getGeometry();
            if ($geometry) {
                $coords = $geometry->getCenterCoordinate();
                $result['lat'] = $coords['lat'];
                $result['lon'] = $coords['lon'];
            }
            $result['title'] = $this->getTitle();
        }

        $categories = $this->getCategoryIds();
        $category = implode(MAP_CATEGORY_DELIMITER, $categories);
        if ($category) {
            $result['category'] = $category;
        }
        return $result;
    }

    public function setURLParam($name, $value) {
        $this->urlParams[$name] = $value;
    }
    
    public function getGeometry() {
        return $this->geometry;
    }

    public function getFields()
    {
        return $this->fields;
    }
    
    public function getField($fieldName) {
        if (isset($this->fields[$fieldName])) {
            return $this->fields[$fieldName];
        }
        return null;
    }
    
    public function setField($fieldName, $value) {
        $this->fields[$fieldName] = $value;
    }

    public function getStyle() {
        return $this->style;
    }

    public function setStyle(MapStyle $style)
    {
        $this->style = $style;
    }
    
    // setters that get used by MapWebModule when its detail page isn't called with a feature
    
    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function setSubtitle($subtitle) {
        $this->subtitle = $subtitle;
    }

    public function setURL($url) {
        $this->url = $url;
    }

    public function serialize() {
        return serialize(
            array(
                'id' => $this->id,
                'title' => $this->title,
                'subtitle' => $this->subtitle,
                'address' => $this->address,
                'url' => $this->url,
                'urlParams' => serialize($this->urlParams),
                'categories' => serialize($this->categories),
                'fields' => serialize($this->fields),
                'style' => serialize($this->style),
                'geometry' => serialize($this->geometry),
            ));
    }

    public function unserialize($data) {
        $data = unserialize($data);
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->subtitle = $data['subtitle'];
        $this->address = $data['address'];
        $this->url = $data['url'];
        $this->urlParams = unserialize($data['urlParams']);
        $this->categories = unserialize($data['categories']);
        $this->fields = unserialize($data['fields']);
        $this->style = unserialize($data['style']);
        $this->geometry = unserialize($data['geometry']);
    }
}
