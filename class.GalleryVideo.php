<?php

class GalleryVideo extends Model {

    private $_controller;

    public static $table = 'gallery_video';
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

    public static function get($options = null) {

        $res = array();

        $where = gOpt('where', $options, '');
        $order = gOpt('order', $options, 'name');
        $limit = gOpt('limit', $options, null);

        $db = db::instance();
        $selection = 'id';
        $table = self::$tbl_video;

        $rows = $db->select($selection, $table, $where, array('order'=>$order, 'limit'=>$limit));
        if($rows and count($rows)) {
            foreach($rows as $row) {
                $res[] = new GalleryVideo($row['id']);
            }
        }

        return $res;

    }

    public function thumbPath($w = null, $h = null) {
        if(!($w and $h)) {
            return CONTENT_WWW.'/gallery/thumb/'.$this->thumb;
        }
        else {
            $image = new GImage(absolutePath(CONTENT_WWW.'/gallery/thumb/'.$this->thumb));
            $thumb = $image->thumb($w, $h);
            return $thumb->getPath();
        }

    }

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
