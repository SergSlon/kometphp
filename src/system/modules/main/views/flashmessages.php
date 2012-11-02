<?php
if (\K::flash()->hasMessages()) {
    ?>
    <div class="container">
        <?php
        $flash_messages =\K::flash()->getMessages();
        foreach ($flash_messages as $msg) {
            ?>
                <div class="alert alert-<?php echo $msg["type"] ?>">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <?php echo $msg["data"] ?>
                </div>
        <?php
        }
        ?>
    </div>
    <?php
}