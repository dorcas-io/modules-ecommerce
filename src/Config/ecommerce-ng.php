<?php
return [
    'payment' => [
    	'default' => 'flutterwave',
    	'providers' => [
            'flutterwave' => [
                'title' => 'Flutterwave'
            ],
            'etranzact' => [
                'title' => 'eTrazact'
            ]
        ],
    ],
    'logistics' => [
    	'default' => 'kwik',
    	'providers' => [
            'kwik' => [
                'title' => 'Kwik Delivery'
            ]
        ],
    ],
    'fulfilment' => [
    	'default' => 'kwik',
    	'providers' => [
            'kwik' => [
                'title' => 'Kwik Fulfilment'
            ]
        ],
    ],
];