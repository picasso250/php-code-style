<?php

$root = 'd:/gxt-web-ui';
$ignore_list = array('.git', '.idea', 'yii', 'vendor');

$queue = array($root);
$bom_table = array();
$encoding_table = array();
$tab_table = array();
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
            if ($encoding === false) {
                echo "Warning: no encoding\n";
                $encoding_table[$f] = 0;
            } elseif ($encoding != 'UTF-8' && $encoding != "ASCII") {
                $encoding_table[$f] = $encoding;
            }

            $fh = fopen($f, 'r');
            $i = 0;
            while (($line = fgets($fh)) != false) {
                // echo "$i: $line\n";
                $i++;
                if (preg_match('/^\t/', $line)) {
                    echo "Warning: tab on line $i\n";
                    $tab_table[$f] = $i;
                }
            }
        } elseif (is_dir($f)) {
            $queue[] = $f;
        }
    }
}

echo "BOM\n";
print_r(array_keys($bom_table));

echo "\nencoding\n";
print_r(($encoding_table));

echo "\ntab\n";
print_r($tab_table);
