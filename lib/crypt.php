<?php

function fileHash($file) {
    $algo = "sha256";
    return hash_file($algo,$file);
}

function dataHash($string) {
    $algo = "sha256";
    return hash($algo,$string);
}