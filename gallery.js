var gino_gallery = gino_gallery || {};

gino_gallery.Showcase = new Class({
    Implements: [Options, Events],
    options: {
        dom_content: null,
        interval_period: 4000
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
            this.dom.content.setStyle('margin-top', (this.viewport.height - 150) + 'px')
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
        }
        this.addCategoryListItems();
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
