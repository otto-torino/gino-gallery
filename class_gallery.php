<?php
/**
 * @file class_gallery.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.Gallery.gallery
 *
 * @copyright 2014-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @namespace Gino.App.Gallery
 * @description Namespace dell'applicazione Gallery
 */
namespace Gino\App\Gallery;

use \Gino\View;
use \Gino\Document;
use \Gino\Loader;
use \Gino\AdminTable;

require_once('class.Image.php');
require_once('class.Category.php');
require_once('class.Video.php');

/**
 * \ingroup gallery
 * @brief Classe di tipo Gino.Controller per la gestione gallerie di elementi multimediali, immagini e video
 *
 * @version 1.2.0
 * @copyright 2014-2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##OUTPUTS
 * - @a box: box di presentazione per home page (view)
 * - @a showcase: showcase position fixed su sfondo (view)
 * - @a index: lista gallerie (page)
 * - @a slideshow: slideshow (view)
 * - @a slideshowNavbar: slideshow con controlli di navigazione (view slideshow_nav)
 * - @a category: navigazione elementi multimediali di una categoria (page)
 * 
 * ##PERMESSI
 * L'applicazione prevede il seguente livello di permesso: \n
 * - can_admin: amministrazione completa del modulo
 * 
 * ##SHOWCASE
 * Lo showcase mostra uno slideshow di immagini sullo sfondo. Per poter funzionare deve essere richiamato all'interno del template come ultimo elemento, 
 * dopo il tag di chiusura del footer e prima di quello di chiusura del body. \n
 * La visualizzazione di questo particolare slideshow necessita dell'impostazione di almeno una galleria come disponibile per la vista showcase.
 * 
 * ##SLIDESHOW
 * Sono presenti due tipologie di slideshow: \n
 * 1. slideshow automatizzato con bottoni avanti/indietro
 * 2. slideshow con controlli di navigazione e preview immagini
 * 
 * La versione 1 viene richiamata con @a slideshow() ed ha come vista @a slideshow.php. I file di riferimento sono slideshow.css e gallery.js. \n
 * La versione 2 viene richiamata con @a slideshowNavbar() ed ha come vista @a slideshow_nav.php. I file di riferimento sono slideshow_nav.css e slideshow_nav.js.
 * 
 * Lo slideshow può essere associato a una galleria specifica oppure a una galleria impostata come disponibile per la vista slideshow nell'interfaccia amministrativa. \n
 * Nel caso in cui siano state impostate più gallerie per lo slideshow, viene visualizzata quella inserita più recentemente.
 */
class gallery extends \Gino\Controller {

    /**
     * @brief Costruttore
     * @return istanza di Gino.App.Gallery.gallery
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * @brief Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
     * @return lista delle proprietà dell'applicazione (tabelle, css, viste, cartelle)
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
                'slideshow.php' => _('Slideshow'),
            	'slideshow_nav.php' => _('Slideshow con controlli di navigazione'),
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
     * @brief Metodi pubblici disponibili per inserimento in layout (non presenti nel file events.ini) e menu (presenti nel file events.ini)
     * @return lista metodi NOME_METODO => array('label' => LABEL, 'permissions' = PERMISSIONS)
     */
    public static function outputFunctions() {

        $list = array(
            "box" => array("label"=>_("Box presentazione gallerie"), "permissions"=>array()),
            "showcase" => array("label"=>_("Showcase fisso su sfondo"), "permissions"=>array()),
            "slideshow" => array("label"=>_("Slideshow"), "permissions"=>array()),
        	"slideshowNavbar" => array("label"=>_("Slideshow con controlli di navigazione"), "permissions"=>array()),
            "index" => array("label"=>_("Lista gallerie"), "permissions"=>array()),
        );

        return $list;
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
     * @return html, box di presentazione
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
     * @brief Imposta la galleria da mostrare nelle slideshow
     * @param integer $ctg_id valore id della galleria
     * @throws \Gino\Exception\Exception404
     * @return object or null
     */
    private function setCtgForSlideshow($ctg_id) {
    	
    	if($ctg_id) {
    		$category = new Category($ctg_id);
    	}
    	else {
    		$categories = Category::objects(null, array('where' => 'slideshow=\'1\'', 'order' => 'id DESC', 'limit' => array(0, 1)));
    		if(!$categories) {
    			return null;
    		}
    		else {
    			$category = $categories[0];
    		}
    	}
    	
    	if(!$category->id or !$category->slideshow) {
    		throw new \Gino\Exception\Exception404();
    	}
    	else {
    		return $category;
    	}
    }

    /**
     * @brief Vista slideshow
     * @param integer $ctg_id valore id della galleria
     * @return html
     */
    public function slideshow($ctg_id = null) {
        
        $category = $this->setCtgForSlideshow($ctg_id);
        if(is_null($category)) {
        	return null;
        }
        
        $this->_registry->addJs($this->_class_www."/gallery.js");
        $this->_registry->addCss($this->_class_www."/slideshow.css");

        $view = new View($this->_view_dir);
        $view->setViewTpl('slideshow');
        $dict = array(
            'section_id' => 'gallery-slideshow',
            'category' => $category
        );

        return $view->render($dict);
    }
    
    /**
     * @brief Vista slideshow con controlli di navigazione
     * @param integer $ctg_id valore id della galleria
     * @return html
     */
    public function slideshowNavbar($ctg_id = null) {
    
    	$category = $this->setCtgForSlideshow($ctg_id);
        if(is_null($category)) {
        	return null;
        }
    
    	$this->_registry->addJs($this->_class_www."/slideshow_nav.js");
    	$this->_registry->addCss($this->_class_www."/slideshow_nav.css");
    
    	$view = new View($this->_view_dir);
    	$view->setViewTpl('slideshow_nav');
    	$dict = array(
    			'section_id' => 'gallery-slideshow-nav',
    			'category' => $category
    	);
    	
    	return $view->render($dict);
    }

    /**
     * @brief Vista vetrina galleria
     * @description Lo showcase viene renderizzato in position fixed sullo sfondo
     * @return html, showcase
     */
    public function showcase() {

        $this->_registry->addJs($this->_class_www."/gallery.js");
        $this->_registry->addCss($this->_class_www."/showcase.css");

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
        $link_ctg = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=ctg'), _('Gallerie'));
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

        $view = new View(null, 'tab');
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
     * @return Gino.Http.Redirect oppure html, interfaccia di amministrazione
     */
    private function manageImage() {

        $admin_table = new AdminTable($this, array());

        $backend = $admin_table->backOffice(
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

        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione video
     * @return Gino.Http.Redirect oppure html, interfaccia di amministrazione
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

        $backend = $admin_table->backOffice(
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

        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione categorie
     * @return Gino.Http.Redirect oppure html, interfaccia di amministrazione
     */
    private function manageCategory() {

        $admin_table = new AdminTable($this, array());

        $buffer = $admin_table->backOffice(
            'Category',
            array(
                'list_display' => array('id', 'name', 'showcase', 'slideshow'),
                'list_title' => _("Elenco gallerie"), 
                'list_description' => "<p>"._('Ciascuna immagine inserita dovrà essere associata ad una categoria qui definita.')."</p>" .
                                      "<p>"._('L\'eliminazione di una categoria NON comporta l\'eliminazione di tutte le immagini associate!')."</p>"
            ),
            array(
            ),
            array()
        );

        return $buffer;
    }
}
