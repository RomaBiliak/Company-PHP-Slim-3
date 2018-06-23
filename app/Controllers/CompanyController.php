<?php
	namespace App\Controllers;

	class CompanyController extends Controller{
		private static $i = 0;
		private $company = [];
		private $companyTree = [];
		public function index($request, $response){
			
			$sql = " SELECT *, company_id as id FROM company ";
			$query = $this->dbh->prepare($sql);
			
			try{
				$query->execute();
				$this->company = $query->fetchAll();
				if(!empty($this->company)){
					$this->createCompanyLevel();
				}
			
				return $response->withHeader('Content-type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*')->withJson($this->companyTree, 201);

			}catch(\PDOExeption $e){
				
			}
		}
		protected function createCompanyLevel(){
			foreach($this->company as $key=>$row){
				if($row['parent_id'] ==0 ){
					$this->companyTree[self::$i][$row['company_id']] = $row;
					unset($this->company[$key]);
				}
				if(self::$i>0){
					if(isset($this->companyTree[self::$i-1][$row['parent_id']])){
						$this->companyTree[self::$i][$row['company_id']] = $row;
						unset($this->company[$key]);
					}
				}
			}
			if(!empty($this->company)){
				self::$i++;
				$this->createCompanyLevel();
			}
		}
		protected function createCompanyTree(){
			foreach($this->companyTree[self::$i] as $company_id=>$company){ 
				$this->companyTree[self::$i-1][$company['parent_id']]['children'][$company_id] = $company;
			}
			self::$i--;
			if(self::$i>0) $this->createCompanyTree();
		}
		public function getCompanyById($request, $response, $args){
			$company_id = (int)$args['company_id'];	
			$sql = " SELECT * FROM company WHERE company_id=".$company_id;
			$query = $this->dbh->prepare($sql);
			$query->execute();
			$company = $query->fetchAll();
			return $response->withHeader('Content-type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*')->withJson($company[0], 201);
		}
		public function updateCompany($request, $response, $args){
			$company_id = (int)$args['company_id'];
			$data = json_decode($request->getParsedBody()['data'],1);
			$sql = " UPDATE  company SET name= :name, estimate = :estimate, parent_id = :parent_id WHERE company_id=".$company_id;
			$query = $this->dbh->prepare($sql);
			$query->execute([':name'=>$data['name'], ':estimate'=>(int)$data['estimate'], ':parent_id'=>(int)$data['parent_id']]);
		}
		public function addCompany($request, $response){
			$data = json_decode($request->getParsedBody()['data'],1);
			$sql = " INSERT INTO  company SET name= :name, estimate = :estimate, parent_id = :parent_id ";
			$query = $this->dbh->prepare($sql);
			$query->execute([':name'=>$data['name'], ':estimate'=>(int)$data['estimate'], ':parent_id'=>(int)$data['parent_id']]);
		}
		public function deleteCompany($request, $response, $args){
			$company_id = (int)$args['company_id'];
			$company_id = (int)$args['company_id'];
			$sql_id = $this->getChildrenId($company_id);
			$sql_id[] = $company_id;
			$not_in = implode(',', $sql_id);
			$sql = " DELETE FROM company WHERE company_id IN (".$not_in.")";
			$query = $this->dbh->prepare($sql);
			$query->execute();
		}
		public function companyData($request, $response, $args){
			$company_id = (int)$args['company_id'];
			$sql_id = $this->getChildrenId($company_id);
			$sql_id[] = $company_id;
			$not_in = implode(',', $sql_id);
			$sql = " SELECT company_id, name FROM company WHERE company_id NOT IN (".$not_in.")";
			$query = $this->dbh->prepare($sql);
			$query->execute();
			$data['company'] = $query->fetchAll();
			return $response->withHeader('Content-type', 'application/json')->withHeader('Access-Control-Allow-Origin', '*')->withJson($data, 201);
		}
		private function getChildrenId($company_id){
			$children_id = [];
			$sql = "SELECT company_id, parent_id FROM company";
			$query = $this->dbh->prepare($sql);
			$query->execute();
			$this->company = $query->fetchAll();
			if(!empty($this->company)){
				$this->createCompanyLevel();
				$this->createCompanyTree();
				foreach($this->companyTree as $level_id=>$level_data){
					foreach($level_data as $level_company_id=>$company_data){
						if($level_company_id == $company_id && !empty($company_data['children'])){
							$this->extractChildrenId($children_id, $company_data['children']);
							break(2);		
						}
					}
				}
			}
			return $children_id;

		}
		private function extractChildrenId(&$children_id, $children){
			foreach($children as $child){
				$children_id[] = $child['company_id'];
				if(!empty($child['children'])) $this->extractChildrenId($children_id, $child['children']);	
			}
		}
	}
	