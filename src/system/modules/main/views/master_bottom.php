            </div><!-- #main -->
            <footer>
                <div class="container" style="margin-bottom:40px">
                    powered by KometPHP v<?php echo \Komet\VERSION; ?>
                </div>
            </footer>
        </div><!-- #wrapper -->
        <?php 
            echo K::asset(array("scripts.combined.js"), $assetsVersion);
        ?>
        <?php echo Komet\Profiler\Profiler::getInstance()->getIframe(); ?>
    </body>
</html>