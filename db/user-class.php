<?php

class User {
	private $db;
    private $table = "user";

    // Properties
    public $id;
    public $login;
    public $password;
    public $status;
    public $token;

    /**
     * Constructor with $db
     *
     * @param $db
     */
    public function __construct($db){
        $this->db = $db;
    }
    /**
     * Log in
     * @return boolean
     */
    public function login(): bool{
        $req = $this->db->prepare('SELECT statut, token FROM '.$this->table .' WHERE login = :login AND password = :password');

        $req->bindValue(':login', $this->login);
        $req->bindValue(':password', hash("sha512", $this->password));

        $res = $req->execute();
        if ($result = $res->fetchArray(SQLITE3_ASSOC)){
            $this->status = $result["statut"];
            $this->token = $result["token"];
            return true;
        }
        return false;
    }

    /**
     * User status
     */
    public function getStatus(){
        $req = $this->db->prepare('SELECT statut FROM '.$this->table .' WHERE token = :token');
        $req->bindValue(':token', $this->token);
        $res = $req->execute();

        return ($result = $res->fetchArray(SQLITE3_ASSOC)) ? $result["statut"] : null;
    }    
}

?>