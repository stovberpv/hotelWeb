<?php 

    require $_SERVER['DOCUMENT_ROOT'] . '/hm/php/db/conn.php';

    //-------------------------------------------------------------------------------------------------
        // get values from ajax
    //-------------------------------------------------------------------------------------------------
    if (isset($_GET["year"]))      { $year      = $_GET["year"];      } else { $year      = ""; };
    if (isset($_GET["month"]))     { $month     = $_GET["month"];     } else { $month     = ""; };
    if (isset($_GET["dayin"]))     { $dayin     = $_GET["dayin"];     } else { $dayin     = ""; }
    if (isset($_GET["dayout"]))    { $dayout    = $_GET["dayout"];    } else { $dayout    = ""; }
    if (isset($_GET["room"]))      { $room      = $_GET["room"];      } else { $room      = ""; }
    if (isset($_GET["price"]))     { $price     = $_GET["price"];     } else { $price     = ""; }
    if (isset($_GET["paid"]))      { $paid      = $_GET["paid"];      } else { $paid      = ""; }
    if (isset($_GET["name"]))      { $name      = $_GET["name"];      } else { $name      = ""; }
    if (isset($_GET["tel"]))       { $tel       = $_GET["tel"];       } else { $tel       = ""; }
    if (isset($_GET["info"]))      { $info      = $_GET["info"];      } else { $info      = ""; }
    if (isset($_GET["sessionId"])) { $sessionId = $_GET["sessionId"]; } else { $sessionId = ""; }

    //-------------------------------------------------------------------------------------------------
        // Выборка 0
        // TODO: session ID
    //-------------------------------------------------------------------------------------------------
    /*
    $query = "SELECT user FROM sessions WHERE session = ?";
    if (!($stmt = $mysqli->prepare($query))) { err2echo(10, "Выборка 0. ", $mysqli); }
    if (!$stmt->bind_param('s', $sessionId)) { err2echo(11, "Выборка 0. ", $mysqli); }
    if (!$stmt->execute())                   { err2echo(12, "Выборка 0. ", $mysqli); }   
    if (!$stmt->bind_result($user))          { err2echo(13, "Выборка 0. ", $mysqli); }
    if (!$stmt->fetch())                     { err2echo(14, "Выборка 0. ", $mysqli); }
    
    $stmt->close();
    */
    $user = $sessionId;

    //-------------------------------------------------------------------------------------------------
        // setup values
    //-------------------------------------------------------------------------------------------------
    $begda = "";
    if(strpos($dayin, ".")) {
        $date = explode(".", $dayin);
        $m = $date[1];
        $d = $date[0];
        if(checkdate($m, $d, $year)) {
            $begda = $year . "-" . $m . "-" . $d;
        } else {
            die(err2echo(22, 'Добавление гостя', $mysqli));
        }
    } else {
        $begda = $year . "-" . $month . "-" . $dayin;
    }

    $endda = "";
    if(strpos($dayout, ".")) {
        $date = explode(".", $dayout);
        $m = $date[1];
        $d = $date[0];
        if(checkdate($m, $d, $year)) {
            $endda = $year . "-" . $m . "-" . $d;
        } else {
            die(err2echo(21, 'Добавление гостя', $mysqli));
        }
    } else {
        $endda = $year . "-" . $month . "-" . $dayout;
    }

    if (strtotime($begda) > strtotime($endda)) {
        die(err2echo(20, 'Добавление гостя', $mysqli));
    }

    $timestamp = date('Y-m-d H:i:s');

    //-------------------------------------------------------------------------------------------------
        // prepare queryes
    //-------------------------------------------------------------------------------------------------
    $query = "INSERT
                INTO gl001(dayin, dayout, room, price, paid, name, tel, info, user, timestamp)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    //-------------------------------------------------------------------------------------------------
        // execute query 1
    //-------------------------------------------------------------------------------------------------
    if (!($stmt = $mysqli->prepare($query))) { die(err2echo(10, 'Добавление гостя', $mysqli)); }
    if (!$stmt->bind_param('ssiddsssss', $begda, $endda, $room, $price, $paid, $name, $tel, $info, $user, $timestamp)) { die(err2echo(11, 'Добавление гостя', $mysqli)); }
    if (!$stmt->execute()) { die(err2echo(12, 'Добавление гостя', $mysqli)); }

    //-------------------------------------------------------------------------------------------------
        // 
    //-------------------------------------------------------------------------------------------------
    $data['data'][0]['id'] = mysqli_insert_id($mysqli);
    $data['data'][0]['dayin'] = $begda;
    $data['data'][0]['dayout'] = $endda;
    $data['data'][0]['room'] = $room;
    $data['data'][0]['price'] = $price;
    $data['data'][0]['paid'] = $paid;
    $data['data'][0]['name'] = $name;
    $data['data'][0]['tel'] = $tel;
    $data['data'][0]['info'] = $info;

    $data['year'] = $year;
    $data['month'] = $month;

    //-------------------------------------------------------------------------------------------------
        // send result
    //-------------------------------------------------------------------------------------------------
    echo json_encode($data);
    
    //-------------------------------------------------------------------------------------------------
        // close connection
    //-------------------------------------------------------------------------------------------------
    $stmt->free_result();
    $stmt->close();

    $mysqli->close();

    function err2echo($id, $text, $conn) {
        $error = "";
        $errno = 666;
        switch ($id) {
            case 0 : $error = "Ошибка подключения: (" . $conn->connect_error . ") "; $errno = $conn->connect_errno;   break;
            case 10: $error = "Ошибка подготовки: ("  . $conn->error         . ") "; $errno = $conn->errno;           break;
            case 11: $error = "Ошибка привязки: ("    . $conn->error         . ") "; $errno = $conn->errno;           break;
            case 12: $error = "Ошибка выполнения: ("  . $conn->error         . ") "; $errno = $conn->errno;           break;
            case 13: $error = "Ошибка переменных: ("  . $conn->error         . ") "; $errno = $conn->errno;           break;
            case 14: $error = "Ошибка выборки: ("     . $conn->error         . ") "; $errno = $conn->errno;           break;
            case 15: $error = "Ошибка результата: ("  . $conn->error         . ") "; $errno = $conn->errno;           break;
            case 20: $error = "Начальная дата не может быть больше конечной"       ;                                  break;
            case 21: $error = "Не удалось вычислить день и месяц выезда"           ;                                  break;
            case 22: $error = "Не удалось вычислить день и месяц въезда"           ;                                  break;
            default: break;
        }

        $errTable = 
        "
        <br/>
        <br/>
        <table class='errorTable-MySQLi' border='1' cellspacing='0' cellpadding='3' style='border-color: black; width: auto; height: auto; margin-left: auto; margin-right: auto'>
            <thead style='background-color: Crimson; color: white; font-weight: bold; text-align:center'>
                <tr>
                    <td colspan='4'>Ошибка</td>
                </tr>
                <tr>
                    <td>ID</td>
                    <td>Источник</td>
                    <td>Номер</td>
                    <td>Описание</td>
                </tr>
            </thead>
            <tbody style='background-color: Cornsilk; color: SlateGray; text-align: left'>
                <tr>
                    <td>" . $id . "</td>
                    <td>" . $text . "</td>
                    <td>" . $errno . "</td>
                    <td>" . $error . "</td>
                </tr>
            </tbody>
        </table>";

        echo $errTable;
    }
    
?>
