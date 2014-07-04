<?php

$root = 'd:/gxt-web-ui';
$ignore_list = array('.git', '.idea', 'yii');

$queue = array($root);
$bom_table = array();
$encoding_table = array();
while ($queue) {
    $r = array_pop($queue);
    echo "for dir $r\n";
    $d = dir($r);
    while (($file = ($d->read())) != false) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        if (in_array($file, $ignore_list)) {
            continue;
        }
        $f = "$r/$file";
        if (preg_match('/\.php/', $file)) {
            echo "for file $f\n";
            $code = file_get_contents($f);
            if (preg_match('/\A\xEF\xBB\xBF/', $code)) {
                echo "Warning: $f ",'BOM',"\n";
                $bom_table[$f] = 1;
            }
            $encoding = mb_detect_encoding($code, array('ASCII', 'UTF-8'), true);
            var_dump($encoding);
            if ($encoding === false) {
                echo "Warning: no encoding\n";
                $encoding_table[$f] = 0;
            } elseif ($encoding != 'UTF-8' && $encoding != "ASCII") {
                $encoding_table[$f] = $encoding;
            }
        } elseif (is_dir($f)) {
            $queue[] = $f;
        }
    }
}

echo "BOM\n";
print_r(array_keys($bom_table));

echo "\nencoding\n";
var_dump(($encoding_table));
