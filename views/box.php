<?php
/**
* @file view/box.php
* @ingroup gino-gallery
* @brief Template per la vista box di presentazione home page
* @description Vengono considerate solamente le categorie con immagini
*
* Variabili disponibili:
* - **section_id**: attributo id section
* - **title**: titolo
* - **ctgs**: array associativo di categorie. Le chiavi sono: 
*     - url: url che porta al dettaglio della categoria
*     - name: nome categoria
*     - cover: immagine cover @ref \Gino\App\Gallery\Image
*
* @version 0.1.0
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Gallery; ?>
<? //@cond no-doxygen ?>
<?php $registry = \Gino\Registry::instance(); ?>
<?php $ctg = $ctgs[0]; ?>
<section id="<?= $section_id ?>">
    <div id='gallery-box-container' style="background: url(<?= $ctg['cover']->path(); ?>) no-repeat center center">
        <h1 onclick="location.href='gallery/index/'" style="cursor: pointer"><?= _('Gallerie') ?> <span class="fa fa-external-link-square"></span></h1>
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
                    name: '<?= \Gino\htmlChars($ctg['name']); ?>',
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
<? // @endcond ?>
