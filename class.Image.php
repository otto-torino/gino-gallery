<?php
/**
 * @file class.Image.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gallery.Image
 *
 * @copyright 2014-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Gallery;

use \Gino\ImageField;
use \Gino\ForeignKeyField;
use \Gino\GImage;

/**
 * \ingroup gallery
 * @brief Classe di tipo Gino.Model che rappresenta un media immagine
 *
 * @version 1.2.0
 * @copyright 2014-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Image extends \Gino\Model {

    public static $table = 'gallery_image';
    public static $columns;
    
    protected static $_extension_img = array('jpg', 'jpeg', 'png');

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Gallery.Image
     */
    function __construct($id) {

        $this->_controller = new gallery();
        $this->_tbl_data = self::$table;

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
     * @see Gino.Model::properties()
     */
    protected static function properties($model, $controller) {
    	 
    	$base_path = $controller->getBaseAbsPath();
    
    	$property['category'] = array(
    		'add_related_url' => $controller->linkAdmin(array(), "block=ctg&insert=1"),
    	);
    	$property['file'] = array(
    		'path' => $base_path,
    		'add_path' => OS.'img'
    	);
    	$property['thumb'] = array(
    		'path' => $base_path,
    		'add_path' => OS.'thumb'
    	);
    	 
    	return $property;
    }

    /**
     * Struttura dei campi della tabella di un modello
     *
     * @return array
     */
    public static function columns() {

        $columns['id'] = new \Gino\IntegerField(array(
    		'name' => 'id',
    		'primary_key' => true,
    		'auto_increment' => true,
    		'max_lenght' => 11,
    	));
        $columns['category'] = new ForeignKeyField(array(
            'name' => 'category',
            'label' => _("Nome galleria"),
            'required' => true,
            'max_lenght' => 11,
            'foreign' => '\Gino\App\Gallery\Category',
            'foreign_order' => 'name ASC',
            'add_related' => true,
            'add_related_url' => null
        ));
        $columns['name'] = new \Gino\CharField(array(
        	'name' => 'name',
        	'label' => _("Etichetta del file"),
        	'required' => true,
        	'max_lenght' => 255,
        ));
        $columns['description'] = new \Gino\TextField(array(
        	'name' => 'description',
        	'label' => _("Descrizione"),
        	'required' => false
        ));
		$columns['file'] = new ImageField(array(
            'name' => 'file',
        	'label' => _("File"),
        	'required'=>true,
            'max_lenght' => 255,
            'extensions' => self::$_extension_img,
            'resize' => false,
            'path' => null,
            'add_path' => null
        ));
		$columns['thumb'] = new ImageField(array(
            'name' => 'thumb',
        	'label' => _("Thumbnail"),
            'max_lenght' => 255,
            'extensions' => self::$_extension_img,
            'resize' => false,
            'path' => null,
            'add_path' => null
        ));

        return $columns;
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

    /**
     * Dimensioni immagine
     * @return array ('width' => WIDTH, 'height' => HEIGHT)
     */
    public function getSize()
    {
        list($width, $height, $type, $attr) = getimagesize(\Gino\absolutePath($this->path()));
        return array('width' => $width, 'height' => $height);
    }

}
Image::$columns=Image::columns();