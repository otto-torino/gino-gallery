<?php
/**
 * @file class_gallery.php
 * @brief Contiene la definizione ed implementazione della classe \ref news.
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

namespace Gino\App\Gallery;

use \Gino\View;
use \Gino\Document;
use \Gino\Loader;
use \Gino\AdminTable;

/** \mainpage Caratteristiche e output disponibili per i template e le voci di menu
 *
 * CARATTERISTICHE
 *
 * Modulo di gestione gallerie di immagini e video
 *
 * OUTPUTS
 * - box di presentazione per home page
 * - showcase position fixed su sfondo
 * - Navigazione elementi multimediali di una categoria
 */
require_once('class.Image.php');
require_once('class.Category.php');
require_once('class.Video.php');

/**
 * @defgroup gino-gallery
 * Modulo di gestione gallerie multimediali
 *
 * Il modulo contiene anche dei css, javascript e file di configurazione.
 *
 */

/**
 * \ingroup gino-gallery
 * Classe per la gestione gallerie di elementi multimediali, immagini e video.
 *
 * Gli output disponibili sono:
 *
 * - box di presentazione per home page
 * - showcase position fixed su sfondo
 * - Navigazione elementi multimediali di una categoria
 * 
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class gallery extends \Gino\Controller {

    /**
     * @brief Costruttore
     * @return nuova istanza
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * @brief Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
     * @return lista delle proprietà utilizzate per la creazione di istanze di tipo news
     */
    public static function getClassElements() {

        return array(
            "tables"=>array(
                'gallery_category', 
                'gallery_image', 
                'gallery_video', 
            ),
            "css"=>array(
                'gallery.css'
            ),
            'views' => array(
                'box.php' => _('Template per l\'inserimento della pagine nel layout'),
                'category.php' => _('Galleria immagini appartenenti a categoria'),
                'showcase.php' => _('Showcase fisso su sfondo'),
                'index.php' => _('Lista gallerie'),
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'gallery'=> array(
                    'img' => null,
                    'thumb' => null
                )
            )
        );
    }

    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end 
     * 
     * Questo metodo viene letto dal motore di generazione dei layout e dal motore di generazione di voci di menu
     * per presentare una lista di output associati all'istanza di classe. 
     * 
     * @access public
     * @return array[string]array
     */
    public static function outputFunctions() {

        $list = array(
            "box" => array("label"=>_("Box presentazione gallerie"), "permissions"=>array()),
            "showcase" => array("label"=>_("Showcase fisso su sfondo"), "permissions"=>array()),
            "index" => array("label"=>_("Lista gallerie"), "permissions"=>array()),
        );

        return $list;
    }
    
	/**
     * @brief Percorso della directory di un media a partire dal percorso base
     *
     * @param integer $id valore ID della pagina
     * @return percorso
     */
    public function getAddPath($id) {

        if(!$id)
            $id = $this->_db->autoIncValue(pageEntry::$table);

        $directory = $id.OS;

        return $directory;
    }

    /**
     * @brief Vista lista gallerie
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response lista gallerie
     */
    public function index(\Gino\Http\Request $request) {

        $this->_registry->addCss($this->_class_www."/gallery.css");

        $categories = Category::objects();

        foreach($categories as $c) {
        	
        	$images = Image::objects(null, array('where' => "category='".$c->id."'"));
            $videos = Video::objects(null, array('where' => "category='".$c->id."'"));
            if(count($images) or count($videos)) {
                $ctgs[] = array(
                    'name' => \Gino\htmlChars($c->name),
                    'description' => \Gino\htmlChars($c->description),
                    'items' => count($images) + count($videos),
                    'url' => $c->getUrl(),
                    'thumb' => isset($images[0]) ? $images[0]->thumbPath(200, 200) : $videos[0]->thumbPath(200, 200)
                );
            }
        }

        $view = new View($this->_view_dir);
        $view->setViewTpl('index');
        $dict = array(
            'section_id' => 'gallery-index',
            'title' => _('Gallerie'),
            'ctgs' => $ctgs,
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Vista box di presentazione home page
     * @return box di presentazione
     */
    public function box() {

        $this->_registry->addCss($this->_class_www."/gallery.css");

        $ctgs = array();

        $images = Image::objects();
        $videos = Video::objects();
        $categories = Category::objects();

        foreach($categories as $c) {
            $images = Image::objects(null, array('where' => "category='".$c->id."'", "limit" => array(0, 1)));
            if(count($images)) {
                $ctgs[] = array(
                    'name' => \Gino\htmlChars($c->name),
                    'url' => $c->getUrl(),
                    'cover' => $images[0]
                );
            }
        }

        $view = new View($this->_view_dir);
        $view->setViewTpl('box');
        $dict = array(
            'section_id' => 'gallery-box',
            'title' => _('Foto'),
            'ctgs' => $ctgs,
        );
        
        return $view->render($dict);
    }

    /**
     * @brief Vista vetrina galleria
     * @description Lo showcase viene renderizzato in position fixed sullo sfondo
     * @return showcase
     */
    public function showcase() {

        $this->_registry->addJs($this->_class_www."/gallery.js");
        $this->_registry->addCss($this->_class_www."/gallery.css");

        $view = new View($this->_view_dir);

        $ctgs = Category::objects(null, array('where' => "showcase='1'", 'order' => 'name ASC'));
        $active_ctg = $ctgs && count($ctgs) ? $ctgs[0] : null;
        
        $view->setViewTpl('showcase');
        $dict = array(
            'section_id' => 'gallery-showcase',
            'ctgs' => $ctgs,
            'active_ctg' => $active_ctg
        );

        return $view->render($dict);
    }

    /**
     * @brief Vista elementi multimediali appartenenti ad una categoria
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response vista elementi multimediali categoria
     */
    public function category(\Gino\Http\Request $request) {

        $this->_registry->addJs($this->_class_www."/moogallery.js");
        $this->_registry->addCss($this->_class_www."/gallery.css");
        $this->_registry->addCss($this->_class_www."/moogallery.css");

        $id = \Gino\cleanVar($request->GET, 'id', 'int', '');

        $category = new Category($id);
        $images = Image::objects(null, array(
            'where' => "category='".$category->id."'"
        ));
        $videos = Video::objects(null, array(
            'where' => "category='".$category->id."'"
        ));
        
        $view = new View($this->_view_dir);
        $view->setViewTpl('category');
        $dict = array(
            'section_id' => 'gallery-category',
            'title' => _('Foto'),
            'images' => $images,
            'videos' => $videos,
            'category' => $category
        );

        $document = new Document($view->render($dict));
        return $document();

    }

    /**
     * @brief Interfaccia di amministrazione modulo
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response interfaccia di back office
     */
    public function manageGallery(\Gino\Http\Request $request) {

        $this->requirePerm('can_admin');

        Loader::import('class', '\Gino\AdminTable');

        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');
        
        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_video = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=video'), _('Video'));
        $link_ctg = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=ctg'), _('Categorie'));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Immagini'));
        
        $sel_link = $link_dft;

        if($block == 'frontend') {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'video') {
            $backend = $this->manageVideo();
            $sel_link = $link_video;
        }
        elseif($block == 'ctg') {
            $backend = $this->manageCategory();
            $sel_link = $link_ctg;
        }
        else {
            $backend = $this->manageImage();
        }
        
   		if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        $view = new View();
        $view->setViewTpl('tab');
        $dict = array(
            'title' => _('Galleria immagini'),
            'links' => array($link_frontend, $link_ctg, $link_video, $link_dft),
            'selected_link' => $sel_link,
            'content' => $backend
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione immagini
     * @return interfaccia di amministrazione
     */
    private function manageImage() {

        $admin_table = new AdminTable($this, array());

        $buffer = $admin_table->backOffice(
            'Image',
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

    /**
     * @brief Interfaccia di amministrazione video
     * @return interfaccia di amministrazione
     */
    private function manageVideo() {

        $admin_table = new AdminTable($this, array());

        if(function_exists('curl_version')) {
            $form_description = _('Le thumbnail dei video sono recuperate automaticamente da youtube/vimeo se non viene caricato un file nel campo thumb.');
            $thumb_required = false;
        }
        else {
            $form_description = _('Le thumbnail dei video non possono essere recuperate automaticamente da youtube/vimeo. Per abilitare tale funzionalità è necessario aggiungere il supporto alle funzioni curl del php.');
            $thumb_required = true;
        }

        $buffer = $admin_table->backOffice(
            'Video', 
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

    /**
     * @brief Interfaccia di amministrazione categorie
     * @return interfaccia di amministrazione
     */
    private function manageCategory() {

        $admin_table = new AdminTable($this, array());

        $buffer = $admin_table->backOffice(
            'Category', 
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
