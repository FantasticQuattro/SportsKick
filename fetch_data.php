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
.button {
    color: #494949  !important;
    text-transform: uppercase;
    background: #ffffff;
    padding: 15px;
    border: 2px solid #494949  !important;
    border-radius: 2px;
    display: inline-block;
    }
.button:hover {
    color: #20bf6b !important;
    border-radius: 15px;
    border-color: #20bf6b !important;
    transition: all 0.3s ease 0s;
    }
p{    
   
    font-family: 'Nunito';
    font-size: 15px;
    font-weight: 400;
    padding: 10px 10px;
}
h4{    
    
    font-family: 'Nunito';
    font-size: 21px;
    font-weight: 400;
    padding: 5px;
}
    
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
      $distance = 15;
  }

$searchText = explode(' ',$_POST["SearchText"]);
 $query = "SELECT * FROM places WHERE Status =1 AND ((Category LIKE '%".$searchText[0]."%' OR Name LIKE '%".$searchText[0]."%'  OR Type LIKE '%".$searchText[0]."%')";
    for($i=1;$i<count($searchText); $i++){
    if(!empty($searchText[$i])){
        $query.="OR (Category LIKE '%".$searchText[$i]."%' OR Name LIKE '%".$searchText[$i]."%' OR Type LIKE '%".$searchText[$i]."%')";
    }
}
    $query.=")";
    
    if(isset($_POST['userLat']) && isset($_POST['userLng'])){
        $current_lat = $_POST['userLat'];
        $current_lon = $_POST['userLng'];
        //echo "Inside set".$current_lat;
    }
    else{
        $current_lat = '';
        $current_lon = '';
    }
    if(isset($_POST['suburbLocation'])){
        if($_POST['suburbLocation'] != ''){
          $query.= "AND Address LIKE '%".$_POST['suburbLocation']."%'" ; 
        } 
    }
    if(isset($_POST['quickLinks'])){
        if($_POST['quickLinks']!=''){
            $query.="AND Type LIKE '%".$_POST['quickLinks']."%'";
        }
        else{
            $_POST['quickLinks']='';
        }
    }
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
    if($current_lat!='' and $current_lon!=''){
        $earths_radius = 6371;
        $query .= "ORDER BY acos(sin($current_lat) * sin(latitude) + cos($current_lat) * cos(latitude) * cos(longitude- ($current_lon))) * $earths_radius"; 
    }
 
    
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
       $distancePlace = getDistance($row['Latitude'],$row['Longitude'], $current_lat, $current_lon);
//          echo $distancePlace;
          //echo "Current Lat ".$current_lat;
          if($current_lat != '' and $current_lon != ''){
              
              if($_POST['suburbLocation']!='' or $_POST["SearchText"]!=''){
       $output .= '   
       <div class="col-md-6 col-md-6">
        <div style="border:1px solid #ff7f3b; border-radius:5px; padding:16px; margin-bottom:16px; height:450px;">

         <h4 align="center" style="color:MediumSeaGreen;"><strong>'. $row['Name'] .'</strong></h4>
       
         <p>
         <b>Type :</b> <br />'. $row['Type'] .' <br /><br />
         <b>Category :</b> <br />'. $row['Category'] .' <br /><br />
         <b>Address :</b> <br />'. $row['Address'] .'<br /><br />
         
         <b>Distance :</b> <br /> 
          <font color="green">'. $distancePlace .' KM </font>
         <br /><br />

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
              else if($distancePlace<=$distance){
                  $output .= '   
       <div class="col-md-6 col-md-6">
        <div style="border:1px solid #ff7f3b; border-radius:5px; padding:16px; margin-bottom:16px; height:450px;">

         <h4 align="center" style="color:MediumSeaGreen;"><strong>'. $row['Name'] .'</strong></h4>
       
         <p>
         <b>Type :</b> <br />'. $row['Type'] .' <br /><br />
         <b>Category :</b> <br />'. $row['Category'] .' <br /><br />
         <b>Address :</b> <br />'. $row['Address'] .'<br /><br />
         
         <b>Distance :</b> <br /> 
          <font color="green">'. $distancePlace .' KM </font>
         <br /><br />

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
          else {
              $output .= '   
       <div class="col-md-6 col-md-6">
        <div style="border:1px solid #ff7f3b; border-radius:5px; padding:16px; margin-bottom:16px; height:450px;">

         <h4 align="center" style="color:MediumSeaGreen;"><strong>'. $row['Name'] .'</strong></h4>
       
         <p>
         <b>Type :</b> <br />'. $row['Type'] .' <br /><br />
         <b>Category :</b> <br />'. $row['Category'] .' <br /><br />
         <b>Address :</b> <br />'. $row['Address'] .'<br /><br />
         
       
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
      }}
     else
     {
      $output = '<br/><br/><center><h4>No Places Found. Please Extend Scope!</h4></center>';
     }
     echo $output;

    }

    function getDistance($current_lat, $current_lon,$latitudeTo, $longitudeTo, $earthRadius = 6371)
    {
    
        $latFrom= deg2rad((double)$current_lat);
    $lonFrom= deg2rad((double)$current_lon);
    $latTo = deg2rad((double)$latitudeTo);
        $lonTo = deg2rad((double)$longitudeTo);
        $latDelta = $latTo - $latFrom;
  $lonDelta = $lonTo - $lonFrom;

  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return round($angle * $earthRadius,3);
    }
?> 
</html>

    

