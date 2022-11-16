<?php

return [
    'name'     => 'broadcast',
    'snippets' => [
        [
            'codeContents' => [
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/controllers/admin/StatController.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'question-adminStat-index-1',
                                    'content' => '
        //概览-问卷
        $questionsModel = vss_model()->getQuestionsModel();
        $questionStat = [
            "total" => $questionsModel->getCount(),
            "day"   => $questionsModel->getCount($conditionDay),
            "week"  => $questionsModel->getCount($conditionWeek),
            "month" => $questionsModel->getCount($conditionMonth),
            "year"  => $questionsModel->getCount($conditionYear),
        ];
        $data["question_stat"] = $questionStat;
'
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        ]
    ]
];
