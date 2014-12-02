<?php
/**
 * \file class.Video.php
 * @brief Contiene la definizione ed implementazione della classe Video.
 * 
 * @version 0.1.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Gallery;

use \Gino\ImageField;
use \Gino\ForeignKeyField;
use \Gino\EnumField;
use \Gino\GImage;

/**
 * \ingroup gino-gallery
 * Classe tipo model che rappresenta un media video.
 *
 * @version 0.1.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class Video extends \Gino\Model {

    public static $table = 'gallery_video';
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
            'platform'=>_("Piattaforma"),
            'name'=>_("Nome"),
            'description'=>_("Descrizione"),
            'code'=>_("Codice"),
            'width'=>_("Larghezza (px)"),
            'height'=>_("Lunghezza (px)"),
            'thumb'=>_("Thumbnail"),
        );

        parent::__construct($id);

        $this->_model_label = _('Video');
    }

    function __toString() {
        return (string) $this->ml('name');
    }

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
            'add_related_url' => $this->_home.'?evt['.$this->_controller->getInstanceName().'-manageGallery]&block=ctg&insert=1',
        ));

        $structure['platform'] = new EnumField(array(
            'name'=>'platform', 
            'model'=>$this,
            'enum'=>array(1 => 'youtube', 2 => 'vimeo')
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

    /**
     * @brief Path relativo della thumb
     * @description se non vengono fornite dimensioni viene considerata la thumb originale, altrimenti la thumb viene creata al volo dalla classe @ref GImage
     * @param int $w larghezza thumb se creata al volo
     * @param int $h altezza thumb se creata al volo
     * @return path
     */
    public function thumbPath($w = null, $h = null) {
        if(!($w and $h)) {
            return CONTENT_WWW.'/gallery/thumb/'.$this->thumb;
        }
        else {
            $image = new GImage(\Gino\absolutePath(CONTENT_WWW.'/gallery/thumb/'.$this->thumb));
            $thumb = $image->thumb($w, $h);
            return $thumb->getPath();
        }

    }

    /**
     * @brief Salva il modello su db
     * @description Il metodo estende quello della classe @ref Model per eseguire il download della thumb direttamente da youtube o vimeo
     * @return TRUE
     */
    public function updateDbData() {
        parent::updateDbData();

        if(!$this->thumb and function_exists('curl_version')) {
            if($this->platform == 1) {
                $url = "http://img.youtube.com/vi/".$this->code."/hqdefault.jpg";
                $this->grabImage($url, CONTENT_DIR.OS.'gallery'.OS.'thumb'.OS.'thumb_video_'.$this->id.'.jpg');
                $this->thumb = 'thumb_video_'.$this->id.'.jpg';
                parent::updateDbData();
            }
            else {
                $info_url = "http://vimeo.com/api/v2/video/".$this->code.".php";
                $ch = curl_init ($info_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                $raw = curl_exec($ch);
                curl_close ($ch);
                $hash = unserialize($raw);
                $url = $hash[0]['thumbnail_large'];
                $this->grabImage($url, CONTENT_DIR.OS.'gallery'.OS.'thumb'.OS.'thumb_video_'.$this->id.'.jpg');
                $this->thumb = 'thumb_video_'.$this->id.'.jpg';
                parent::updateDbData();
            }
        }

        return true;
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
