<?php
/**
* @file view/showcase.php
* @ingroup gino-gallery
* @brief Template per la vista showcase fisso sullo sfondo
*
* Variabili disponibili:
* - **section_id**: attributo id section
* - **ctgs**: array di categorie @ref \Gino\App\Gallery\Category
* - **active_ctg**: categoria @ref \Gino\App\Gallery\Category attiva
*
* @version 1.0.0
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Gallery; ?>
<? //@cond no-doxygen ?>
<? if($active_ctg): ?>
<script>
    var ctgs = [];
    <? foreach($ctgs as $ctg): ?>
        var ctg = {name: '<?= \Gino\jsVar($ctg->ml('name')) ?>'};
        var images = [];
        <? foreach($ctg->getImages() as $image): ?>
            images.push({
                path: '<?= $image->path() ?>',
                caption: '<?= \Gino\jsVar($image->ml('name')) ?>'
            });
        <? endforeach ?>
        ctg.images = images;
        ctgs.push(ctg);
    <? endforeach ?>
    var showcase = new gallery.Showcase(ctgs, {
        dom_content: 'main-container'
    });
</script>
<? endif ?>
<? // @endcond ?>
