<?php
session_start();
$GLOBALS['mysql'] = new mysqli('localhost', 'root', 'root', 'messages_without_hashtags', 8889);


function add_hashtag($message_text) {
    $mysql = $GLOBALS['mysql'];
    $regexp = "/#[A-zА-я]+/u";
    preg_match_all($regexp, $message_text, $matches);
    $matches = $matches[0];
    if (count($matches)) {
        $formed_query = "INSERT INTO hashtags (data) VALUES ";
        $values_list = [];
        $select_list = [];
        foreach ($matches as $val) {
            $select_list[] = "'$val'";
        }
//        Select hashtags which already in db
        $included = [];
        $select_query = sprintf("SELECT data FROM hashtags where data in (%s)",
            implode(", ", $select_list));
        foreach ($mysql->query($select_query) as $value) {
            $included[] = $value['data'];
        }
//        Filter inserted hashtags
        foreach ($matches as $val) {
            if (!in_array($val, $included)) {
                $values_list[] = "('$val')";
            }
        }
        if (count($values_list)) {
            $formed_query .= implode(", ", $values_list);
            $mysql->query($formed_query);
        }
        return $matches[0];
    }
}

$add_message = function (){
    $message_text = $_POST['message_text'];
    $mysql = $GLOBALS['mysql'];
    $hashtag_name = add_hashtag($message_text);
    echo $hashtag_name;
    if (isset($hashtag_name) | isset($_POST['hashtag'])) {
        if (isset($_POST['hashtag'])) {
            $hashtag_name = $_POST['hashtag'];
            add_hashtag($hashtag_name);
        }
        $query = "INSERT INTO messages (message_data, `#_id`) values ('$message_text', (select id from hashtags where data = '$hashtag_name'))";
        $mysql->query($query);
        $_SESSION['need_hashtag'] = false;
        $_SESSION['message'] = "";
;
    }
    else {
        $_SESSION['need_hashtag'] = true;
        $_SESSION['message'] = $message_text;
    }

};

function show_messages(){
    $mysql = $GLOBALS['mysql'];
    $list =[];
    foreach ($mysql->query("SELECT message_data from messages") as $val) {
        $list[] = "<li>".$val['message_data']. "</li>";
    }
    if (count($list)) {
        return implode("\n", $list);
    }
    return "";
}

function show_hashtags() {
    $mysql = $GLOBALS['mysql'];
    $list =[];
    foreach ($mysql->query("SELECT data from hashtags") as $val) {
        $list[] = "<li>".$val['data']. "</li>";
    }
    if (count($list)) {
        return implode("\n", $list);
    }
    return "";
}

$methods = [
    'add_message' => $add_message
];
if (isset($_GET['method']) | isset($_POST['method'])) {
    if (isset($_GET['method'])){
        $methods[$_GET['method']]();
    }
    else{
        $methods[$_POST['method']]();
    }
    header("Location: /");
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Messages</title>
</head>
<body>
<h1>Hashtag sorter</h1>

<h2>Сообщения</h2>
<ul>
    <?=show_messages()?>
</ul>

<h2>Хештеги</h2>
<ul>
    <?=show_hashtags()?>
</ul>
<form action="/" method="post" accept-charset="utf-8">
    <input type="hidden" name="method" value="add_message">
    <?php if($_SESSION['need_hashtag']) {
        echo "<p>Вы не ввели в сообщении ни одного хештега. Пожалуйста укажите его в этом поле</p>";
        echo "<input type='text' name='hashtag'>";
    }?>
    <input type="text" name="message_text" placeholder="Сообщение" value="<?=$_SESSION['message']?>">
    <button type="submit">Отправить</button>
</form>
</body>
</html>
