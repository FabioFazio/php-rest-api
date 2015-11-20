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
                    'feedback'=>
                        'optional output defined as object of strings {error,warning,info,success}',
                    'result'=>[
                        'secret'=> 'session key (exists if http = 200 )'
                    ],
                ],
            ],[
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
                    'feedback'=>
                        'optional output defined as object of strings {error,warning,info,success}',
                    'result'=>[
                        'session' => 'session obj (exists if http = 200 )'
                    ],
                ],
            ],[
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
                    'feedback'=>
                        'optional output defined as object of strings {error,warning,info,success}',
                ],
            ],[
                'name'=>'signup',
                'method'=>'POST',
                'descritpion'=>'Public requests to generate a new user',
                'input'=>[
                    'name'=>[
                        'required'  => true,
                        'regexp'    => '/^.*$/'
                    ],
                    'surname'=>[
                        'required'  => true,
                        'regexp'    => '/^.*$/'
                    ],
                    'email'=>[
                        'required'  => true,
                        'regexp'    => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/'
                    ],
                    'language'=>[
                        'required'  => false,
                        'regexp'    => '/^\\d+$/'
                    ],
                    'password'=>[
                        'required'  => true,
                        'regexp'    => '/^.{5,}$/'
                    ],
                    'sex'=>[
                        'required' => false,
                        'regexp'    => '/^0|1$/'
                    ],
                    'idprivacy'=>[
                        'required'  => true,
                        'regexp'    => '/^1$/'
                    ],
                    'captcha'=>[
                        'required'  => true, // array(input,id)
                    ],
                ],
                'output'=>[
                    'feedback'=>
                        'optional output defined as object of strings {error,warning,info,success}',
                ],
            ],[
                'name'=>'captcha',
                'method'=>'GET',
                'descritpion'=>'Requests to generate a new registration captcha',
                'input'=>[
                ],
                'output'=>[
                    'feedback'=>
                        'optional output defined as object of strings {error,warning,info,success}',
                    'result'=>[
                        'captcha' => 'captcha code',
                        'path' => 'url path to png file',
                    ],
                ],
            ],[
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
                    'feedback'=>
                        'optional output defined as object of strings {error,warning,info,success}',
                ],

            ],
        ],
    ]
];
?>
