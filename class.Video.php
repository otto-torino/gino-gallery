<?php
/**
 * @file class.Video.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gallery.Video.
 *
 * @copyright 2014-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Gallery;

use \Gino\ImageField;
use \Gino\ForeignKeyField;
use \Gino\EnumField;
use \Gino\GImage;

/**
 * \ingroup gallery
 * @brief Classe tipo Gino.Model che rappresenta un media video
 *
 * @version 1.2.0
 * @copyright 2014-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Video extends \Gino\Model {

    public static $table = 'gallery_video';
    public static $columns;
    
    protected static $_extension_img = array('jpg', 'jpeg', 'png');

    /**
     * Costruttore
     *
     * @param integer $id valore ID del record
     * @return istanza di Gino.App.Gallery.Video
     */
    function __construct($id) {

        $this->_controller = new gallery();
        $this->_tbl_data = self::$table;

        parent::__construct($id);

        $this->_model_label = _('Video');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     * @return nome video
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
        $columns['platform'] = new \Gino\BooleanField(array(
        	'name' => 'platform',
        	'label' => _('Piattaforma'),
        	'required' => true,
        	'choice' => array(1 => 'youtube', 2 => 'vimeo')
        ));
        $columns['name'] = new \Gino\CharField(array(
        	'name' => 'name',
        	'label' => _("Nome"),
        	'required' => true,
        	'max_lenght' => 255,
        ));
        $columns['description'] = new \Gino\TextField(array(
        	'name' => 'description',
        	'label' => _("Descrizione"),
        	'required' => false
        ));
        $columns['code'] = new \Gino\CharField(array(
        	'name' => 'code',
        	'label' => _("Codice"),
        	'required' => true,
        	'max_lenght' => 255,
        ));
        $columns['width'] = new \Gino\IntegerField(array(
        	'name' => 'width',
        	'label' => _("Larghezza (px)"),
        	'required' => true,
        	'max_lenght' => 4,
        ));
        $columns['height'] = new \Gino\IntegerField(array(
        	'name' => 'height',
        	'label' => _("Lunghezza (px)"),
        	'required' => true,
        	'max_lenght' => 4,
        ));
        $columns['thumb'] = new ImageField(array(
            'name' => 'thumb',
            'label' => array(_("Thumbnail"), _("la thumbnail viene generata automaticamente nel caso in cui non venga inserita")),
            'max_lenght' => 255,
            'extensions' => self::$_extension_img,
            'resize' => false,
            'path' => null,
            'add_path' => null
        ));

        return $columns;
    }

    /**
     * @brief Path relativo della thumb
     * @description se non vengono fornite dimensioni viene considerata la thumb originale, altrimenti la thumb viene creata al volo dalla classe @ref GImage
     * @param int $w larghezza thumb se creata al volo
     * @param int $h altezza thumb se creata al volo
     * @return path
     */
    public function thumbPath($w = null, $h = null) {

        $relpath = $this->_controller->getBasePath().'/thumb/'.$this->thumb;

        if(!($w and $h)) {
            return $relpath;
        }
        else {
            $image = new GImage(\Gino\absolutePath($relpath));
            $thumb = $image->thumb($w, $h);
            return $thumb->getPath();
        }
    }

    /**
     * @brief Salva il modello su db
     * @see Gino.Model::save()
     * @description Il metodo estende quello della classe @ref Gino.Model per eseguire il download della thumb direttamente da youtube o vimeo
     * @return TRUE
     */
    public function save() {

        parent::save();

        if(!$this->thumb and function_exists('curl_version')) {

            $path_to_file = $this->_controller->getBaseAbsPath().OS.'thumb'.OS.'thumb_video_'.$this->id.'.jpg';

            if($this->platform == 1) {
                $url = "http://img.youtube.com/vi/".$this->code."/hqdefault.jpg";
                $this->grabImage($url, $path_to_file);
                $this->thumb = 'thumb_video_'.$this->id.'.jpg';
                parent::save();
            }
            else {
                $info_url = "http://vimeo.com/api/v2/video/".$this->code.".php";
                $ch = curl_init ($info_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                $raw = curl_exec($ch);
                curl_close ($ch);
                $hash = unserialize($raw);
                $url = $hash[0]['thumbnail_large'];
                $this->grabImage($url, $path_to_file);
                $this->thumb = 'thumb_video_'.$this->id.'.jpg';
                parent::save();
            }
        }

        return TRUE;
    }

    /**
     * @brief Salva una immagine da url esterno
     * @param string $url url immagine
     * @param string $saveto percorso di destinazione
     * @return void
     */
    private function grabImage($url, $saveto){
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $raw=curl_exec($ch);
        curl_close ($ch);
        if(file_exists($saveto)){
            unlink($saveto);
        }
        $fp = fopen($saveto,'x');
        fwrite($fp, $raw);
        fclose($fp);
    }

}
Video::$columns=Video::columns();