var gallery = gallery || {};

/**
 * @fn Showcase
 * @brief Costruttore della classe Showcase utilizzata dalla vista showcase
 * @param object $options opzioni:
 *   - dom_content: string, valore id del container
 *   - interval_period: int, intervallo animazione automatica
 *   - screen_min_width: int, valore in pixel della larghezza minima richiesta per visualizzare lo slideshow;
 *   				come impostazione predefinita lo slideshow è sempre visibile a qualsiasi larghezza (value null) 
 *   - screen_margin_min: int, valore in pixel del margine superiore del container (dom_content) per un viewport minore/uguale al valore di screen_min_width
 *   - screen_margin_max: int, valore in pixel del margine superiore del container (dom_content) per un viewport maggiore del valore di screen_min_width
 * @return istanza di Showcase
 * @version 1.0.0
 * @copyright 2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
gallery.Showcase = new Class({
    Implements: [Options, Events],
    options: {
        dom_content: null,
        interval_period: 4000,
        screen_min_width: null,
        screen_margin_min: null,
        screen_margin_max: null,
    },
    initialize: function(ctgs, options) {
        var self = this;
        this.setOptions(options);
        // dom elements
        this.dom = {
            content: $(self.options.dom_content)
        };
        this.first_onresize = false; // resize fired on load
        this.viewport = this.getViewport();
        
        this.ctgs = ctgs;
        this.active_ctg_index = 0;
        // init component
        this.dom.list = new Element('ul.gallery-showcase')
            .inject(document.body, 'bottom');
        this.init();
        
        this.addEvent('imageloaded', this.onImageLoaded.bind(this));
        this.addEvent('imageloadingcomplete', this.onImageLoadingComplete.bind(this));
        // check window resize
        window.addEvent('resize', this.onResize.bind(this));
    },
    init: function() {
        this.updating = false;
        this.images = this.ctgs[this.active_ctg_index].images;
        this.spinner = new Spinner(this.dom.list, {
            'class': 'gallery-spinner-container'
        });
        this.setContentMargin();
        this.spinner.show();
        // load first image and asequentially all others
        this.loadImage(0, {css: 'slide-active'});
    },
    update: function() {
        this.updating = true;
        this.dom.list.empty();
        this.dom.loading_bar.addClass('no-transition').setStyle('width', '0');
        this.clearAnimation();
        this.images = this.ctgs[this.active_ctg_index].images;
        this.spinner = new Spinner(this.dom.list, {
            'class': 'gallery-spinner-container'
        });
        this.loadImage(0, {css: 'slide-active'});
    },
    
    setContentMargin: function() {
        if(typeOf(this.dom.content) == 'element') {
            this.dom.content.setStyle('margin-top', (this.viewport.height - 150) + 'px');
            
            if(window.location.pathname != '' && window.location.pathname != '#') {
            	window.scroll(0, Math.abs(this.viewport.height / 2));
            }
        }
    },
    responsive: function() {
    	var self = this;
    	if(typeof self.options.screen_min_width == 'number') {
    		
    		if(self.viewport.width > self.options.screen_min_width) {
    			self.dom.list.setStyle('display', 'block');
    			self.dom.loading_bar_bkg.setStyle('display', 'block');
    			self.dom.prev_button.setStyle('display', 'block');
    			self.dom.next_button.setStyle('display', 'block');
    			
    			if(typeof self.options.screen_margin_max == 'number') {
    				$(self.options.dom_content).setStyle('margin-top', self.options.screen_margin_max);
    			}
    		}
    		else {
    			self.dom.list.setStyle('display', 'none');
    			self.dom.loading_bar_bkg.setStyle('display', 'none');
    			self.dom.prev_button.setStyle('display', 'none');
    			self.dom.next_button.setStyle('display', 'none');
    			
    			if(typeof self.options.screen_margin_min == 'number') {
    				$(self.options.dom_content).setStyle('margin-top', self.options.screen_margin_min);
    			}
    		}
    	}
    },
    loadImage: function(index, options) {
        var self = this;
        var image = new Element('img').setStyle('visibility', 'hidden');
        image.onload = function() {
            var li = new Element('li.' + (typeof options != 'undefined' && options.css ? options.css : ''))
                .adopt(image, new Element('div.caption').set('text', self.images[index].caption))
                .inject(self.dom.list)
            self.sizeImage(image);
            self.spinner.hide();
            self.fireEvent('imageloaded', index);
        };
        image.src = this.images[index].path;
        this.images[index].dom = image;
    },
    sizeImage: function(image) {
        var img_dim = image.getCoordinates();
        var ratio = img_dim.width / img_dim.height;
        if(this.viewport.ratio < ratio) {
            var img_height = this.viewport.height;
            var img_width = ratio * img_height;
            var left_shift = -1 * (img_width - this.viewport.width) / 2;
            var top_shift = 0;
        }
        else {
            var img_width = this.viewport.width;
            var img_height = img_width / ratio;
            var top_shift = -1 * (img_height - this.viewport.height) / 2;
            var left_shift = 0;
        }
        image.setStyles({ width: img_width + 'px', height: img_height + 'px', visibility: 'visible', top: top_shift + 'px', left: left_shift + 'px' });
    },
    onResize: function() {
    	if(this.first_onresize) {
            var self = this;
            this.viewport = this.getViewport();
            this.setContentMargin();
            this.images.each(function(image) { self.sizeImage(image.dom) });
            this.responsive();
        }
        this.first_onresize = true;
    },
    onImageLoaded: function(index) {
        if(index == this.images.length -1) {
            this.fireEvent('imageloadingcomplete');
        }
        else {
            var options = index == this.images.length -2 ? {css: 'slide-prev'} : {};
            this.loadImage(++index, options);
        }
    },
    onImageLoadingComplete: function() {
        if(!this.updating) {
            this.renderLoadingBar();
            this.addNavigationButtons();
            this.addCategoryNavigation();
            var self = this;
        }
        this.addCategoryListItems();
        this.responsive();
        this.setAnimation();
    },
    addCategoryNavigation: function() {
        var self = this;
        this.dom.category_button = new Element('div.gallery-category-button')
            .addEvent('click', function() {
                self.dom.ctg_list.style.display = self.dom.ctg_list.style.display == 'block' ? 'none' : 'block';
            })
            .inject(document.body)
            .adopt(this.dom.ctg_list = new Element('div.gallery-showcase-ctg-list'));
    },
    addCategoryListItems: function() {
        var self = this;
        this.dom.ctg_list.empty();
        var items = [];
        this.ctgs.each(function(ctg, index) {
            var item = new Element('li.' + (index == self.active_ctg_index ? 'active' : ''));
            if(index != self.active_ctg_index) {
                (function(_index) {
                    item.adopt(new Element('span')
                        .set('text', ctg.name))
                        .addEvent('click', function() {
                            self.active_ctg_index = _index;
                            self.update();
                        });
                })(index)
            }
            else {
                item.adopt(new Element('span').set('text', ctg.name));
            }
            items.push(item);
        })
        this.dom.ctg_list.adopt(items);

    },
    setAnimation: function() {
        var self = this;
        this.dom.loading_bar.removeClass('no-transition');
        setTimeout(function() { self.dom.loading_bar.setStyle('width', '100%'); }, 100);
        this.interval = setInterval(this.slide.bind(this, 'next'), this.options.interval_period);
    },
    clearAnimation: function() {
        clearInterval(this.interval);
    },
    renderLoadingBar: function() {
        this.dom.loading_bar_bkg = new Element('div.gallery-showcase-loading-bar-bkg')
            .adopt(this.dom.loading_bar = new Element('div.loading-bar').setStyles({
                width: 0,
                transition: 'all ' + this.options.interval_period/1000 + 's ease'
            }))
            .inject(document.body);
    },
    addNavigationButtons: function() {
        this.dom.prev_button = new Element('div.gallery-showcase-button.prev')
            .addEvent('click', function() { this.clearAnimation(); this.slide('prev'); this.setAnimation(); }.bind(this))
            .inject(document.body);
        this.dom.next_button = new Element('div.gallery-showcase-button.next')
            .addEvent('click', function() { this.clearAnimation(); this.slide('next'); this.setAnimation(); }.bind(this))
            .inject(document.body);
    },
    slide: function(dir) {
        var self = this;
        this.dom.loading_bar.addClass('no-transition').setStyle('width', '0');
        setTimeout(function() { self.dom.loading_bar.removeClass('no-transition').setStyle('width', '100%'); }, 100);
        var active = $$('.gallery-showcase .slide-active')[0];
        var next = active.getNext('li') || $$('.gallery-showcase li')[0];
        var prev = active.getPrevious('li') || $$('.gallery-showcase li:last-child')[0];
        if(dir == 'next') {
            next.addClass('slide-active');
            prev.removeClass('slide-prev');
            active.removeClass('slide-active').addClass('slide-prev');
        }
        else if(dir == 'prev') {
            prev.addClass('slide-active');
            prev.removeClass('slide-prev');
            active.removeClass('slide-active');
            var prev_prev = prev.getPrevious('li') || $$('.gallery-showcase li:last-child')[0];
            prev_prev.addClass('slide-prev');
        }
    },
    getViewport: function() {

        var width, height, left, top, cX, cY;

        // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
        if(typeof window.innerWidth != 'undefined') {
            width = window.innerWidth,
            height = window.innerHeight
        }
        // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
        else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth !='undefined' && document.documentElement.clientWidth != 0) {
            width = document.documentElement.clientWidth,
            height = document.documentElement.clientHeight
        }

        top = typeof self.pageYOffset != 'undefined'
            ? self.pageYOffset 
            : (document.documentElement && typeof document.documentElement.scrollTop != 'undefined')
                ? document.documentElement.scrollTop
                : document.body.clientHeight;

        left = typeof self.pageXOffset
            ? self.pageXOffset
            : (document.documentElement && typeof document.documentElement.scrollTop != 'undefined')
                ? document.documentElement.scrollLeft
                : document.body.clientWidth;

            cX = left + width/2;
            cY = top + height/2;

        return {'width':width, 'height':height, 'left':left, 'top':top, 'cX':cX, 'cY':cY, 'ratio': width/height };

    },
});

/**
 * @fn Slideshow
 * @brief Funzione Slideshow utilizzata dalla vista slideshow
 * 
 * @version 1.0.0
 * @copyright 2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##NOTE
 * Per modificare gli stili delle frecce avanti/indietro modificare i riferimenti css nella funzione renderNavbar(); 
 * ad esempio fa-arrow-left al posto di fa-chevron-left. \n
 * Modificare, se necessario gli stili css nel file slideshow.css: nel caso delle frecce slideshow-prev, slideshow-next, slideshow-index.
 */
