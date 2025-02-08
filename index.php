<?php

if ($text == '/start') {
    $user_query = mysqli_query($con, "SELECT * FROM `users` WHERE `telegram_id` = '$chat_id'");
    if (mysqli_num_rows($user_query) == 0) {
        $user_rec = mysqli_query($con, "INSERT INTO `users`(`telegram_id`, `username`) VALUES ('$chat_id', '$user_name')");
    }
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🎓 Welcome to CGPA Pro! 🔥 \nYour smart and easy-to-use GPA & CGPA calculator! 📊 \n\n🚀 What can I do? \n✅ Calculate your GPA & CGPA instantly \n✅ Supports different grading systems \n✅ Track multiple semesters easily \n\n⚡ Start calculating now! Just click Calculate 🚀!",
        'reply_markup' => json_encode([
            'keyboard' => [
                [['text' => 'Calculate 🚀']]
            ],
            'resize_keyboard' => true
        ]),
    ]);
}

// Course name

if ($text == 'Calculate 🚀') {
    $course_rec = mysqli_query($con, "INSERT INTO `courses`(`telegram_id`, `status`) VALUES ('$chat_id', 'writing')");
    if ($course_rec) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "📚 Enter the course name (optional) 📝",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => 'Skip']],
                    [['text' => 'Cancel']],
                ],
                'resize_keyboard' => true
            ]),
        ]);
    }
}

$sql_check = mysqli_query($con, "SELECT * FROM `courses` WHERE `telegram_id` = '$chat_id' AND `status` = 'writing' ORDER BY `id` DESC LIMIT 1");
$check = mysqli_fetch_assoc($sql_check);
if ($check['status'] == 'writing') {
    if ($text != 'Calculate 🚀') {
        $course_update = mysqli_query($con, "UPDATE `courses` SET `course_name` = '$text', `status` = 'pending' WHERE `telegram_id` = '$chat_id' AND `status` = 'writing' ORDER BY `id` DESC LIMIT 1");
        if ($course_update) {
            credit_hour();
        }
    }
}


if ($text == 'Skip') {
    $course_update = mysqli_query($con, "UPDATE `courses` SET `course_name` = 'No course name', `status` = 'pending' WHERE `telegram_id` = '$chat_id' AND `status` = 'writing' ORDER BY `id` DESC LIMIT 1");
    if ($course_update) {
        credit_hour();
    }
}

// Credit hour
if (strpos($data, 'callback_credit_') !== false) {
    $credit_hour = str_replace('callback_credit_', '', $data);

    $course_update = mysqli_query($con, "UPDATE `courses` SET `credit_hours` = '$credit_hour', `grade_point` = '$grade_point' WHERE `telegram_id` = '$chat_id2' AND `status` = 'pending' ORDER BY `id` DESC LIMIT 1");
    if ($course_update) {
        credit_hour();
    }
}

// grade
if (strpos($data, 'grade_') !== false) {
    $grade = str_replace('grade_', '', $data);

    switch ($grade) {
        case 'a-plus':
        case 'a':
            $grade_point = 4;
            break;
        case 'a-minus':
            $grade_point = 3.75;
            break;
        case 'b-plus':
            $grade_point = 3.5;
            break;
        case 'b':
            $grade_point = 3;
            break;
        case 'b-minus':
            $grade_point = 2.75;
            break;
        case 'c-plus':
            $grade_point = 2.5;
            break;
        case 'c':
            $grade_point = 2;
            break;
        case 'c-minus':
            $grade_point = 1.75;
            break;
        case 'd':
            $grade_point = 1;
            break;
        case 'f':
            $grade_point = 0;
            break;

        default:
            $grade_point = 0;
            break;
    }

    $course_update = mysqli_query($con, "UPDATE `courses` SET `grade` = '$grade', `grade_point` = '$grade_point' WHERE `telegram_id` = '$chat_id2' AND `status` = 'pending' ORDER BY `id` DESC LIMIT 1");
    if ($course_update) {
        bot('sendMessage', [
            'chat_id' => $chat_id2,
            'text' => "🤔 Have you done? or add more?",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => 'Add more']],
                    [['text' => 'Done']],
                ],
                'resize_keyboard' => true
            ]),
        ]);
    }
}



// ===================== FUNCTIONS =====================

function credit_hour(): void
{
    global $chat_id;

    $credit_hour_btn = json_encode(
        [
            'inline_keyboard' => [
                [['text' => '1', 'callback_data' => 'callback_credit_1']],
                [['text' => '2', 'callback_data' => 'callback_credit_2']],
                [['text' => '3', 'callback_data' => 'callback_credit_3']],
                [['text' => '4', 'callback_data' => 'callback_credit_4']],
                [['text' => '5', 'callback_data' => 'callback_credit_5']],
                [['text' => '6', 'callback_data' => 'callback_credit_6']],
                [['text' => '7', 'callback_data' => 'callback_credit_7']],
            ],
        ]
    );

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "⏳ Choose credit hour",
        'reply_markup' => $credit_hour_btn,
    ]);
}

function grade()
{
    global $chat_id2;
    global $mid;

    $grade_btn = json_encode(
        [
            'inline_keyboard' => [
                [['text' => 'A-', 'callback_data' => 'grade_a-minus'], ['text' => 'A', 'callback_data' => 'grade_a'], ['text' => 'A+', 'callback_data' => 'grade_a-plus']],
                [['text' => 'B-', 'callback_data' => 'grade_b-minus'], ['text' => 'B', 'callback_data' => 'grade_b'], ['text' => 'B+', 'callback_data' => 'grade_b-plus']],
                [['text' => 'C-', 'callback_data' => 'grade_c-minus'], ['text' => 'C', 'callback_data' => 'grade_c'], ['text' => 'C+', 'callback_data' => 'grade_c-plus']],
                [['text' => 'D', 'callback_data' => 'grade_d'], ['text' => 'F', 'callback_data' => 'grade_f']],
            ],
        ]
    );
    bot('editMessageText', [
        'chat_id' => $chat_id2,
        'message_id' => $mid,
        'text' => "📊 Choose your grade",
        'reply_markup' => $grade_btn,
    ]);
}
