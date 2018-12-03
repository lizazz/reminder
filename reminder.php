<?php
echo "hello reminder";
$user = 'root';
$pass = 'vagrant';

try {
    $time = time();
    $time = '1542672000';
    $Start_days_ago = $time - 60*60*24*4;
    $end_days_ago = $time - 60*60*24*3;
    $dbh = new PDO('mysql:host=localhost;dbname=eleksun', $user, $pass);
    $sqlQuery = '
      SELECT commerce_order.order_id, commerce_line_item.line_item_label, commerce_product.title, commerce_product.product_id from commerce_order
      LEFT JOIN commerce_line_item ON (commerce_line_item.order_id = commerce_order.order_id)
      LEFT JOIN commerce_product ON (commerce_product.sku = commerce_line_item.line_item_label)
        WHERE commerce_order.status = "completed" AND 
        (commerce_order.created BETWEEN ' . $Start_days_ago . ' AND ' . $end_days_ago . ')';
    echo $sqlQuery;
    echo "<pre>";
    foreach($dbh->query($sqlQuery) as $row) {
        print_r($row);
    }
    echo '</pre>';
    $dbh = null;
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>