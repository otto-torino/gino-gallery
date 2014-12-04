<?php
/**
 * @file class.Category.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gallery.Category
 *
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Gallery;

use \Gino\BooleanField;

/**
 * @brief Classe tipo Gino.Model che rappresenta una categoria di media
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Category extends \Gino\Model {

    public static $table = 'gallery_category';

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Gallery.Category
     */
    function __construct($id) {

        $this->_controller = new gallery();
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name'=>_("Nome"),
            'showcase'=>_("Disponibile per vista showcase"),
        );

        parent::__construct($id);

        $this->_model_label = _('Categoria');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome categoria
     */
    function __toString() {
        return (string) $this->ml('name');
    }

    /**
     * @brief Sovrascrive la struttura di default
     *
     * @see Gino.Model::structure()
     * @param integer $id
     * @return array, struttura
     */
    public function structure($id) {

        $structure = parent::structure($id);

        $structure['showcase'] = new BooleanField(array(
            'name'=>'showcase',
            'model'=>$this,
            'enum'=>array(1 => _('si'), 0 => _('no')), 
        ));

        return $structure;
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
