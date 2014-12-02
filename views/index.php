<?php
/**
* @file view/index.php
* @ingroup gino-gallery
* @brief Template per la vista elenco categorie
*
* Variabili disponibili:
* - **section_id**: attributo id section
* - **title**: titolo
* - **ctgs**: array associativo di categorie. Le chiavi sono: 
*     - thumb: path della thumb che rappresenta la categoria
*     - url: url che porta al dettaglio della categoria
*     - name: nome categoria
*     - description: descrizione categoria
*
* @version 0.1.0
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Gallery; ?>
<? //@cond no-doxygen ?>
<section id="<?= $section_id ?>">
    <h1><?= $title ?></h1>
    <ul class="gallery-list">
    <? foreach($ctgs as $ctg): ?>
        <li>
            <div class="left" style="margin-right: 10px;">
                <img src="<?= $ctg['thumb'] ?>" style="width: 200px;" class="img img-circle" />
            </div>
            <div class="left">
                <p style="margin-top: 65px;"><b><a href="<?= $ctg['url'] ?>"><?= $ctg['name'] ?></a></b></p>
                <p><?= $ctg['description'] ?></p>
            </div>
            <div class="clear"></div>
        </li>
    <? endforeach ?>
    </ul>
</section>
<? // @endcond ?>
