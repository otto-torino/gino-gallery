<?php $registry = registry::instance(); ?>
<?php $ctg = $ctgs[0]; ?>
<section id="<?= $section_id ?>">
    <div id='gallery-box-container' style="background: url(<?= $ctg['cover']->path(); ?>) no-repeat center center">
        <h1>Gallerie</h1>
        <p class="caption"><a href="<?= $ctg['url'] ?>"><?= $ctg['name']; ?></a></p>
    </div>
    <script>
        var gallery = { 
            current: 0
        };
        gallery.items = [];
        <? foreach($ctgs as $ctg): ?>
            gallery.items.push(
                {
                    img: '<?= $ctg['cover']->path(); ?>',
                    name: '<?= htmlChars($ctg['name']); ?>',
                    url: '<?= $ctg['url'] ?>'
                }
            );
            // preload images
            var image = new Image();
            image.src = '<?= $ctg['cover']->path(); ?>';
        <? endforeach ?>
        gallery.rotate = function() {
            var next = gallery.current == gallery.items.length - 1 ? 0 : gallery.current + 1;
            $('gallery-box-container').setStyle('background-image', 'url(' + gallery.items[next].img + ')');
            $$('#gallery-box-container .caption a').set('text', gallery.items[next].name)
                                                   .set('href', gallery.items[next].url);
            gallery.current = next;
        };

        setInterval(gallery.rotate, 5000);
    </script>
</section>
