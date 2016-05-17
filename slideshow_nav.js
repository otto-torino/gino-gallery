var gallery = gallery || {};

/**
 * @fn SlideshowNav
 * @brief Funzione SlideshowNav utilizzata dalla vista slideshow_nav
 * 
 * @version 1.0.0
 * @copyright 2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 * 
 * ##NOTE
 * Per modificare gli stili delle frecce avanti/indietro modificare i riferimenti css nella funzione renderNavbar(); 
 * ad esempio fa-arrow-left al posto di fa-chevron-left. \n
 * Modificare, se necessario gli stili css nel file slideshow_nav.css: nel caso delle frecce slideshow-prev, slideshow-next, slideshow-index.
 */
gallery.SlideshowNav = function() {
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
		var real_width = this._container.getCoordinates().width - this._container.getStyle('padding-left').toInt() - this._container.getStyle('padding-right').toInt() - this._container.getStyle('border-left-width').toInt() - this._container.getStyle('border-right-width').toInt();
		// - this._container.getStyle('border-left-width').toInt() - this._container.getStyle('border-right-width').toInt()	// if not defined in IE return 'medium'
		var real_height = real_width / this._or_slide_width * this._or_slide_height;
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
			this._nav_prev = new Element('span.fa.fa-2x.fa-arrow-circle-left.slideshow-prev.inactive'),
			this._dot_nav = new Element('span.slideshow-dotnav'),
			this._nav_index = new Element('span.fa.fa-2x.fa-bars.slideshow-index'),
			this._nav_next = new Element('span.fa.fa-2x.fa-arrow-circle-right.slideshow-next')
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
		this._rail.setStyle(
			'left', 
			(-this._slide_width * (dir == 'next' ? ++this._current : --this._current)) + 'px'
		);
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