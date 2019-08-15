<?php

require_once('GetContinent.php');

if (isset($_FILES) && $_FILES['inputfile']['error'] == 0) { // Проверяем, загрузил ли пользователь файл
    $destiation_dir = dirname(__FILE__) . '/' . $_FILES['inputfile']['name']; // Директория для размещения файла
    move_uploaded_file($_FILES['inputfile']['tmp_name'], $destiation_dir); // Перемещаем файл в желаемую директорию
} else {
    echo 'No File Uploaded'; // Оповещаем пользователя о том, что файл не был загружен
}

$array  = array();
$i      = 0;
$fields = array("id", "date", "time", "phone", "ip_address");
$handle = @fopen("cdrs.csv", "r");

if ($handle) {
    while (($row = fgetcsv($handle, 4096)) !== false) {
        if (empty($fields)) {
            $fields = $row;
            continue;
        }
        foreach ($row as $k => $value) {
            $array[$i][$fields[$k]] = $value;
        }
        $cont  = GetContinent::get_curl("http://api.ipstack.com/" . $array[$i]["ip_address"] .
         "access_key=d9f000dbc0237078dfb39bf8033d244c");

        $array[$i]["continent_code"] = $cont['continent_code'];
        $array[$i]["continent_name"] = $cont['continent_name'];

        $i++;
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}

foreach ($array as $key => $val) {

    $array_filter[$key] = $val['id'];
}


$array_uni = array_unique($array_filter);
asort($array_uni);

$id_count = [];

foreach ($array_uni as $country_item) {

    $id_count[$country_item] = count(array_keys($array_filter, $country_item));
}

?>

<!DOCTYPE HTML>
<html lang="ru-RU">
    <head>
        <meta charset="UTF-8">
        <title>Statistic</title>
    </head>

    <body>

    <center><h1> Statistic </h1></center>

    <div class="messages">

        <table class="table table-bordered" border="1" width="100%" bgcolor="#0">
            <tr><th style="text-align:center"><font color=white</font>User ID</th>  <th style="text-align:center"><font color=white</font>Number_of_calls_on_one_continent</th> <th style="text-align:center"><font color=white</font>Total_call_duration_on_one_continent</th> <th style="text-align:center"> <font color=white</font>Total_number_of_calls</th>
                <th style="text-align:center"> <font color=white</font>Total_duration_of_all_calls</th> </tr>

            <?php

            foreach ($id_count as $key => $value) {
                $sum = 0;
                foreach ($array as $k => $v) {
                    if ($v['id'] == $key) {
                        $sum = $sum + $v['time'];
                    }
                }
                ?>

                <tr bgcolor="#FFFFE1">
                    <td style="text-align:center"><?php echo $key; ?> </td><td style="text-align:center"><?php echo $value['date']; ?></td><td style="text-align:center"><?php echo $value['phone']; ?></td>
                    <td style="text-align:center"><?php echo $value; ?></td><td style="text-align:center"><?php echo $sum; ?></td> 
                </tr>

            <?php } ?>

        </table>

    </div>

</body>
</html>