<?php
/**
 * @file class.Category.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gallery.Category
 *
 * @copyright 2014-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Gallery;

use \Gino\BooleanField;

/**
 * \ingroup gallery
 * @brief Classe tipo Gino.Model che rappresenta una categoria di media
 *
 * @version 1.2.0
 * @copyright 2014-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Category extends \Gino\Model {

    public static $table = 'gallery_category';
    public static $columns;

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Gallery.Category
     */
    function __construct($id) {

        $this->_controller = new gallery();
        $this->_tbl_data = self::$table;
        
        parent::__construct($id);
        
        $this->_model_label = _('Galleria');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome categoria
     */
    function __toString() {
        return (string) $this->ml('name');
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
    	$columns['name'] = new \Gino\CharField(array(
    		'name' => 'name',
    		'label' => _("Nome"),
    		'required' => true,
    		'max_lenght' => 255,
    	));
    	$columns['showcase'] = new \Gino\BooleanField(array(
    		'name' => 'showcase',
    		'label' => _('Disponibile per vista showcase'),
    		'required' => true,
    	));
    	$columns['slideshow'] = new \Gino\BooleanField(array(
    		'name' => 'slideshow',
    		'label' => _('Disponibile per vista slideshow'),
    		'required' => true,
    	));
    	
    	return $columns;
    }

    /**
     * @brief Url media della categoria
     * @return url
     */
    public function getUrl() {
        return $this->_controller->link($this->_controller, 'category', array('id' => $this->id));
    }

    /**
     * @brief Immagini della categoria
     * @return array di oggetti @ref Gino.App.Gallery.Image
     */
    public function getImages() {
        return Image::objects(null, array('where' => "category='".$this->id."'"));
    }

}
Category::$columns=Category::columns();