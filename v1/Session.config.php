<?php
return [
    'service'=>[
        'name'=>'session',
        'description'=>'Session services handler',
        'actions'=>[
            [
                'name'=>'signin',
                'method'=>'POST',
                'descritpion'=>'Segenerate a new session if credentials are correct',
                'input'=>[
                    'username'=>[
                        'required'  => true,
                        'regexp'    => '/^.*$/'
                    ],
                    'password'=>[
                        'required'  => true,
                        'regexp'    => '/^.*$/'
                    ]
                ],
                'output'=>[
                    'error'=>
                        // red alert
                        'Error description (exists if http != 200 )',
                    'feedback'=>
                        // yellow alert if http != 200 / green otherwise
                        'Feedback message to display',
                    'data'=>
                        'result object with session metadata',
                    'secret'=>
                        'session key (exists if http = 200 )'
                ],
            ],
            [
                'name'=>'get',
                'method'=>'GET',
                'descritpion'=>'Get full session dataset',
                'input'=>[
                    'secret'=>[
                        'required'  => false, // default: current one
                        'regexp'    => '/^.*$/'
                    ],
                ],
                'output'=>[
                    'error'=>
                        // red alert
                        'Error description (exists if http != 200 )',
                    'feedback'=>
                        // yellow alert if http != 200 / green otherwise
                        'Feedback message to display',
                    'data'=>
                        'result object with session metadata',
                ],
            ],
            [
                'name'=>'signout',
                'method'=>'DELETE',
                'descritpion'=>'Delete current session',
                'input'=>[
                    'secret'=>[
                        'required'  => false, // default: current one
                        'regexp'    => '/^.*$/'
                    ],
                ],
                'output'=>[
                    'error'=> // red alert
                        'Error description (exists if http != 200 )',
                    'feedback'=>
                        // yellow alert if http != 200 / green otherwise
                        'Feedback message to display',
                    'data'=>
                        'result object with session metadata',
                ],
            ],
            [
                'name'=>'signup',
                'method'=>'POST',
                'descritpion'=>'Generate a new user',
            ],
            [
                'name'=>'recover',
                'method'=>'PUT',
                'descritpion'=>'Recover lost credentials',
                'input'=>[
                    'username'=>[
                        'required'  => true, // default: current one
                        'regexp'    => '/^.*$/'
                    ],
                    'email'=>[
                        'required'  => true, // default: current one
                        'regexp'    => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/'
                    ],
                ],
                'output'=>[
                    'error'=> // red alert
                        'Error description (exists if http != 200 )',
                    'feedback'=>
                        // yellow alert if http != 200 / green otherwise
                        'Feedback message to display',
                ],

            ],
        ],
    ]
];
?>
