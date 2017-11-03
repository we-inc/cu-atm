# cu-atm
CU ATM

## ServiceAuthentication

คลาส `ServiceAuthentication` (ไฟล์ `app/ServiceAuthentication.php`)
ใช้สำหรับการยืนยันว่ามีหมายเลขบัญชีอยู่จริงด้วยเมธอด `accountAuthenticationProvider` โดยรับพารามิเตอร์ 1 ตัวคือหมายเลขบัญชี (string)

### วิธีการเรียกใช้
```php
<?php
include_once "./app/ServiceAuthentication.php";

try {
  $accountInfo = ServiceAuthentication::accountAuthenticationProvider("0123444667");
  print_r($accountInfo);
  
} catch (AccountInformationException $e) {
  echo $e->getMessage();
}
```
โดยที่
- `"0123444667"` คือหมายเลขบัญชี
- `$accountInfo` คือตัวแปรที่เก็บผลลัพธ์จากการเรียกใช้เมธอด `accountAuthenticationProvider` ซึ่งเป็น array มีโครงสร้างดังนี้
```php
Array
(
    [accNo] => 0123444667
    [accName] => Kritsada Kancha
    [accBalance] => 2000000
)
```
กรณีที่ไม่พบหมายเลขบัญชีดังกล่าวจะส่งผลลัพธ์มาเป็น `AccountInformationException` ออกมา
ตัวอย่าง
```php
Account number : 012344667 not found.
```
 
## DBConnection

คลาส `DBConnection` (ไฟล์ `app/DBConnection.php`) ให้บริการเชื่อมต่อฐานข้อมูลเพื่อ บันทึกยอดเงินคงเหลือ เรียกดูยอดค้างชำระ และเรียกดูข้อมูลบัญชี

### วิธีการบันทึกยอดเงินคงเหลือด้วยเมธอด `saveTransaction`
รับพารามิเตอร์ 2 ตัวคือ 
1. หมายเลขบัญชี (string) 
1. ยอดเงินคงเหลือที่ผ่านการทำรายการแล้ว (int)

```php
<?php
include_once "./app/DBConnection.php";

$result = DBConnection::saveTransaction("0123444667", 50000);

if ($result) {
    echo "Transaction was saved";
}
else {
    echo "Transaction failured";
}
```

กรณีที่บันทึกยอดเงินคงเหลือสำเร็จ จะคืนค่าเป็น `true` และเป็น `false` กรณีที่ไม่สำเร็จ

### วิธีการเรียกดูยอดค้างชำระด้วยเมธอด `getCharge`
รับพารามิเตอร์ 2 ตัวคือ 

1. หมายเลขบัญชี (string) 
1. ประเภทบริการโดยแบ่งเป็น 3 ประเภทดังนี้ (int)
  - 0 (`ServiceType::ELECTRIC_BILLING`) คือ ยอดค้างชำระค่าไฟฟ้า
  - 1 (`ServiceType::WATER_BILLING`) คือ ยอดค้างชำระค่าน้ำประปา
  - 2 (`ServiceType::PHONE_BILLING`) คือ ยอดค้างชำระค่าโทรศัพท์

```php
<?php
include_once "./app/DBConnection.php";

try {

  $charge = DBConnection::getCharge("0123444667", ServiceType::ELECTRIC_BILLING);

} catch (BillingException $e) {

  echo "BillingException: {$e->getMessage()}";
  
} catch (AccountInformationException $e) {

  echo "AccountInformationException: {$e->getMessage()}";
  
}
```
กรณีที่เรียกดูยอดค้างชำระสำเร็จจะได้ผลลัพธ์เป็นยอดค้างชำระ (int)
กรณีที่ระบุประเภทบริการไม่ถูกต้องจะส่ง `BillingException` ออกมา
กรณีที่ระบุหมายเลขบัญชีไม่ถูกต้องจะส่ง `AccountInformationException` ออกมา 

### วิธีการเรียกดูข้อมูลบัญชีด้วยเมธอด `accountInformationProvider` เพื่อทำ userAuthentication
รับพารามิเตอร์ 2 ตัวคือ 
1. หมายเลยบัญชี (string)
1. หมายเลข pin (string)

```php
<?php

include_once "./app/DBConnection.php";

try {
    $result = DBConnection::accountInformationProvider("0123444667", "4563");

    print_r($result);
    
} catch (AccountInformationException $e){
    
    echo $e->getMessage();

}
```

กรณีที่เรียกดูข้อมูลบัญชีสำเร็จจะได้ผลลัพธ์เป็น array ซ่ึงมีโครงสร้างข้อมูลดังนี้

```
Array
(
    [accNo] => 0123444667
    [accName] => Kritsada Kancha
    [accBalance] => 2000000
)
```

กรณีที่เรียกดูข้อมูลบัญชีไม่สำเร็จจะส่ง `AccountInformationException` ออกมา


## การคืนค่าเริ่มต้นของฐานข้อมูล
เพื่อให้ข้อมูลทดสอบของฐานข้อมูลกลาง กลับไปเป็นข้อมูลตั้งต้นที่ให้ สามารถเรียกใช้เมธอด `restore` ในคลาส `DBConnection` ได้

```php
<?php

include_once "./app/DBConnection.php";

DBConnection::restore();

```