gallery.Slideshow = function() {
    this.init = function(container, slides, slide_width, slide_height, media_root) {
        this._container = document.id(container)
            .addClass('slideshow');
        this._slides = slides;
        this._current = 0;
        this._media_root = media_root;
        this._or_slide_width = slide_width;
        this._or_slide_height = slide_height;
        this.bootstrap();
        var self = this;
        
        window.addEvent('resize', function() {
            if(self._resize_to) {
                clearTimeout(self._resize_to);
            }
            self._resize_to = setTimeout(function() {
                self._container.empty();
                self.bootstrap();
            }, 500);
        });
        setInterval(function() {
            self.shift('next');
        }, 5000);
    };
    
    this.bootstrap = function() {
        this.render();
        this.events();
    };
    
    this.render = function() {
        this._container.setStyles({
            'width': this._or_slide_width + 'px',
            'max-width': '100%'
        });
        this.responsive();
        this.renderSpot();
        this.renderRail();
        this.renderNavbar();
        this.renderSlides();
    };
    
    this.responsive = function() {
        var real_width = this._container.getCoordinates().width.toInt() - this._container.getStyle('padding-left').toInt() - this._container.getStyle('padding-right').toInt();
    	// - this._container.getStyle('border-left-width').toInt() - this._container.getStyle('border-right-width').toInt()	// if not defined in IE return 'medium'
    	var real_height = real_width / this._or_slide_width.toInt() * this._or_slide_height.toInt();
        this._slide_width = real_width;
        this._slide_height = real_height;
        this._container.setStyles({
            'width': this._slider_width + 'px'
        });
    }
    
    this.renderSpot = function() {
        this._spot = new Element('div.slideshow-spot')
            .setStyles({
                width: '100%',
                height: this._slide_height + 'px'
            })
            .inject(this._container);
    }
    
    this.renderRail = function() {
        this._rail = new Element('div.slideshow-rail')
            .setStyles({
                width: (this._slide_width * this._slides.length) + 'px',
                height: this._slide_height + 'px'
            })
            .inject(this._spot);
    }
    
    this.renderNavbar = function() {
        this._navbar = new Element('div.slideshow-navbar')
            .adopt(
                this._nav_prev = new Element('span.fa.fa-2x.fa-chevron-left.slideshow-prev.inactive'),
                this._dot_nav = new Element('span.slideshow-dotnav'),
                this._nav_index = new Element('span.fa.fa-2x.fa-bars.slideshow-index'),
                this._nav_next = new Element('span.fa.fa-2x.fa-chevron-right.slideshow-next')
            )
            .inject(this._container);
        
            this._navbar_pane = new Element('div.slideshow-navbar-pane')
            .setStyles({
                'width': this._spot.getCoordinates().width + 'px',
                bottom: (this._container.getCoordinates().height - this._spot.getCoordinates(this._container).bottom) + 'px'
            })
            .inject(this._container);
    };
    
    this.renderSlides = function() {
        var self = this;
        this._slides.each(function(slide, index) {
            var dot = new Element('span.slideshow-dotnav-dot' + (index == 0 ? '.active' : ''))
                .setProperty('data-index', index)
                .inject(self._dot_nav);
            var pane_el = new Element('img.slideshow-thumb' + (index == 0 ? '.active' : '') + '[src=' + self._media_root + slide.img + ']')
                .setProperty('data-index', index)
                .inject(self._navbar_pane);
            
            if(index == self._slides.length - 1) {
                pane_el.onload = function() {    
                    self._navbar_pane.store('height', self._navbar_pane.getCoordinates().height)
            .setStyle('height', 0);
                };
            }
                
            var article = new Element('article.slideshow-slide')
                .setStyles({
                    width: self._slide_width + 'px',
                    height: self._slide_height + 'px',
                    'background-image': 'url(' + self._media_root + slide.img + ')',
                    'background-repeat': 'no-repeat',
                    'background-position': 'center center',
                    'background-size': 'cover'
                })
                .adopt(new Element('div.slideshow-caption')
                    .set('html', slide.caption)            
                )
                .inject(self._rail);
        });
    } 
    
    this.events = function() {
        var self = this;
        this._nav_next.addEvent('click', this.shift.bind(this, 'next'));
        this._nav_prev.addEvent('click', this.shift.bind(this, 'prev'));
        this._nav_index.addEvent('click', this.toggleNavPane.bind(this));
        this._dot_nav.getElements('.slideshow-dotnav-dot').addEvent('click', function() {
            self.jump(this.get('data-index'));
        });
        this._navbar_pane.getElements('img').addEvent('click', function() {
            self.jump(this.get('data-index'));
        })
    };
    
    this.shift = function(dir) {
        if(dir == 'next' && this._current == this._slides.length - 1) {
            this._rail.setStyle(
                'left', 
                '0px'
            );
            this._current = 0;
        }
        else {
            this._rail.setStyle(
                'left', 
                (-this._slide_width * (dir == 'next' ? ++this._current : --this._current)) + 'px'
            );
        }
        this.updateNav();
    }
    
    this.jump = function(index) {
        this._current = index;
        this._rail.setStyle(
            'left', 
            (-this._slide_width * this._current) + 'px'
        );
        this.updateNav();
    }
    
    this.toggleNavPane = function() {
        if(this._navbar_pane.hasClass('active')) {
            this._navbar_pane.removeClass('active');
            this._navbar_pane.setStyle('height', '0');
            this._nav_index.removeClass('active');
        }
        else {
            this._navbar_pane.addClass('active');
            this._navbar_pane.setStyle('height', this._navbar_pane.retrieve('height'));
            
            this._nav_index.addClass('active');
        }
    }
    
    this.updateNav = function() {
    	this._nav_prev[this._current == 0 ? 'addClass' : 'removeClass']('inactive');
        this._nav_next[this._current == this._slides.length -1 ? 'addClass' : 'removeClass']('inactive');
        this._dot_nav.getElements('.slideshow-dotnav-dot').removeClass('active');
        this._dot_nav.getElements('.slideshow-dotnav-dot')[this._current].addClass('active');
        this._navbar_pane.getElements('.slideshow-thumb').removeClass('active');
        this._navbar_pane.getElements('.slideshow-thumb')[this._current].addClass('active');
    }
};
