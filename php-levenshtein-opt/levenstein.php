<?php
/**
 * Левенштейн с оптимизациями
 *
 * 1. Просматриваем словарь не по порядку, а по разнице длины текущего слова и словарных слов (len_diff)
 * 2. Исключаем из расчета дистанции Левенштейна словарные слова, len_diff которых больше или равна
 * уже найденной минимальной дистанции - все равно меньше уже не найдем (потребуются доп. удаления или вставки)
 * 3. Запоминаем дистанцию для повторяющихся слов, чтобы не считать заново
 *
 * @maintainer Vitaliy Fedorov <900dw1n@gmail.com>
 */

define('VOCABULARY_FILENAME', './vocabulary.txt');
$start = microtime(true);


if ($argc < 2) {
    echo 'Error: script estimates source file name as the first parameter' . PHP_EOL;
    exit(0);
}

if (!$rows = file($argv[1])) {
    echo 'Error: could not open \'' . $argv[1] . '\' in read mode' . PHP_EOL;
    exit(0);
}

$vocabulary_text = file_get_contents(VOCABULARY_FILENAME);
$vocabulary = explode(PHP_EOL, $vocabulary_text);

$indexed = [];
foreach ($vocabulary as $row => $correct_word) {
    if (!$wlen = strlen($correct_word)) continue;
    if (!isset($indexed[$wlen])) {
        $indexed[$wlen] = [];
    }
    $indexed[$wlen][$correct_word] = true;
}
unset($vocabulary);
$length_set = array_keys($indexed);

$search_map = [];
$result = 0;
$found_map = [];
foreach ($rows as $row) {
    $words = explode(' ', $row);
    foreach ($words as $word) {
        if ($word) {
            $word = strtoupper(trim($word));
            if (isset($found_map[$word])) {
                $result += $found_map[$word];
                continue;
            }
            $wlen = strlen($word);
            if (isset($indexed[$wlen][$word])) {
                continue;
            }
            $distance = null;
            $len_diff = 0;
            $all_words[] = $word;

            if (!isset($search_map[$wlen])) {
                $search_map[$wlen] = [];
                foreach ($length_set as $length) {
                    $search_map[$wlen][$length] = abs($wlen - $length);
                }
                asort($search_map[$wlen]);
            }

            foreach ($search_map[$wlen] as $block_wlen => $len_diff) {
                if ($len_diff >= $distance && $distance !== null) {
                    break;
                }
                foreach ($indexed[$block_wlen] as $correct_word => $stubb) {
                    $local_distance = levenshtein($word, $correct_word);
                    if ($local_distance < $distance || $distance === null) {
                        $distance = $local_distance;
                        if ($len_diff >= $distance) {
                            break;
                        }
                    }
                }
            }
            $found_map[$word] = $distance;
            $result += $distance;
        }
    }
}

echo $result . PHP_EOL;