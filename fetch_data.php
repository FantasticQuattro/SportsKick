<!DOCTYPE html>
<html>
<head>
<title>fetch_data</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">    
     <link rel="icon" href="images/webLogo.png" type="image/jpg" sizes="16*16">
<link rel="icon" href="images/webLogo.png" type="image/jpg" sizes="16*16">
		<!--coustom css-->
        <link href="css/style.css" rel="stylesheet" type="text/css"/>

<style>
.button {align-self: center; color: coral; background-color: floralwhite; align-content: center; font-weight: bold}
</style>

</head> 
    
<!--DISTANCE 1: get current location-->
<script> 
    navigator.geolocation.getCurrentPosition(function(location) {
    current_lat=location.coords.latitude;
    current_lon=location.coords.longitude;

    });
</script>
    
<?php  

    
if(isset($_POST["action"]))
{
  //Order Distance: add for order distance
  if(isset($_POST["Distance"])){
    $Distance_filter = $_POST["Distance"];
    $distance = (int)$Distance_filter;
 
  }else
  {
      $distance = 1000;
  }
 //add these 'if' because sometimes cookie not work, set default location: monash
  if(!isset($_COOKIE['userLat'])){
      $userLat = -37.877111;
      setcookie("userLat",$userLat);
  }
  $current_lat = $_COOKIE['userLat'];
  
  if(!isset($_COOKIE['userLng'])){
      $userLng = 145.043750;
      setcookie("userLng",$userLng);
  }
  $current_lon = $_COOKIE['userLng'];
  
  $earths_radius = 6371;
   
 $searchText = explode(' ',$_POST["SearchText"]);
 $query = "SELECT * FROM places WHERE Status =1 AND (Category LIKE '%".$searchText[0]."%' OR Name LIKE '%".$searchText[0]."%'  OR Type LIKE '%".$searchText[0]."%'";
    for($i=1;$i<count($searchText); $i++){
    if(!empty($searchText[$i])){
        $query.="OR Category LIKE '%".$searchText[$i]."%' OR Name LIKE '%".$searchText[$i]."%' OR Type LIKE '%".$searchText[$i]."%'";
    }
}
 $query.=") AND Type LIKE '%".$_POST['quickLinks']."%' AND Address LIKE '%".$_POST['suburbLocation']."%'";
 $query.= "AND acos(sin($current_lat) * sin(latitude) + cos($current_lat) * cos(latitude) * cos(longitude- ($current_lon))) * $earths_radius <= $distance ";
    
 if(isset($_POST["Type"]))
 {
  $Type_filter = implode("','", $_POST["Type"]);
  $query .= "AND Type IN('".$Type_filter."')";
 }
 if(isset($_POST["Category"]))
 {
  $Category_filter = implode("','", $_POST["Category"]);
  $query .= "AND Category IN('".$Category_filter."')";
 }
    
  //add for distance radius
  $query .= "ORDER BY acos(sin($current_lat) * sin(latitude) + cos($current_lat) * cos(latitude) * cos(longitude- ($current_lon))) * $earths_radius";

    
  getData($query);
}
    
    //DISTANCE 2: calculate distance ; step 3.in the php loop
    function getDistance($latitudeTo, $longitudeTo)
    {
    $latitudeFrom= $_COOKIE['userLat'];
    $longitudeFrom=  $_COOKIE['userLng'];
    $rad = M_PI / 180;
    //Calculate distance from latitude and longitude
    $theta = $longitudeFrom - $longitudeTo;
    $dist = sin($latitudeFrom * $rad) * sin($latitudeTo * $rad) +  cos($latitudeFrom * $rad) * cos($latitudeTo * $rad) * cos($theta * $rad);
    return number_format((float)(acos($dist) / $rad * 60 *  1.853), 2, '.', ''); 

    }
 
    function getData($query)
    {
        
        include('database_connection.php');
        $statement = $connect->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $total_row = $statement->rowCount();
        $output = '';

     if($total_row > 0)
     {
         ?>

    <center><h4>We have found <?php echo $total_row ?> result(s)
        <?php 
         if(isset($_POST["Type"])){
            $Type_filter = implode(", ", $_POST["Type"]);
            echo "about ";
            echo "$Type_filter";
          }else
          {
            echo NULL;
          }
        
        ?>
        for you.
        </h4></center>
    <br/>
    <?php
      foreach($result as $row)
      {
       $distance = getDistance($row['Latitude'],$row['Longitude']);
       $output .= '   
       <div class="col-md-6">
        <div style="border:1px solid #ff7f3b; border-radius:5px; padding:16px; margin-bottom:16px;">

         <h4 align="center" style="color:MediumSeaGreen;"><strong>'. $row['Name'] .'</strong></h4>
         <br />
         <p>
         <b>Type :</b> <br />'. $row['Type'] .' <br /><br />
         <b>Category :</b> <br />'. $row['Category'] .' <br /><br />
         <b>Address :</b> <br />'. $row['Address'] .'<br /><br />
         
         <b>Distance :</b> <br /> 
          <font color="green">'. $distance .' KM </font>
         <br />

         <form method="post" action="detailPage.php">
         <input type="hidden" name="Type" value="'. $row['Type'] .'">
         <input type="hidden" name="Category" value="'. $row['Category'] .'" >
         <input type="hidden" name="Address" value="'. $row['Address'] .'">
         <input type="hidden" name="Name" value="'. $row['Name'] .'">
         <input type="hidden" name="longitude" value="'. $row['Longitude'] .'">
         <input type="hidden" name="latitude" value="'. $row['Latitude'] .'">
         <input type="hidden" name="ID" value="'. $row['ID'] .'">
         <input type="hidden" name="localPhNum" value="'.$row['Local_Phone_Number'].'">
         <input type="hidden" name="internationalPhNum" value="'.$row['International_Phone_Number'].'">
         <input type="hidden" name="website" value="'.$row['Website'].'">
         <b><center><input type="submit" class="button" value= "More Details"></center></b>
         </form>

         </p>

        </div>
       </div>
       ';   

      }
     }
     else
     {
      $output = '<br/><br/><center><h4>No Places Found. Please Extend Scope!</h4></center>';
     }
     echo $output;

    }

    
?> 
</html>

    

