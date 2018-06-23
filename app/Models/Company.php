<?php
	namespace App\Models;

	class Company{
		public function getCompany(){
			$sql = " SELECT * FROM company ";
			$company = $this->dbh->prepare($sql);
			var_dump($company);
		}

	}