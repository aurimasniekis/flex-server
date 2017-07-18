<?php

return function ($aliases): string {
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Symfony Flex Server</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <style type="text/css">
        .container {
            margin-top: 50px;
        }
    </style>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<div class="container">
    <div class="row">
        <div class="page-header">
            <h1>
                Symfony Flex Server <br />
                <small>Manages Symfony recipes</small>
            </h1>
        </div>
    
        <p><b>WARNING</b> This is alpha software; use it at your own risk, protect your code with a source code management tool.</p>
        <h3 style="margin-top: 20px;">This server defines the following package aliases:</h3>
    
    </div>
    <div class="row">
    
        <form>
            <div class="form-group">
                <input type="text" id="search" autocomplete="off" autofocus class="form-control input-lg" placeholder="Filter by..."/>
            </div>
        </form>
    
        <div class="list-group">';

    foreach ($aliases  as $name => $alias) {
        $html .= '    <a href="#" class="list-group-item">' . PHP_EOL;
        $html .= '        <h4 class="list-group-item-heading">' . $name . '</h4>' . PHP_EOL;
        $html .= '        <p class="list-group-item-text">' . implode(', ', $alias) . '</p>' . PHP_EOL;
        $html .= '    </a>' . PHP_EOL . PHP_EOL;
    }

    $html .= '    </div>
    </div>
    
    <p class="text-right">
        <small>Powered By <a href="https://github.com/aurimasniekis/flex-server">Flex Server</a> 1.0.0-dev</small>
    </p>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script type="text/javascript">
    $(function () {
        var searchInput = $(\'input#search\');
        var packages = $(\'.list-group-item\');
        var timer;
        searchInput.keyup(function(){
            clearTimeout(timer);
            var ms = 350; // milliseconds
            var needle = $(this).val().toLowerCase(), showTitle, showAliases;
            timer = setTimeout(function() {
                packages.each(function(){
                    showTitle = $(this).find(".list-group-item-heading").text().toLowerCase().indexOf(needle) != -1;
                    showAliases = $(this).find(".list-group-item-text").text().toLowerCase().indexOf(needle) != -1;
                    
                    $(this).toggle((showTitle || showAliases));
                });
            }, ms);
        }).focus();
        
        searchInput.change(function(){
            window.location.hash = "!/" + $(this).val().toLowerCase();
        });
        
        $(window).on("hashchange", function() {
            if (window.location.hash.indexOf("#!/") == 0) {
                searchInput.val(window.location.hash.replace(/#!\//,"").toLowerCase());
                searchInput.trigger("keyup");
            } else {
                var $anchor = $("div[id=\'"+window.location.hash.replace(/^#/,"")+"\']");
                if ($anchor.length != $anchor.filter(":visible").length) {
                    searchInput.val("").trigger("keyup");
                    $anchor.get(0).scrollIntoView();
                }
            }
        });
        $(window).trigger("hashchange");
    });
</script>
</body>
</html>';

    return $html;
};