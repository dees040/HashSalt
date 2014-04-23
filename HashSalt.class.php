<?php
/**
 * HashSalt.class.php
 * HashSalt - A simple class that hashes passwords with salts.
 *
 * @author      Dees Oomens (dees040)
 * @git         https://github.com/dees040/HashSalt
 * @version     V0.1
 */

define("DB_SERVER", "your_database_server");
define("DB_NAME", "your_database_name");
define("DB_USER", "your_database_user");
define("DB_PASS", "your_database_user_pass");
define("TABLE_USERS", "your_user_table");

class HashSalt
{
    private $connection; // Holds Database connection

    /**
     * Class constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * init - Make database connection.
     */
    private function init() {
        /* Make connection to database */
        try {
            # MySQL with PDO_MYSQL
            $this->connection = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME, DB_USER, DB_PASS);
            $this->connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        } catch(PDOException $e) {
            echo "Error connecting to database.";
        }
    }

    /**
     * generateHashSalt - Creates hash of given password
     * plus salt.
     * Returns a array with the hashed password and the salt
     * used for the password.
     */
    public function generateHashSalt($password) {
        $passwordSalt = $this->generateSalt(8);
        $passwordAndSalt = $password.$passwordSalt;
        $hashedPassword  = hash('sha256', $passwordAndSalt);

        return array('password' => $hashedPassword, 'salt' => $passwordSalt);
    }

    /**
     * checkUserPass - Checks if given user and password match
     * with info from database.
     */
    public function checkUserPass($user, $password) {
        $items = array(':user' => $user);
        if (is_int($user)) {
            $userInfo = $this->query("SELECT password, usersalt FROM ".TABLE_USERS." WHERE id = :user", $items)->fetchObject();
        } else {
            $userInfo = $this->query("SELECT password, usersalt FROM ".TABLE_USERS." WHERE username = :user", $items)->fetchObject();
        }

        if ($userInfo == false) return false;

        $userPassword = hash('sha256', $password.$userInfo->usersalt);

        if ($userPassword == $userInfo->password) return true;

        return false;
    }

    /**
     * generateRandStr - Generates a string made up of randomized
     * letters (lower and upper case) and digits, the length
     * is a specified parameter.
     */
    public function generateSalt($length){
        $salt = "";
        for($i = 0; $i < $length; $i++) {
            $randnum = mt_rand(0,61);
            if ($randnum < 10) {
                $salt .= chr($randnum+48);
            } else if($randnum < 36) {
                $salt .= chr($randnum+55);
            } else {
                $salt .= chr($randnum+61);
            }
        }
        return $salt;
    }

    /**
     * query - Function to make queries in PDO.
     */
    private function query($query, $items = array()) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($items);
        } catch(PDOException $e) {
            echo $e;
            $stmt = false;
        }

        return $stmt;
    }
}