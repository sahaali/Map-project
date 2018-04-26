
<?
date_default_timezone_set('Canada/Central');
set_time_limit(0);
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);
$csv_uploaded_file_name = "uploaded_data.csv";
$csv_geocoded_file_name = "geocoded_data.csv";

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
  </head>
  <body>
    
    <div  class="container"  style="margin-top:30px;">
      <div class="jumbotron" style="padding:20px 30px">
        <h1>Upload new file:</h1>
        <?
            if(isset($_GET['done'])){
                echo '<div class="alert alert-success" style="width:400px;" role="alert">The <a href="csv/'.$csv_uploaded_file_name.'">file</a> has been uploaded.</div>';
            }
               
            if (file_exists("csv/" . $csv_uploaded_file_name)) {
                $fileDate = date ("F d Y H:i:s.", filemtime("csv/" . $csv_uploaded_file_name));
                echo '<div class="alert alert-info" style="width:400px;" role="alert">Current <a href="csv/'.$csv_uploaded_file_name.'">file</a>. Date: '.$fileDate.'</div>';
            }

        ?>
        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data">
            <input type="submit" name="submit" class="btn btn-sm btn-primary" />
            <input type="file" name="file" id="file" />
        </form>
      </div>
      <div class="jumbotron" style="padding:20px 30px">
        <h1>Run geocode:</h1>
        <?
            if (file_exists("csv/".$csv_geocoded_file_name)) {
                $fileDate = date ("F d Y H:i:s.", filemtime("csv/" . $csv_geocoded_file_name));
                echo '<div class="alert alert-info" style="width:400px;" role="alert">Geocoded <a href="csv/'.$csv_geocoded_file_name.'">file</a>. Date: '.$fileDate.'</div>';
            }

            if(isset($_GET['doneGeo'])){
                echo '<div class="alert alert-success" style="width:400px;" role="alert">The file has been Geocoded</div>';
            }
        ?>
        <a href="upload.php?runGeo" class="btn btn-sm btn-primary" />Run</a> *remove UNIT
      </div>
<?



/*
$rows   = array_map('str_getcsv', file('csv/'.$csv_uploaded_file_name));
$header = array_shift($rows);
$csv    = array();
foreach($rows as $row) {
    $csv[] = array_combine($header, $row);
}
var_dump($csv);
*/


if ( isset($_GET["runGeo"]) ) {
    
    if (!file_exists("csv/" . $csv_uploaded_file_name)) {
        echo '<div class="alert alert-danger" style="width:400px;" role="alert">No such file.</div>';
        exit();
    }
    geocode("csv/" . $csv_uploaded_file_name, "csv/".$csv_geocoded_file_name);
    //header("Location:upload.php?doneGeo");
    exit();

}

if ( isset($_POST["submit"]) ) {

    if ( isset($_FILES["file"])) {

        //if there was an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

        }
        else {
            //Print file details
            echo "Upload: " . $_FILES["file"]["name"] . "<br />";
            echo "Type: " . $_FILES["file"]["type"] . "<br />";
            echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
            echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

            //if file already exists
            if (file_exists("csv/" . $_FILES["file"]["name"])) {
                echo $_FILES["file"]["name"] . " already exists. ";
            }
            else {
                //Store file in directory "upload" with the name of "uploaded_file.txt"
               
                if (move_uploaded_file($_FILES["file"]["tmp_name"], "csv/" . $csv_uploaded_file_name)) {
                    echo "Stored in: " . "csv/" . $_FILES["file"]["name"] . "<br />";
                    header("Location:upload.php?done");
                    exit();
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        }
    } else {
            echo "No file selected <br />";
    }
}


function geocode($inputFileName, $outputFileName){
    
    $isFirst = true;
    $csvFile = file($inputFileName);
    $main = [];
    foreach ($csvFile as $line) {
        
        
        $data = str_getcsv($line);
        $addr = str_replace("UNIT ","",$data[3].', '.$data[7]);
        echo $addr.'<br>';
        
        if ($isFirst) { // skip the first row as it is column names
            $isFirst = false;
            $data[] = "lat"; // add column name for lat
            $data[] = "lng";// add column name for lng
            $main[] = $data;
            continue;
        }   
        /* ------------ GEOCODE --------------*/
        
        $i=0;
        $api_index = 0;
        $api = [
                'AIzaSyCvJM1wSf3LjwRnNog7gxnPmQyWcYBsA3U',
                'AIzaSyBMZj7338GtbomQV9xQyzwcZhES6uOhw80',
                'AIzaSyCANNotXGYMO9-woy5ckgAnQDzRYjRPBMI',
                'AIzaSyD4qmCXobQDBm4V3WYedbafE5zbLa7RFlo',
                'AIzaSyBKeO6ZCver3tZ1edCUEIBhelZu3klg9ic',
                'AIzaSyDYB0MjcxW-W-1gBNtlVq-__IX4iSwZQbk',
                'AIzaSyBY1fTeF2CUBSq4ko0vKPJa_RezcDMdFuM',
                'AIzaSyCQQPMjmIpiR8u9vdOqVyG5_R_vj5drseI',
                'AIzaSyAmzJ5Bp_tS8tk0U5gJc5CEZfKG2tk27Eg',
            ];
            
        echo $url = 'https://maps.googleapis.com/maps/api/geocode/json?key='.$api[$api_index].'&address=' . rawurlencode($addr).'&region=ca';
        echo '<br>';
        $json = json_decode(file_get_contents($url));
        if (@$json->error_message == "You have exceeded your daily request quota for this API."){
            $api_index++;
        }
        $lat_ = $json->results[0]->geometry->location->lat;
        $lng_ = $json->results[0]->geometry->location->lng;
        $data[] = $lat_;
        $data[] = $lng_;
        echo '<br>'.$lat_."|".$lng_;
        /* ------------ GEOCODE --------------*/
        
        echo '<hr>';
        $main[] = $data;
        flush();
    }

    $fp = fopen($outputFileName, 'w');
    foreach ($main as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);
}
?>

    </div>
  </body>
</html>