<?php

return [
    'tileLayer' => [
        // See https://leafletjs.com/reference.html#tilelayer-url-template
        'urlTemplate' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        // See https://leafletjs.com/reference.html#tilelayer-option
        'options' => [
            'attribution' => '&copy; <a href="https://openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
        ],
    ],
];
