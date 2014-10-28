<? if($active_ctg): ?>
<script>
    var ctgs = [];
    <? foreach($ctgs as $ctg): ?>
        var ctg = {name: '<?= jsVar($ctg->ml('name')) ?>'};
        var images = [];
        <? foreach($ctg->getImages() as $image): ?>
            images.push({
                path: '<?= $image->path() ?>',
                caption: '<?= jsVar($image->ml('name')) ?>'
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
