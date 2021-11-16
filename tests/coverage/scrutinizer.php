<?php

$url = 'https://scrutinizer-ci.com/api/repositories/g/staudenmeir/eloquent-has-many-deep/data/code-coverage';

$data = [
    'revision' => exec('git rev-parse HEAD'),
    'parents' => [
        0 => exec('git log --pretty=%P -n1 HEAD'),
    ],
    'coverage' => [
        'format' => 'php-clover',
        'data' => base64_encode(
            str_replace(
                getcwd() . '/',
                '{scrutinizer_project_base_path}/',
                file_get_contents('coverage.xml')
            )
        ),
    ],
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($data),
    ],
];

$context = stream_context_create($options);

file_get_contents($url, false, $context);
