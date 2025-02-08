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
    if ($text != 'Skip') {
        if ($text != 'Calculate 🚀') {
            if ($text != '/start') {
                if ($text != '/history') {
                    if ($text != '/cgpa') {
                        $course_update = mysqli_query($con, "UPDATE `courses` SET `course_name` = '$text', `status` = 'pending' WHERE `telegram_id` = '$chat_id' AND `status` = 'writing' ORDER BY `id` DESC LIMIT 1");
                        if ($course_update) {
                            credit_hour();
                        }
                    }
                }
            }
        }
    }
}

// Skip btn
if ($text == 'Skip') {
    $course_update = mysqli_query($con, "UPDATE `courses` SET `course_name` = 'No course name', `status` = 'pending' WHERE `telegram_id` = '$chat_id' AND `status` = 'writing' ORDER BY `id` DESC LIMIT 1");
    if ($course_update) {
        credit_hour();
    }
}

// cancel btn
if ($text == 'Cancel') {
    $del_course = mysqli_query($con, "DELETE FROM `courses` WHERE `telegram_id` = '$chat_id' AND `status` != 'Done'");
    if ($del_course) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ Data removed successfully",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => 'Calculate 🚀']]
                ],
                'resize_keyboard' => true
            ]),
        ]);
    }
}

// Credit hour
if (strpos($data, 'callback_credit_') !== false) {
    $credit_hour = str_replace('callback_credit_', '', $data);

    $course_update = mysqli_query($con, "UPDATE `courses` SET `credit_hours` = '$credit_hour', `grade_point` = '$grade_point' WHERE `telegram_id` = '$chat_id2' AND `status` = 'pending' ORDER BY `id` DESC LIMIT 1");
    if ($course_update) {
        grade();
    }
}

// grade
if (strpos($data, 'grade_') !== false) {
    $grade = str_replace('grade_', '', $data);

    switch ($grade) {
        case 'a-plus':
            $grade_point = 4;
            $grade_sign = 'A+';
            break;
        case 'a':
            $grade_point = 4;
            $grade_sign = 'A';
            break;
        case 'a-minus':
            $grade_point = 3.75;
            $grade_sign = 'A-';
            break;
        case 'b-plus':
            $grade_point = 3.5;
            $grade_sign = 'B+';
            break;
        case 'b':
            $grade_point = 3;
            $grade_sign = 'B';
            break;
        case 'b-minus':
            $grade_point = 2.75;
            $grade_sign = 'B-';
            break;
        case 'c-plus':
            $grade_point = 2.5;
            $grade_sign = 'C+';
            break;
        case 'c':
            $grade_point = 2;
            $grade_sign = 'C';
            break;
        case 'c-minus':
            $grade_point = 1.75;
            $grade_sign = 'C-';
            break;
        case 'd':
            $grade_point = 1;
            $grade_sign = 'D';
            break;
        case 'f':
            $grade_point = 0;
            $grade_sign = 'F';
            break;

        default:
            $grade_point = 0;
            break;
    }

    $course_update = mysqli_query($con, "UPDATE `courses` SET `grade` = '$grade_sign', `grade_point` = '$grade_point', `status` = 'Ready' WHERE `telegram_id` = '$chat_id2' AND `status` = 'pending' ORDER BY `id` DESC LIMIT 1");
    if ($course_update) {
        $Courses_get = mysqli_query($con, "SELECT * FROM `courses` WHERE `telegram_id` = '$chat_id2' AND `status` = 'Ready'");
        $message = "✅ Your course grade submitted \n";
        $num = 0;
        while ($row = mysqli_fetch_assoc($Courses_get)) {
            $num++;
            $course_name = $row['course_name'];
            $grade = $row['grade'];
            $message .= "\n$num. $course_name ($grade)";
        }
        bot('editMessageText', [
            'chat_id' => $chat_id2,
            'message_id' => $mid,
            'text' => $message,
            'reply_markup' => $grade_btn,
        ]);
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

// Add more courses
if ($text == 'Add more') {
    $course_rec = mysqli_query($con, "INSERT INTO `courses`(`telegram_id`, `status`) VALUES ('$chat_id', 'writing')");
    if ($course_rec) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "📚 Enter the course name (optional) 📝",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => 'Skip']],
                    [['text' => 'Done']],
                ],
                'resize_keyboard' => true
            ]),
        ]);
    }
}

