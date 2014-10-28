<?php

class GalleryImage extends Model {

        private $_controller;

        public static $table = 'gallery_image';
        protected static $_extension_img = array('jpg', 'jpeg', 'png');

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record
     * @param object $instance istanza del controller
     */
    function __construct($id) {

        $this->_controller = 'gallery';
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'category'=>_("Categoria"),
            'name'=>_("Nome"),
            'description'=>_("Descrizione"),
            'file'=>_("File"),
            'thumb'=>_("Thumbnail"),
        );

        parent::__construct($id);

        $this->_model_label = $this->id ? $this->name : '';
    }

    function __toString() {
        return (string) $this->name;
    }

    public function structure($id) {

        $structure = parent::structure($id);
        
        $structure['category'] = new ForeignKeyField(array(
            'name'=>'category', 
            'model'=>$this,
            'required'=>true,
            'lenght'=>3, 
            'foreign'=>'GalleryCategory', 
            'foreign_order'=>'name ASC'
        ));
        

        $structure['file'] = new ImageField(array(
            'name'=>'file', 
            'model'=>$this,
            'lenght'=>100, 
            'extensions'=>self::$_extension_img, 
            'resize'=>false, 
            'path'=>CONTENT_DIR.OS.'gallery'.OS.'img', 
        ));

        $structure['thumb'] = new ImageField(array(
            'name'=>'thumb', 
            'model'=>$this,
            'lenght'=>100, 
            'extensions'=>self::$_extension_img, 
            'resize'=>false, 
            'path'=>CONTENT_DIR.OS.'gallery'.OS.'thumb', 
        ));

        return $structure;
    }

    public function path() {
        return CONTENT_WWW.'/gallery/img/'.$this->file;
    }

    public function thumbPath($w = 100, $h = 100) {
        if($this->thumb) {
            return CONTENT_WWW.'/gallery/thumb/'.$this->thumb;
        }
        else {
            $image = new GImage(absolutePath($this->path()));
            $thumb = $image->thumb($w, $h);
            return $thumb->getPath();
        }

    }

}
