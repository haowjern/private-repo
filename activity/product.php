<?php
session_start();


if (isset($_SESSION["editlisting"])) {

    //connect to database
    include '../database.php';
    include 'photos_interface.php';

    //set the variables with session values and escape mysql characters
    $product_name=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["product_name"]);
    $productID=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["productID"]);
    $product_description=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["product_description"]);
    $start_price=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["start_price"]);
    $reserve_price=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["reserve_price"]);
    $quantity=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["quantity"]);
    $conditionname=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["conditionname"]);
    $categoryname=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["categoryname"]);
    $sellerID=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["sellerID"]);
    $auctionable=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["auctionable"]);
    $startdate=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["startdate"]);
    $enddate=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["enddate"]);
    $endtime=mysqli_real_escape_string($connection,$_SESSION["editlisting"]["endtime"]);
    $photos_file_path= $_SESSION["editlisting"]["photos"]["file_path"]; // as this can contain multiple photos, each photo is indexed with a key starting from 0 in the order they were added. 

    if ($auctionable=="Yes"){
        $auctionable="1";
      } else{
      $auctionable="0";}

    //look up the categoryID and conditionID based on user input
    $sql="SELECT categoryID FROM Category WHERE categoryname='$categoryname'";
        $result=$connection->query($sql);
        if ($result==TRUE){
            $row=$result->fetch_assoc();
            $categoryID=$row['categoryID'];
        } else {
        echo "Error: ". $sql . "<br>" . $connection->error;
        }

    $sql="SELECT conditionID FROM ConditionIndex WHERE conditionname='$conditionname'";
        $result=$connection->query($sql);
        if ($result==TRUE){
            $row=$result->fetch_assoc();
            $conditionID=$row['conditionID'];
        } else {
        echo "Error: ". $sql . "<br>" . $connection->error;
        }

    if ($productID=="new"){
        //insert new row into product database with new productID
        $sql="INSERT INTO Product (product_name,product_description,start_price,reserve_price,quantity,categoryID,conditionID,sellerID,auctionable,startdate,enddate,endtime) 
            VALUES('$product_name','$product_description','$start_price','$reserve_price','$quantity','$categoryID','$conditionID','$sellerID','$auctionable','$startdate','$enddate','$endtime')";

        if ($connection->query($sql)==TRUE){
        echo "New record successfully created for product";
        } else {
        echo "Error: ". $sql . "<br>" . $connection->error;
        }
      
        //fetch new productID
        $sql="SELECT LAST_INSERT_ID()";
        $result=$connection->query($sql);
        if ($result->num_rows>0) {
            while($row = $result->fetch_assoc()){
                $productID = $row["LAST_INSERT_ID()"];
            }
        } else {
            echo("Error: " . $sql . "<br>" . $connection->error);
        }

        // upload photo to database
        foreach ($photos_file_path as $file_index=>$val) {
            
            $photo_arr['productID'] = $productID;
            $photo_arr['photoID'] = 0; 
            $photo_arr['file_path'] = mysqli_real_escape_string($connection, $val);

            $new_photo_arr= set_photo($photo_arr, "insert");
            $_SESSION["editlisting"]["photos"]["photoID"][$file_index] = $new_photo_arr['photoID']; // update photos with new photoID
        }

    }else{
        //for existing product, startdate cannot be changed. 
        $sql="UPDATE Product 
            SET product_name='$product_name',
            product_description='$product_description',
            start_price='$start_price', 
            reserve_price='$reserve_price',
            quantity='$quantity',
            categoryID='$categoryID',
            conditionID='$conditionID',
            auctionable='$auctionable',
            enddate='$enddate',
            endtime='$endtime'
            WHERE productID=$productID";

        if ($connection->query($sql) === TRUE) {
            echo "Record updated successfully for Product table";
        } else {
            echo "Error updating record: " . $connection->error;
        }
    }

    $connection->close();



//add item to auction table if it is auctionable
  if ($auctionable==1){
    // call the query file to insert item into auction table
  }
}

$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$extra = 'sellershop.php';
$link="http://".$host.$uri."/".$extra;

header("Location: ../index.php");

//empty the session element
unset($_SESSION["editlisting"]);
unset($_SESSION["original_start_price"]);
?>