// Finished course adding
if ($text == 'Done') {
    $courses_check = mysqli_query($con, "SELECT * FROM `courses` WHERE `telegram_id` = '$chat_id' AND `status` = 'Ready'");
    if (mysqli_num_rows($courses_check) > 0) {
        $courses = mysqli_query($con, "SELECT  COUNT(*) AS total_courses, SUM(`credit_hours`) AS total_credit_hours, SUM(`credit_hours` * `grade_point`) AS total_grade_points  FROM `courses` WHERE `telegram_id` = '$chat_id' AND `status` = 'Ready'");
        $row = mysqli_fetch_assoc($courses);
        $total_courses = $row['total_courses'];
        $total_credit_hours = $row['total_credit_hours'];
        $total_grade_points = $row['total_grade_points'];
        $gpa = $total_grade_points / $total_credit_hours;
        $gpa = number_format($gpa, 2);

        $cgpa_rec = mysqli_query($con, "INSERT INTO `cgpa_records` (`telegram_id`, `total_credit_hours`, `total_grade_points`, `gpa`) VALUE ('$chat_id', '$total_credit_hours', '$total_grade_points', '$gpa')");
        if ($cgpa_rec) {
            $course_update = mysqli_query($con, "UPDATE `courses` SET `status` = 'Done' WHERE `telegram_id` = '$chat_id'");
            if ($course_update) {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "🎓 **GPA Calculation Result** 🎓 \n\n📖 Total Courses: $total_courses \n🎯 Total Credit Hours: $total_credit_hours \n📈 Total Grade Points: $total_grade_points \n\n🔥 **GPA: $gpa** \n\n🔄 Keep up the hard work! Stay focused and aim higher! 🚀 \n\n💡 Want to check your overall CGPA? Click 👉 /cgpa",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => json_encode([
                        'keyboard' => [
                            [['text' => 'Calculate 🚀']]
                        ],
                        'resize_keyboard' => true
                    ]),
                ]);
            }
        }
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ No course found!",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => 'Calculate 🚀']]
                ],
                'resize_keyboard' => true
            ]),
        ]);
    }
}

// CGPA

if ($text == '/cgpa') {
    $cgpa_records = mysqli_query($con, "SELECT * FROM `cgpa_records` WHERE `telegram_id` = '$chat_id'");
    if (mysqli_num_rows($cgpa_records) > 0) {
        $message = "📜 *Your GPA History:*\n\n";
        $total_credit_hours = 0;
        $total_grade_points = 0;

        while ($row = mysqli_fetch_assoc($cgpa_records)) {
            $date = date("Y-m-d", strtotime($row['created_at']));
            $gpa = number_format($row['gpa'], 2);
            $message .= "📅 *$date* - 📊 *GPA:* `$gpa`\n";

            $total_credit_hours += (int) $row['total_credit_hours'];
            $total_grade_points += (float) $row['total_grade_points'];
        }

        $cgpa = ($total_credit_hours > 0) ? ($total_grade_points / $total_credit_hours) : 0;
        $cgpa = number_format($cgpa, 2);

        $message .= "\n🔥 *Final CGPA:* `$cgpa`";

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => 'Calculate 🚀']]
                ],
                'resize_keyboard' => true
            ]),
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ No data found! \n\nClick `Calculate 🚀` to add CGPA",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => 'Calculate 🚀']]
                ],
                'resize_keyboard' => true
            ]),
        ]);
    }
}

// History
if ($text == '/history') {
    $cgpa_records = mysqli_query($con, "SELECT * FROM `cgpa_records` WHERE `telegram_id` = '$chat_id' LIMIT 10");
    if (mysqli_num_rows($cgpa_records) > 0) {

        while ($row = mysqli_fetch_assoc($cgpa_records)) {
            $id = $row['id'];
            $date = date("Y-m-d", strtotime($row['created_at']));
            $gpa = number_format($row['gpa'], 2);
            $message = "📅 *$date* - 📊 *GPA:* `$gpa`\n";

            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => '🗑 Delete', 'callback_data' => 'delete_' . $id]]
                    ],
                ]),
            ]);
        }
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ No data found! \n\nClick `Calculate 🚀` to add CGPA",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => 'Calculate 🚀']]
                ],
                'resize_keyboard' => true
            ]),
        ]);
    }
}

// Delete CGPA
if (strpos($data, 'delete_') !== false) {
    $id = str_replace('delete_', '', $data);

    $del_cgpa = mysqli_query($con, "DELETE FROM `cgpa_records` WHERE `id` = '$id'");
    if ($del_cgpa) {
        bot('editMessageText', [
            'chat_id' => $chat_id2,
            'message_id' => $mid,
            'text' => "✅ Data removed successfully",
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