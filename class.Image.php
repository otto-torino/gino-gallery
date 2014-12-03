<?php
/**
 * \file class.Image.php
 * @brief Contiene la definizione ed implementazione della classe Image.
 * 
 * @version 0.1.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Gallery;

use \Gino\ImageField;
use \Gino\ForeignKeyField;
use \Gino\GImage;

/**
 * \ingroup gino-gallery
 * Classe tipo model che rappresenta un media immagine.
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Image extends \Gino\Model {

    public static $table = 'gallery_image';
    protected static $_extension_img = array('jpg', 'jpeg', 'png');

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
            'category'=>_("Categoria"),
            'name'=>_("Nome"),
            'description'=>_("Descrizione"),
            'file'=>_("File"),
            'thumb'=>_("Thumbnail"),
        );

        parent::__construct($id);

        $this->_model_label = _('Immagine');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome immagine
     */
    function __toString() {
        return (string) $this->ml('name');
    }

    /**
     * @brief Sovrascrive la struttura di default
     * 
     * @see propertyObject::structure()
     * @param integer $id
     * @return array
     */
    public function structure($id) {

        $structure = parent::structure($id);
        
        $structure['category'] = new ForeignKeyField(array(
            'name'=>'category', 
            'model'=>$this,
            'required'=>true,
            'lenght'=>3, 
            'foreign'=>'\Gino\App\Gallery\Category', 
            'foreign_order'=>'name ASC',
            'add_related' => true,
            'add_related_url' =>$this->_controller->linkAdmin(array(), "block=ctg&insert=1")
        ));
        
		$base_path = $this->_controller->getBaseAbsPath();
        
        $structure['file'] = new ImageField(array(
            'name'=>'file', 
            'model'=>$this,
            'lenght'=>100, 
            'extensions'=>self::$_extension_img, 
            'resize'=>false, 
            'path'=>$base_path, 
        	'add_path'=>OS.'img'
        ));

        $structure['thumb'] = new ImageField(array(
            'name'=>'thumb', 
            'model'=>$this,
            'lenght'=>100, 
            'extensions'=>self::$_extension_img, 
            'resize'=>false, 
            'path'=>$base_path, 
        	'add_path'=>OS.'thumb'
        ));

        return $structure;
    }

    /**
     * @brief Path relativo all'immagine
     * @ return path
     */
    public function path() {
        
    	return $this->_controller->getBasePath().'/img/'.$this->file;
    }

    /**
     * @brief Path relativo della thumb
     * @description se la thumb non è stata inserita viene generata da @ref GImage delle dimensioni date
     * @param int $w larghezza thumb se creata al volo
     * @param int $h altezza thumb se creata al volo
     * @return path
     */
    public function thumbPath($w = 100, $h = 100) {
        if($this->thumb) {
            
        	return $this->_controller->getBasePath().'/thumb/'.$this->thumb;
        }
        else {
        	$image = new GImage(\Gino\absolutePath($this->path()));
        	$thumb = $image->thumb($w, $h);
        	return $thumb->getPath();
        }
    }

}
