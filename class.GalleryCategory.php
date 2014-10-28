<?php

class GalleryCategory extends Model {

    private $_controller;

    public static $table = 'gallery_category';

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record
     * @param object $instance istanza del controller
     */
    function __construct($id) {

        $this->_controller = new gallery();
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name'=>_("Nome"),
            'showcase'=>_("Disponibile per vista showcase"),
        );

        parent::__construct($id);

        $this->_model_label = $this->id ? $this->name : '';
    }

    function __toString() {
        return (string) $this->name;
    }

    /**
     * Sovrascrive la struttura di default
     * 
     * @see propertyObject::structure()
     * @param integer $id
     * @return array
     */
    public function structure($id) {
        
        $structure = parent::structure($id);

        $structure['showcase'] = new booleanField(array(
            'name'=>'showcase',
            'model'=>$this,
            'enum'=>array(1 => _('si'), 0 => _('no')), 
        ));

        return $structure;
    }

    public function getUrl() {
            return get_class($this->_controller).'/category/'.$this->id;
    }

    public function getImages() {
        return GalleryImage::objects(null, array('where' => "category='".$this->id."'"));
    }

}
