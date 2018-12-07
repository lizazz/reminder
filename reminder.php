<?php


ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$reminder = new Reminder();

$reminder->index();

class Reminder
{
    public $dbh;

    function __construct()
    {
        $user = 'eleksun';
        $pass = 'DnhPJyrn1QERCzsa';
      //  $user = 'root';
      //  $pass = 'vagrant';
        $this->dbh = new PDO('mysql:host=localhost;dbname=eleksun;charset=utf8', $user, $pass);
    }

    function index()
    {
      //  echo "hello reminder";
        $time = time();
        $time = '1541203200';
        $Start_days_ago = $time - 60*60*24*2;
        $end_days_ago = $time - 60*60*24*1;
        $orders = [];

        try
        {

            /* $sqlQuery = '
               SELECT commerce_order.order_id, commerce_line_item.line_item_label, commerce_product.title, commerce_product.product_id from commerce_order
               LEFT JOIN commerce_line_item ON (commerce_line_item.order_id = commerce_order.order_id)
               LEFT JOIN commerce_product ON (commerce_product.sku = commerce_line_item.line_item_label)
                 WHERE commerce_order.status = "completed" AND
                 (commerce_order.created BETWEEN ' . $Start_days_ago . ' AND ' . $end_days_ago . ')';*/
            $sqlQuery = '
              SELECT commerce_order.order_id from commerce_order
                WHERE commerce_order.status = "completed" AND 
                (commerce_order.created BETWEEN ' . $Start_days_ago . ' AND ' . $end_days_ago . ')';
            //   echo $sqlQuery;
            //
            foreach ($this->dbh->query($sqlQuery) as $row)
            {
                if (isset($row['order_id']))
                {
                    $orders[$row['order_id']]['products'] = $this->getProduct($row['order_id']);
                    $orders[$row['order_id']]['customer'] = $this->getCustomer($row['order_id']);
                }

            }

            foreach ($orders as $orderId => $order) {
                $this->sendEmail($orderId, $order);
            }

            $dbh = null;
        } catch
        (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }


    function getProduct($order_id)
    {
        $products = [];
        $sqlQuery = '
          SELECT 
            commerce_line_item.line_item_label, 
            commerce_product.title, 
            commerce_product.product_id, 
            commerce_line_item.line_item_id, 
            field_data_field_product.entity_id 
          FROM commerce_line_item
          LEFT JOIN commerce_product ON (commerce_product.sku = commerce_line_item.line_item_label)
          LEFT JOIN field_data_field_product ON (field_data_field_product.field_product_product_id = commerce_product.product_id)
            WHERE commerce_line_item.order_id = ' . $order_id;

        foreach ($this->dbh->query($sqlQuery) as $row) {
            $products[$row['line_item_label']]['sku'] = $row['line_item_label'];
            $products[$row['line_item_label']]['title'] = $row['title'];
            $products[$row['line_item_label']]['product_id'] = $row['product_id'];
            $products[$row['line_item_label']]['url'] = 'https://eleksun.com.ua/node/' . $row['entity_id'];
        }

        return $products;
    }

    function getCustomer($order_id)
    {
        $customer = [];
        $sqlQuery = '
          SELECT commerce_customer_billing_profile_id,  field_data_field_user_email.field_user_email_email
          FROM field_data_commerce_customer_billing
          LEFT JOIN field_data_field_user_email ON (field_data_field_user_email.entity_id = field_data_commerce_customer_billing.commerce_customer_billing_profile_id)
            WHERE field_data_commerce_customer_billing.entity_id = ' . $order_id . ' LIMIT 1';

        foreach ($this->dbh->query($sqlQuery) as $row) {
            return $row['field_user_email_email'];
        }

    }

    function sendEmail($orderId, $order)
    {
           $to  = "eaklimka@gmail.com,alexandr.naboka.ee@gmail.com,slavikpetrechenko@gmail.com";

           $subject = "Интернет-магазин электротехники www.eleksun.com.ua - Вы довольны сервисом?";

           $message = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Интернет-магазин электротехники www.eleksun.com.ua - Вы довольны сервисом?</title>
</head>
<body style="background-color: #dce1e5">
    <div style="width: 100%; background-color: #207499; height: 80px;padding-left:50px">
        <table>
            <tr style="color:#ffd84a">
                <td style="width: 30%">
                    <a href="https://eleksun.com.ua" title="Интернет-магазин электрооборудования Eleksun" rel="home" id="logo">
                        <img src="https://eleksun.com.ua/sites/all/themes/eleksun/logo.png" alt="Интернет-магазин электрооборудования Eleksun" title="Интернет-магазин электрооборудования Eleksun">
                    </a>
                </td>

                <td style="padding-left:50px">099-428-33-17<br>098-564-65-57</td>
                <td></td>
                <td >График работы 9-18 / пн-пт</td>
            </tr>
        </table>
    </div>

    <div style="padding-left:50px">
        <p>Добрый день</p>
        <p>
            Вы делали заказ № ' . $orderId .' в нашем интернет-магазине <a href="https://eleksun.com.ua">www.eleksun.com.ua</a>.
        </p>
        <p>
            Нам было бы интересно узнать Ваше мнение о нашем товаре или об уровне сервиса и мы бы хотели Вас попросить оставить отзыв.
        </p>
        <p>
            Вы можете оставить отзыв об купленном товаре
        </p>
        <table style="border-collapse: collapse;">
            <tr>
                <th style="padding: 3px; border: 1px solid black;">Код</th>
                <th style="padding: 3px; border: 1px solid black;">Наименование</th>
            </tr>';

            foreach ($order['products'] as $product) {
            $message .= '
            <tr>
                <td style="padding: 3px; border: 1px solid black;">' . $product["sku"] . '</td>
                <td style="padding: 3px; border: 1px solid black;"><a href="' . $product["url"] . '">' . $product["title"]. '</a></td>
            </tr>';
            }

            $message .= '</table>
        
        <p>
            или об опыте работы с нами  на <a href="https://www.google.com.ua/search?q=eleksun&oq=eleksun&aqs=chrome..69i57j69i60l4j69i65.4023j0j7&sourceid=chrome&ie=UTF-8#lrd=0x4127a08c9fb72c89:0x1face26daadbe1e4,3,,,">google.maps</a>.
        </p>
        <p>
            В качестве благодарности мы готовы Вам предоставить скидку в размере 5% на следующую покупку.
        </p>
        <p>
            Для получения скидки Вам необходимо упомянуть об отзыве и дать ссылку на страницу с ним при следующем заказе
        </p>
    </div>
    <div style="  position: absolute;
  left: 0;
  bottom: 0;
  width: 100%;
  height: 80px;background-color: #aeb5bb;" class="footer"></div>
</body>
</html>';

           $headers  = "Content-type: text/html; charset=utf-8 \r\n";
           $headers .= "From: Eleksun <info@eleksun.com.ua>\r\n";
        //echo $message;
           mail($to, $subject, $message, $headers);
    }
}
?>