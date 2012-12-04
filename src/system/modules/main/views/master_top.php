<!DOCTYPE html>
<html lang="en" class="no-js <?php echo implode(" ", K::request()->tags()); ?>" data-baseurl="<?php echo K::url() ?>" data-view="<?php echo $viewID ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo $title; ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">
        
        <meta name="ROBOTS" content="INDEX,FOLLOW">
        <meta name="author" content="<?php echo K::url("base") ?>humans.txt">
        
        <meta name="description" content="" />
        <meta name="keywords" content="" />
        
        <!-- For third-generation iPad with high-resolution Retina display: -->
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo K::asset()->url('touch-icons/apple-touch-icon-144x144-precomposed.png', $assetsVersion) ?>">
        <!-- For iPhone with high-resolution Retina display: -->
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo K::asset()->url('touch-icons/apple-touch-icon-114x114-precomposed.png', $assetsVersion) ?>">
        <!-- For first- and second-generation iPad: -->
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo K::asset()->url('touch-icons/apple-touch-icon-72x72-precomposed.png', $assetsVersion) ?>">
        <!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
        <link rel="apple-touch-icon-precomposed" href="<?php echo K::asset()->url('touch-icons/apple-touch-icon-57x57-precomposed.png', $assetsVersion) ?>">
        
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo K::url("static") ?>assets/img/favicon.ico?v=<?php echo $assetsVersion ?>">
        
        <?php
            echo K::asset(array("styles.combined.css", "scripts_head.combined.js"), $assetsVersion);
        ?>
        <style>
            body{
                padding-top: 0;
            }
            #wrapper > footer{
                text-align:center;
                margin-top:40px;
                font-style: italic;
                color:#888;
            }
            .brand{
                font-size:16px !important;
            }
            .brand img{
                vertical-align: baseline;
            }
            .brand i{
                font-style: normal;
                font-family:"Courier New", courier, monotype;
                display:block;
            }
            pre{
                font-size:12px;
            }
        </style>
        <?php
            echo K::asset()->bundle(array("test1.css","test2.css"));
        ?>
    </head>
    <body>
        <div id="wrapper">
            <header>
                <div class="container al-c" style="margin-top:20px">
                    <?php if($viewID != "error"): ?>
                    <div class="hero-unit" style="">
                        <h1 class="brand"><a style="color:#000; text-decoration:none;" href="<?php echo K::url("base") ?>">
                                <span style="font-size:32px">KometPHP</span> <i>v<?php echo \Komet\VERSION; ?></i>
                            </a></h1><br />
                        <p>a lightweight RESTFul and HMVC application framework for PHP</p><br />
                        <a target="_blank" href="http://github.com/kometphp/kometphp/" class="btn btn-inverse"><i class="icon-github"></i> View project on Github</a>
                        <br /><br />
                        <a href="<?php echo K::url("mvc") ?>test/" class="btn"><i class=""></i> Test Logger</a>
                        <a href="<?php echo K::url("mvc") ?>say/?text=Hello another world!" class="btn"><i class=""></i> Test Demo module</a>
                    </div>
                    <?php endif; ?>
                </div>
            </header>
            <div id="main" class="container">