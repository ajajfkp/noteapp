<?php
 	require_once("Rest.inc.php");
	
	class API extends REST {
	
		public $data = "";
		
		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			require_once 'dbConnect.php';
			// opening db connection
			$db = new dbConnect();
			$this->conn = $db->connect();
		}

		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404); // If the method not exist with in this class "Page not found".
		}
				
		private function login(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			$authData = json_decode(file_get_contents("php://input"),true);
			$username = $authData['userLogin']['username'];		
			$passwd = $authData['userLogin']['password'];	

			if(!empty($username) and !empty($passwd)){
				$query="SELECT id, username, passwd FROM users WHERE username = '$username' AND passwd = '".md5($passwd)."' LIMIT 1";
				$r = $this->conn->query($query) or die($this->conn->error.__LINE__);

				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();					
					// If success everythig is good send header as "OK" and user details
					$result['status'] = "success";
					$this->response($this->json($result), 200);
				}
				$error = array('status' => "Failed", "msg" => "Invalid User name or Password");
				$this->response($this->json($error), 204);die;	// If no records "No Content" status
			}
			
			$error = array('status' => "Failed", "msg" => "Invalid User name or Password");
			$this->response($this->json($error), 400);
		}
		
		
		
		private function getnotes(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			
			$parmsData = $this->_request;
			$uid = $parmsData['userId'];
			$limit = $parmsData['limit'];
			$offset = $parmsData['offset'];
			
			$query="SELECT un.id, un.title, un.notes, un.priority FROM usersnote un WHERE delete_flag='0' and un.added_by=" .$uid. "  order by un.id desc LIMIT " .$offset. "," .$limit;
			$r = $this->conn->query($query) or die($this->conn->error.__LINE__);
			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status */
		}
		
		
		
		private function getnote(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$parmsData = $this->_request;
			$nid = $parmsData['noteid'];

			if($nid > 0){	
				$query="SELECT un.id, un.title, un.notes, un.priority FROM usersnote un WHERE un.id=" .$nid;
				$r = $this->conn->query($query) or die($this->conn->error.__LINE__);
				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();	
					$this->response($this->json($result), 200); // send user details
				}
			}
			$this->response('',204);	// If no records "No Content" status
		}
		
		 private function insertNote(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			
			$parmsData = $this->_request;
			$title = $parmsData['title'];
			$note = $parmsData['notes'];
			$prty = $parmsData['priority'];
			$uid = (int)$parmsData['uid'];
			
			$query = "INSERT INTO usersnote (title,notes,priority,added_by) VALUES('".$title."','".$note."','".$prty."','".$uid."')";
			
			if(!empty($parmsData)){
				$r = $this->conn->query($query) or die($this->conn->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Note Created Successfully.", "data" => $parmsData);
				$this->response($this->json($success),200);
			}else{
				$this->response('',204);	//"No Content" status
			}
		}
		
		private function updateNote(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			
			$parmsData = $this->_request;
			$id = (int)$parmsData['id'];
			$title = $parmsData['title'];
			$note = $parmsData['notes'];
			$prty = $parmsData['priority'];

			$query = "UPDATE usersnote SET title='" .trim($title). "', notes='" .trim($note). "', priority='" .trim($prty). "' WHERE id=$id";
			
			if(!empty($parmsData)){
				$r = $this->conn->query($query) or die($this->conn->error.__LINE__);
				$success = array('status' => "success", "msg" => "Note ".$id." Updated Successfully.", "data" => $parmsData);
				$this->response($this->json($success),200);
			}else{
				$this->response('',204);	// "No Content" status */
			}
		}
		
		private function deleteNote(){
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$parmsData = $this->_request;
			$id = $parmsData['id'];
			$count = count(explode(',',$id));
			if(!empty($id)){				
				$query = "UPDATE usersnote SET delete_flag='1' WHERE id IN ($id)";
				$r = $this->conn->query($query) or die($this->conn->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Successfully deleted ".$count." record.");
				$this->response($this->json($success),200);
			}else{
				$this->response('',204);	// If no records "No Content" status
			}
		}
		
		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();
?>