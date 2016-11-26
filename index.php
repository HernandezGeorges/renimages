<?php

ini_set('default_charset','utf-8');

// tri de tableau en ordre croissant et de manière récursive
function RecurSortArray(&$aDatas) {
    ksort($aDatas);
    foreach ($aDatas as &$a) {
        if (is_array($a) && !empty($a)) RecurSortArray($a);
    }
}

// recupération des données exif des images
function imagesExifDatasArray($dir) {
    $aFilesDatas = array();
    $aIterators = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
    foreach($aIterators as $sName => $oIterator){
        if(strstr($oIterator, '.DS_Store')) continue;
        if(strstr($oIterator, '.svn')) continue;
        $ext = pathinfo($oIterator->getFilename(), PATHINFO_EXTENSION);
        if($ext && in_array($ext, array('jpg', 'jpeg', 'tif', 'tiff', 'png', 'bmp', 'webp', 'JPG', 'JPEG', 'TIF', 'TIFF', 'PNG', 'BMP', 'WEBP')) && $exif = exif_read_data($oIterator)) {
            if(isset($exif['DateTime'])){
                $chkDt = date_parse($exif['DateTime']);
                if($chkDt['warning_count']==0 && $chkDt['error_count']==0) {
                    $timestamp = mktime($chkDt['hour'],$chkDt['minute'],$chkDt['second'],$chkDt['month'],$chkDt['day'],$chkDt['year']);
                    $dayDate = date('Y-m-d', $timestamp);
                    $aFilesDatas[$dayDate][$timestamp][] = $oIterator;
                }
            }
        }
    }
    return $aFilesDatas;
}

// renommage des fichiers
function renameFilesFromDatasArray($aFilesDatas) {
    
    date_default_timezone_set('Europe/Paris'); 

    $j = 0;
    $html = '';
    $rename = "./renamed/";
    foreach($aFilesDatas as $ymd => $timestamp_filename){
        $i = 1;
        foreach($timestamp_filename as $timestamp => $filename_array) {
            foreach($filename_array as $key => $filename) {
                $num = str_pad($i, 3, "0", STR_PAD_LEFT);
                $dbl = empty($key)?'':'_'.$key;
                $end = explode('.',$filename);
                $lower = end($end);
                $ext = strtolower($lower);
                $dateheure = date("H",$timestamp)."h".date("i",$timestamp)."min";
                
                // option création de répertoires année / mois /
                $yeardir = date('Y',$timestamp)."/";
                $monthdir = date('M',$timestamp)."/";
                if(!is_dir($rename.$yeardir.$monthdir)) {
                    mkdir($rename.$yeardir.$monthdir, 0777, true);
                }
                
                $nouveaufichier = $rename.$yeardir.$monthdir.$ymd."_".$dateheure."_".$num.$dbl.".".$ext; 
                while(file_exists($nouveaufichier)){
                    $num = str_pad($i++, 3, "0", STR_PAD_LEFT);
                    $nouveaufichier = $rename.$yeardir.$monthdir.$ymd."_".$dateheure."_".$num.$dbl.".".$ext;
                }
                rename($filename, $nouveaufichier);
                $html .= '
                    <tr>
                        <td>'.$filename.'</td>
                        <td>
                            <div class="pops" data-content="<img src=\''.$nouveaufichier.'\' style=\'height:150px;\'/>" rel="popover">'.$nouveaufichier.'</div>
                        </td>
                    </tr>';
                
                $j++;
            }
            $i++;
        }
    }
    return array($html, $j);
}

$html = '';
$t = time();
if(!empty($_POST) && $_POST['time'] <= $t) {
    $rep = "./sources/";
    $files = imagesExifDatasArray($rep);
    RecurSortArray($files);
    $res = renameFilesFromDatasArray($files);
    $html = '
    <br>
    <div class="bs-example">
        <table class="table table-striped table-hover table-condensed">
            <tr>
                <th>Nombre total d\'images renommées</th>
                <th>'.$res[1].'</th>
            </tr>'.$res[0].'
        </table>
    </div>';
    unset($_POST);
}

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="bootstrap.css" rel="stylesheet" media="screen">
        <link href="docs.css" rel="stylesheet" media="screen">
    </head>
    <body>
        <div class="container">
            <form method="POST">
                <fieldset>
                    <legend>Cliquez sur le bouton pour renommer vos images</legend>
                    <input type="hidden" name="time" value="<?php echo time() ?>" />
                    <button type="submit" class="btn btn-primary">Lancer le renommage</button>
                </fieldset>
            </form>
            <?php echo $html; ?>
        </div>
        <script type="text/javascript" src="jquery.js"></script>
        <script type="text/javascript" src="bootstrap-tooltip.js"></script>
        <script type="text/javascript" src="bootstrap-popover.js"></script>
        <script type="text/javascript">
            $(function (){
                console.log('yep!');
                $("div[class=pops]").popover({placement:'left'});  
            });
        </script>
    </body>
</html>