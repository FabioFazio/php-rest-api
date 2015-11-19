<?php
return [
    'service'=>[
        'name'=>'main',
        'description'=>'Main API example',
        'actions'=>[
            [
                'name'=>'example',
                'method'=>'GET',
                'descritpion'=>'Retrive a box content from box\'s token',
                'input'=>['token'=>[
                        'required'  => true,
                        'regexp'    => '/^[0-9]{1,5}$/'
                    ]
                ],
                'output'=>['message'=>'Your box content is %'],
            ],
        ],
    ]
];
?>
