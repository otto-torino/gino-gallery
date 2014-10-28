<?php

require_once('class.GalleryImage.php');
require_once('class.GalleryCategory.php');
require_once('class.GalleryVideo.php');

class gallery extends Controller {

    function __construct() {
        parent::__construct();
    }

    public static function getClassElements() {

        return array(
            "tables"=>array(
                'gallery_category', 
                'gallery_image', 
            ),
            "css"=>array(
                'gallery.css'
            ),
            'views' => array(
                'box.php' => _('Template per l\'inserimento della pagine nel layout'),
                'category.php' => _('Galleria immagini appartenenti a categoria'),
                'showcase.php' => _('Showcase fisso su sfondo'),
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'gallery'=> null
            )
        );
    }

    /**
     * Definizione dei metodi pubblici che forniscono un output per il front-end 
     * 
     * Questo metodo viene letto dal motore di generazione dei layout e dal motore di generazione di voci di menu
     * per presentare una lista di output associati all'istanza di classe. 
     * 
     * @static
     * @access public
     * @return array[string]array
     */
    public static function outputFunctions() {

        $list = array(
            "box" => array("label"=>_("Box presentazione gallerie"), "permissions"=>array()),
            "showcase" => array("label"=>_("Showcase fisso su sfondo"), "permissions"=>array()),
        );

        return $list;
    }

    public function box() {

        $this->_registry->addCss($this->_class_www."/gallery.css");

        $view = new view($this->_view_dir);

        $view->setViewTpl('box');

        $ctgs = array();

        $images = GalleryImage::objects();
        $videos = GalleryVideo::objects();
        $categories = GalleryCategory::objects();

        foreach($categories as $c) {
                $images = GalleryImage::objects(null, array('where' => "category='".$c->id."'", "limit" => array(0, 1)));
                if(count($images)) {
                        $ctgs[] = array(
                                'name' => htmlChars($c->name),
                                'url' => $c->getUrl(),
                                'cover' => $images[0]
                        );
                }
        }

        $dict = array(
            'section_id' => 'gallery-box',
            'title' => _('Foto'),
            'ctgs' => $ctgs,
        );

        return $view->render($dict);
    }

    public function showcase() {

        $this->_registry->addJs($this->_class_www."/gallery.js");
        $this->_registry->addCss($this->_class_www."/gallery.css");

        $view = new view($this->_view_dir);

        $ctgs = GalleryCategory::objects(null, array('where' => "showcase='1'", 'order' => 'name ASC'));
        $active_ctg = count($ctgs and $ctgs) ? $ctgs[0] : null;

        $view->setViewTpl('showcase');
        $dict = array(
            'section_id' => 'gallery-showcase',
            'ctgs' => $ctgs,
            'active_ctg' => $active_ctg
        );

        return $view->render($dict);
    }

    public function category() {

        $this->_registry->addJs($this->_class_www."/moogallery.js");
        $this->_registry->addCss($this->_class_www."/gallery.css");
        $this->_registry->addCss($this->_class_www."/moogallery.css");

        $view = new view($this->_view_dir);

        $id = cleanVar($_GET, 'id', 'int', '');

        $view->setViewTpl('category');

        $category = new GalleryCategory($id);
        $images = GalleryImage::objects(null, array(
            'where' => "category='".$category->id."'"
        ));
        $videos = GalleryVideo::objects(null, array(
            'where' => "category='".$category->id."'"
        ));
        $dict = array(
            'section_id' => 'gallery-category',
            'title' => _('Foto'),
            'images' => $images,
            'videos' => $videos,
            'category' => $category
        );

        return $view->render($dict);

    }

    public function manageGallery() {

        $this->requirePerm('can_admin');

        loader::import('class', 'AdminTable');

        $block = cleanVar($_GET, 'block', 'string', '');

        $link_frontend = "<a href=\"".$this->_home."?evt[$this->_instance_name-manageGallery]&block=frontend\">"._("Frontend")."</a>";
        $link_ctg = "<a href=\"".$this->_home."?evt[$this->_instance_name-manageGallery]&block=ctg\">"._("Categorie")."</a>";
        $link_video = "<a href=\"".$this->_home."?evt[$this->_instance_name-manageGallery]&block=video\">"._("Video")."</a>";
        $link_dft = "<a href=\"".$this->_home."?evt[".$this->_instance_name."-manageGallery]\">"._("Immagini")."</a>";
        $sel_link = $link_dft;

        if($block == 'frontend') {
            $buffer = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'video') {
            $buffer = $this->manageVideo();
            $sel_link = $link_video;
        }
        elseif($block == 'ctg') {
            $buffer = $this->manageCategory();
            $sel_link = $link_ctg;
        }
        else {
            $buffer = $this->manageImage();
        }

        $dict = array(
            'title' => _('Galleria immagini'),
            'links' => array($link_frontend, $link_ctg, $link_video, $link_dft),
            'selected_link' => $sel_link,
            'content' => $buffer
        );

        $view = new view();
        $view->setViewTpl('tab');

        return $view->render($dict);

    
    }

    private function manageImage() {

        $admin_table = new adminTable($this, array());

        $buffer = $admin_table->backOffice(
            'galleryImage', 
            array(
                'list_display' => array('id', 'category', 'name',),
                'list_title' => _("Elenco immagini"), 
                'filter_fields' => array('category')
            ),
            array(
            ),
            array()
        );

        return $buffer;
    }

    private function manageVideo() {

        $admin_table = new adminTable($this, array());

        if(function_exists('curl_version')) {
            $form_description = _('Le thumbnail dei video sono recuperate automaticamente da youtube/vimeo se non viene caricato un file nel campo thumb.');
            $thumb_required = false;
        }
        else {
            $form_description = _('Le thumbnail dei video non possono essere recuperate automaticamente da youtube/vimeo. Per abilitare tale funzionalità è necessario aggiungere il supporto alle funzioni curl del php.');
            $thumb_required = true;
        }

        $buffer = $admin_table->backOffice(
            'galleryVideo', 
            array(
                'list_display' => array('id', 'category', 'name',),
                'list_title' => _("Elenco video"), 
                'filter_fields' => array('category', 'platform')
            ),
            array(
                'form_description' => "<p>".$form_description."</p>"
            ),
            array(
                'thumb' => array(
                    'required' => $thumb_required
                )
            )
        );

        return $buffer;
    }

    private function manageCategory() {

        $admin_table = new adminTable($this, array());

        $buffer = $admin_table->backOffice(
            'galleryCategory', 
            array(
                'list_display' => array('id', 'name', 'showcase'),
                'list_title' => _("Elenco categorie"), 
                'list_description' => "<p>"._('Ciascuna immagine inserita dovrà essere associato ad una categoria qui definita.')."</p>" .
                                                            "<p>"._('L\'eliminazione di una categoria NON comporta l\'eliminazione di tutte le immagini associate!')."</p>"
            ),
            array(
            ),
            array()
        );

        return $buffer;

    }
}
