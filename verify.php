<?php

$root = 'd:/gxt-web-ui';
$ignore_list = array('.git', '.idea', 'yii', 'vendor');

$queue = array($root);
$bom_table = array();
$encoding_table = array();
$tab_table = array();
$if_table = array();
$define_table = array();
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
                // 2.1. [强制]程序块要采用缩进风格编写，缩进的空格数为4个。
                if (preg_match('/^\t/', $line)) {
                    echo "Warning: tab on line $i\n";
                    $tab_table[$f] = $i;
                }

                // 2.4. [强制]if/while等结构体，即使只有一行，也必须加上花括号，不得写成一行。
                if (preg_match('/\b(if|while)\b/', $line) && !preg_match('/\{\s*$/', $line)) {
                    echo "Warning: line $i: $line if/while not follow by {\n";
                    $if_table[$f][] = $i;
                }

                // 3.2. [强制]常量命名使用全部大写字符，单词之间以’_’连接。
                if (preg_match('/\bdefine\(/', $line) && !preg_match('/\bdefine\(\'[A-Z0-9_]+\'/', $line)) {
                    echo "Warning: line $i: $line define not upper case\n";
                    $define_table[$f][] = $i;
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

echo "\nif/while\n";
print_r($if_table);

echo "\ndefine\n";
print_r($define_table);