<?php

include_once 'ServiceType.php';
include_once 'AccountInformationException.php';
include_once 'BillingException.php';

class DBConnection {

    private static $dsn = 'mysql:dbname=u334971496_cutqa;host=sql128.main-hosting.eu';
    private static $user = 'u334971496_cutqa';
    private static $pass = 'cutqa1234';
    private static $con = null;
    
    public static function getInstance() {

        if(DBConnection::$con === null) {
            DBConnection::$con = new PDO(
                DBConnection::$dsn,
                DBConnection::$user,
                DBConnection::$pass
            );

            return DBConnection::$con;
        }

        return DBConnection::$con;
    }

    public static function accountInformationProvider(): array {
        $argument = func_get_args();

        if (count($argument) == 1) {
            return DBConnection::serviceAuthentication($argument[0]);
        }
        elseif(count($argument) == 2) {
            return DBConnection::userAuthentication(
                $argument[0],
                $argument[1]
            );
        }
    }

    public static function saveTransaction(string $accNo, int $updatedBalance): bool {
        $con = DBConnection::getInstance();
        $stmt = $con->prepare(
            "UPDATE ACCOUNT SET balance = :updatedBalance WHERE no = :accNo"
        );

        $result = $stmt->execute([
            ':accNo'            => $accNo, 
            ':updatedBalance'   => $updatedBalance
            ]);
        
        return !empty($result);
    }

    public static function getCharge(string $accNo, int $type): int {
        $con = DBConnection::getInstance();
        $stmt = null;

        if (ServiceType::ELECTRIC_BILLING == $type) {
            $stmt = $con->prepare(
                "SELECT electricCharge as charge FROM ACCOUNT WHERE no = :accNo"
            );

        }
        elseif (ServiceType::WATER_BILLING == $type) {
            $stmt = $con->prepare(
                "SELECT waterCharge as charge FROM ACCOUNT WHERE no = :accNo"
            );

        }
        elseif (ServiceType::PHONE_BILLING == $type) {
            $stmt = $con->prepare(
                "SELECT phoneCharge as charge FROM ACCOUNT WHERE no = :accNo"
            );
        }

        if(null == $stmt) {
            throw new BillingException("Unknow billing type.");
        }

        $stmt->execute([':accNo' => $accNo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
        if(!$result) {
            throw new AccountInformationException("Account number : {$accNo} not found.");
        }

        return intval($result['charge']);
    }

    public static function restore(): void {
        $con = DBConnection::getInstance();
        
        $stmt = $con->prepare("TRUNCATE ACCOUNT");
        $stmt->execute();

        $stmt = $con->prepare(
            "INSERT INTO `ACCOUNT` (`no`, `pin`, `name`, `balance`, `waterCharge`, `electricCharge`, `phoneCharge`) VALUES
            ('0123444667', '4563', 'Kritsada Kancha', 2000000, 10000, 53000, 400000),
            ('1924356780', '9541', 'Johny Walker', 890200, 10000, 700000, 100000),
            ('2476492431', '4212', 'Jack Sparrow', 1000000, 100000, 50000, 50000),
            ('4235750021', '6783', 'Tony Stark', 4000000, 410000, 3500000, 200000),
            ('5902150431', '4134', 'Bruce Wayne', 9555000, 5004000, 0, 1150250),
            ('7840125312', '9783', 'SpearMan PicSun', 50001, 10000, 10000, 0),
            ('9835602413', '6391', 'Martin Flower', 400000, 0, 300000, 240000),
            ('9178906629', '7287', 'Clara Oswald', 5000, 10000, 5010, 5001),
            ('3430368497', '8053', 'River Song', 10000, -1, -30, -1000)"
        );
        $stmt->execute();
    }

    private static function serviceAuthentication(string $accNo): array {
        $con = DBConnection::getInstance();
        $stmt = $con->prepare(
            "SELECT no as accNo, "
            . "name as accName, "
            . "balance as accBalance "
            . "FROM ACCOUNT "
            . "WHERE no = :accNo"
        );
        $stmt->execute([':accNo' => $accNo]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new AccountInformationException("Account number : {$accNo} not found.");
        }
        return $result;
    }

    private static function userAuthentication(string $accNo, string $pin): array {
        $con = DBConnection::getInstance();
        
        $stmt = $con->prepare(
            "SELECT no as accNo, "
            . "name as accName, "
            . "balance as accBalance "
            . "FROM ACCOUNT "
            . "WHERE no = :accNo AND pin = :pin"
        );
        $stmt->execute([':accNo' => $accNo, ':pin' => $pin]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new AccountInformationException("Account number or PIN is invalid.");
        }
        
        return $result;
    }

}
