<?php
// Student Name: Shraddha Bhadkamkar
// display all errors on the browser
error_reporting(E_ALL);
ini_set('display_errors','On');

// put your generated access token here (should have No Expiration)
$auth_token = 'gui1TGpLe0AAAAAAAAAAAYvhlsjFwETr2OMYNRZ1esp4D2PSXDq-XwFEA3I6-Prl';

// set it to true to display debugging info
$debug = true;

if(isset($_GET['func'])){
  if(isset($_GET['image'])){
    $imageName = $_GET['image'];
    download($imageName,"images/".$imageName);
    echo('<div id = "imageDisplay" style="height: 600px; width: 1000px; border: 2px solid black"><img src="images/'.$imageName.'"/></div>');
  }   
}


if($_SERVER["REQUEST_METHOD"] == "POST"){     
    
    if(isset($_POST['displayList']))
    {
        $result = directoryList("");  
    }    

    if(isset($_POST['upload']))
    {             
        if(isset($_FILES["photo"])){       
            $filename = $_FILES["photo"]["tmp_name"];  
            upload($filename);   
        }            
    }
   
		if(isset($_POST['deleteImg']) AND !empty($_POST['deleteImg']))
		{	      
			$path = '/'.$_POST['deleteImg'];
			delete($path);
		}   
    
}

function directoryList ($path) {
    global $auth_token, $debug;
    $args = array("path" => $path);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $auth_token,
                'Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_URL, 'https://api.dropboxapi.com/2/files/list_folder');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
    try {
      $result = curl_exec($ch);
    } catch (Exception $e) {
      echo 'Error: ', $e->getMessage(), "\n";
    }
    if ($debug)
       //print_r($result);
    $array = json_decode(trim($result), TRUE);
    if ($debug)  
 
    foreach ($array['entries'] as $x) {         
        
        echo ('<ul><li><a href="?func=true&image='.$x['name'].'">'.$x['name'].'</a>&nbsp;<button onclick="deleteImage(\''.$x['name'].'\')">Delete</button><form id="del_form_id" method="post" enctype="multipart/form-data"><input type="hidden" name="deleteImg" value="'.$x['name'].'" ></form></li></ul>');
       
    }
    curl_close($ch);
    return $array;
 }

function delete($path){
  global $auth_token, $debug;
  $path = '/'.$_POST['deleteImg'];
  $args = array("path" => $path);  
  $ch = curl_init();
  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $auth_token,
                   'Content-Type: application/json', 'path: "/'.$path.'"'));
  curl_setopt($ch, CURLOPT_URL, 'https://api.dropboxapi.com/2/files/delete_v2');
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));

  try {
    $result = curl_exec($ch);
    echo "Image deleted succesfully!!  ";
    echo "Click on 'Display list of files' button to see updated list.";
  } catch (Exception $e) {
    echo 'Error: ', $e->getMessage(), "\n";
  }     
  if ($debug)    
  $array = json_decode(trim($result), TRUE);      
  curl_close($ch);
  return $array;
   
  curl_close($ch);  
}
 
function download ($path, $target_path ) {  
    global $auth_token, $debug;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $auth_token,
                   'Content-Type:', 'Dropbox-API-Arg: {"path":"/'.$path.'"}'));
    curl_setopt($ch, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/download');
    try {
      $result = curl_exec($ch);
      echo "Image downloaded successfully!! Please check images folder!";      
    } catch (Exception $e) {
      echo 'Error: ', $e->getMessage(), "\n";
    }
    file_put_contents($target_path,$result);
    curl_close($ch);
 }

 function upload ($path) {    
    global $auth_token, $debug;
    $args = array("path" => $path, "mode" => "add");
    $fp = fopen($path, 'rb');
    $size = filesize($path);
    $imgPath = $_FILES["photo"]["name"];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $auth_token,
                 'Content-Type: application/octet-stream',
              'Dropbox-API-Arg: {"path":"/'.$imgPath.'", "mode":"add"}'));
    curl_setopt($ch, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/upload');
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, $size);
    try {
      $result = curl_exec($ch);
      echo "Image uploaded successfully!!";
    } catch (Exception $e) {
      echo 'Error: ', $e->getMessage(), "\n";
    }
    if ($debug)      
    curl_close($ch);
    fclose($fp);
 }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">    
</head>
<body>
    <form action="album.php" method="post" enctype="multipart/form-data">
  
        <label for="fileSelect" style="font-size:25px">Filename:</label>
        <input type="file" name="photo" id="fileSelect" style="font-size:25px">
        <input type="submit" name="upload" value="Upload" style="font-size:25px">&nbsp;&nbsp;&nbsp;
        <input type="submit" name="displayList" value="Display list of files" style="font-size:25px"><br><br>        
        
    </form>
   

  <script type="text/javascript">
	
	function deleteImage(imageName)
	{    
		document.getElementById('del_form_id').submit();
	}
  </script>
  

</body>
</html>



