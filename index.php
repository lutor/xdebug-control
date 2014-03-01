 <?php 
 /**
  * XDebug Control
  * Author: Martin Lutonsky <martin.lutonsky@gmail.com> http://martinlutonsky.com
  */ob_start();?><!DOCTYPE html>
<html>
<head>
    <title>XDebug Control</title>
    <style type="text/css">
        body{ width: 900px; margin: auto; padding: 1em; font-family: "Trebuchet MS", Helvetica, Arial, sans-serif; font-size: 13px }

        .on{ color: green }

        .off{ color: red }

        #overlay{ width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.3);
            text-align: center; }

        #overlay-wrapper{
            font-size: 40px;
            padding-top: 350px;
            text-shadow: 0px 1px 4px #333333;
            color: #fff;
        }
    </style>
    <script type="text/javascript" src="http://code.jquery.com/jquery-2.1.0.min.js"></script>
    <script type="text/javascript">var restart =<?= isset($_GET['restart']) ? 'true' : 'false';?>;
        var timeout = 120000;
        var url = './?ajaxRestart';
        var completeUrl = './';

        function showOverlay() {
            $('body').append('<div id="overlay"><div id="overlay-wrapper">restarting Apache<br/><span id="counter"></span></div></div>');

            (function () {
                var counter = timeout / 1000;

                setInterval(function () {
                    counter--;
                    if (counter >= 0) {
                        span = document.getElementById("counter");
                        console.log(counter);
                        if (counter % 10 === 0) {
                            span.innerHTML = '.';
                        } else {
                            span.innerHTML = span.innerHTML + '.';
                        }
                    }
                    // Display 'counter' wherever you want to display it.
                    if (counter === 0) {
                        clearInterval(counter);
                        $('#overlay').remove();
                        $(location).attr('href', completeUrl);
                    }

                }, 1000);
            })();
        }

        $(document).ready(function () {
            if (restart) {

                console.log('RESTARTUJU...');
                showOverlay();
                $.ajax({
                    method: 'get',
                    url: url,
                    timeout: timeout
                }).always(function () {
                    console.log('hotovo!');
                    setTimeout(function () {
                        $(location).attr('href', completeUrl);
                    }, 3000);
                });
            }
        });

    </script>
</head>
<body>
<?php

$xdebug_enabled = (bool)ini_get('xdebug.default_enable');
$xdebug_remote_enable = (bool)ini_get('xdebug.remote_enable');
$phpini = php_ini_loaded_file();
$xdebug_settings = array();
$phpini_handler = fopen($phpini, "r");
$phpini_content = '';
$redirect = null;

while (!feof($phpini_handler)) {
    $line = fgets($phpini_handler);

    if ($line && substr(trim($line), 0, 7) === 'xdebug.') {
        $xdebug_settings[] = $line;
    }
    $phpini_content .= $line;
}
fclose($phpini_handler);

if (isset($_GET['on']) || isset($_GET['off'])) {
    $set = (int)isset($_GET['on']);


    $phpini_content = preg_replace('/(xdebug\.default_enable)\s*=\s*([a-z0-9])/i', '\\1=' . $set, $phpini_content);
    $phpini_content = preg_replace('/(xdebug\.remote_enable)\s*=\s*([a-z0-9])/i', '\\1=' . $set, $phpini_content);
    file_put_contents($phpini, $phpini_content);
    $redirect = 'restart';
}

if (isset($_GET['ajaxRestart'])) {
    shell_exec(__DIR__ . DIRECTORY_SEPARATOR . 'restart.cmd');
}

?>
<h1>XDebug Control</h1>

<div>
    <p><strong>php.ini</strong>: <a href="editor://open/?file=<?= urlencode($phpini) ?>&line=1"><?= $phpini ?></a></p>

    <p><strong>XDebug status</strong>: <strong style="font-size:1.5em"
                                               class="<?= $xdebug_enabled ? 'on' : 'off' ?>"><?= $xdebug_enabled ? 'On' : 'Off' ?>
        </strong> (<a
            href="./?<?= $xdebug_enabled ? 'off' : 'on'; ?>")><?= $xdebug_enabled ? 'Off' : 'On' ?></a>)</p>

    <strong>XDebug settings:</strong>
    <textarea style="width:900px;height:450px;" readonly="true"><?= implode("", $xdebug_settings); ?></textarea>
</div>
</body>
</html>
<?php
if ($redirect !== null) {
    header('Location: ./?' . $redirect, true, 302);
    ob_end_clean();
}
ob_end_flush(); ?>