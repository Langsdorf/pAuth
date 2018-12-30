<?php

class AuthClass {
	
	private $mysql_db;
	private $mysql_host;
	private $mysql_user;
	private $mysql_pass;
	private $con;
	
	private $s = "9wEm3a2XmMn48r3W";
	
	public function __construct($mysql_db, $mysql_host, $mysql_user, $mysql_pass){
		$this->mysql_db = $mysql_db;
		$this->mysql_host = $mysql_host;
		$this->mysql_user = $mysql_user;
		$this->mysql_pass   = $mysql_pass;
		$this->con = mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
	}
	
	public function __destruct(){
		if(is_object($this->con)){
			$this->con->close();
		}
	}
	
	public function register($name, $email, $pass, $admin, $cpf, $number) {
		if (is_null($name) || is_null($pass)) return false;
		
		$name = mysqli_real_escape_string($this->con, $name);
		$email = mysqli_real_escape_string($this->con, $email);
		$pass = mysqli_real_escape_string($this->con, $pass);
		$admin = mysqli_real_escape_string($this->con, $admin);
		$cpf = mysqli_real_escape_string($this->con, $cpf);
		$number = mysqli_real_escape_string($this->con, $number);
		
		$sql_line = "SELECT * FROM users WHERE user = '$name' or email = '$email' or cpf = '$cpf'";
		$q = mysqli_query($this->con, $sql_line);
		if(mysqli_num_rows($q) > 0) return false;
		
		$hash = sha1(md5($pass . $this->s . $cpf));
		$token = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10) . md5(time());
		
		$q2 = mysqli_query($this->con, "SELECT token FROM users WHERE token = '$token'");
		if(mysqli_num_rows($q2) > 0) return false;
		
		$sql_line2 = "INSERT INTO users(user, email, password, admin, cpf, number, token)
						VALUES ('$name', '$email', '$hash', '$admin', '$cpf', '$number', '$token')";
		if ($insert_data = mysqli_query($this->con, $sql_line2)) {
			return true;
		}
		
		return false;
	}
	
	public function login($cpf, $pass) {
		@session_start();
		
		$cpf = mysqli_real_escape_string($this->con, $cpf);
		$pass = mysqli_real_escape_string($this->con, $pass);
		$hash = sha1(md5($pass . $this->s . $cpf));		

		
		$sql_line = "SELECT * FROM users WHERE cpf = '$cpf' and password = '$hash'";
		$q = mysqli_query($this->con, $sql_line);
		if(mysqli_num_rows($q) > 0){
			$cb = base64_encode($cpf);
			setcookie("login", $cb, time()+3600*24);
			return true;
		}
		return false;
	}
	
	public function logout() {
		@session_start();
		
		if (isLogged()) {
			setcookie('login', null, time() - 0);
            return true;
		}
		
		return false;
	}
	
	public function isLogged() {
		@session_start();
		
		if(isset($_COOKIE['login'])){
            return true;
		}
		
		return false;
	}
	
	public function isAdmin($cpf) {
		if(isset($cpf)) {
			$cpf = mysqli_real_escape_string($this->con, $cpf);
			$sql_line = "SELECT * FROM users WHERE cpf = '$cpf' and admin = '1'";
			$q = mysqli_query($this->con, $sql_line);
			if(mysqli_num_rows($q) > 0) return true;
		}
		return false;
	}
	
	public function getInfo($cpf) {
		if(isset($cpf)) {
			$cpf = mysqli_real_escape_string($this->con, $cpf);
			$sql_line = "SELECT id, user, email, admin, number FROM users WHERE cpf = '$cpf'";
			$q = mysqli_query($this->con, $sql_line);
			if(mysqli_num_rows($q) > 0) {
				$row = mysqli_fetch_assoc($q);
				return $row;
			}
		}
		return null;
	}
}
?>