<?php
    #---------------------------------------------------------------------------------
    # debug settings
    #---------------------------------------------------------------------------------
    if (1 == 2) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
    #---------------------------------------------------------------------------------
    # 
    #---------------------------------------------------------------------------------
    require $_SERVER['DOCUMENT_ROOT'] . '/db/utils.php';
    #---------------------------------------------------------------------------------
    # check autorization
    #---------------------------------------------------------------------------------
    if (!isAuthorized()) {
        die(err2echo(23,'', ''));
    }
    #---------------------------------------------------------------------------------
    # connect to db
    #---------------------------------------------------------------------------------
    require $_SERVER['DOCUMENT_ROOT'] . '/db/conn.php';
    #---------------------------------------------------------------------------------
    # get values
    #---------------------------------------------------------------------------------
    isset($_GET["year"])  ? $year  = $_GET["year"]  : die(err2echo(24, '', ''));
    isset($_GET["month"]) ? $month = $_GET["month"] : die(err2echo(25, '', ''));
    #
    $dayin  = $year . "-" . $month . "-" . 01;
    $dayout = $year . "-" . $month . "-" . cal_days_in_month(CAL_GREGORIAN, $month, $year);
    #---------------------------------------------------------------------------------
    # get guest list
    #---------------------------------------------------------------------------------
    $query = "SELECT * FROM gl001 WHERE dayout >= ? AND dayin  <= ?";
    #
    !($stmt = $mysqli->prepare($query))         && die(err2echo(10, "Выборка Список гостей. ", $mysqli));
    !($stmt->bind_param('ss', $dayin, $dayout)) && die(err2echo(11, "Выборка Список гостей. ", $mysqli));
    !($stmt->execute())                         && die(err2echo(12, "Выборка Список гостей. ", $mysqli));   
    !($result = $stmt->get_result())            && die(err2echo(15, "Выборка Список гостей. ", $mysqli));   
    $rows = []; 
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    #
    $data['data'] = $rows;
    #
    $data['total'] = mysqli_num_rows($result);
    #
    $data['year'] = $year;
    $data['month'] = $month;
    #---------------------------------------------------------------------------------
    # close connection
    #---------------------------------------------------------------------------------
    $stmt->free_result();
    $stmt->close();
    $mysqli->close();
    #---------------------------------------------------------------------------------
    # send result
    #---------------------------------------------------------------------------------
    $data['status'] = true;
    echo json_encode($data);
?>