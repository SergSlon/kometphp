<div class="container">
    <h1>You said: </h1>
    <blockquote style="font-size:18px; color:darkred;"><?php echo $text; ?></blockquote>
    <a class="btn" href="<?php echo \K::url(); ?>"><i class="icon-home"></i> Back to home</a>
</div>
<style>
<?php include K::asset()->path("css/demo.css") ?>
</style>