<?php
/**
 * Utility library regarding everything user accounts and authentication.
 * @package UserAccount
 * @author Roberto Oliveros
 */

/**
 * Includes the Persist package.
 */
require_once('business/persist.php');
/**
 * Includes the UserAccounDAM package for accessing the database.
 */
require_once('data/user_account_dam.php');

/**
 * Represents a role a user might play in the system.
 * @package UserAccount
 * @author Roberto Oliveros
 */
class Role{
	/**
	 * Holds the role's identifier.
	 *
	 * @var integer
	 */
	private $_mId;
	
	/**
	 * Holds the role's name.
	 *
	 * @var string
	 */
	private $_mName;
	
	/**
	 * Constructs the role with the provided id and name.
	 *
	 * Must be called only from the database layer. Used getInstance() instead with a valid id.
	 * @param integer $id
	 * @param string $name
	 * @throws Exception
	 */
	public function __construct($id, $name){
		try{
			Number::validatePositiveInteger($id, 'Id inv&aacute;lido.');
			String::validateString($name, 'Nombre inv&aacute;lido.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo construct en Role con datos erroneos! ' .
					$e->getMessage());
			throw $et;
		}
		
		$this->_mId = $id;
		$this->_mName = $name;
	}
	
	/**
	 * Returns the role's id.
	 *
	 * @return integer
	 */
	public function getId(){
		return $this->_mId;
	}
	
	/**
	 * Retuns the role's name.
	 *
	 * @return string
	 */
	public function getName(){
		return $this->_mName;
	}
	
	/**
	 * Returns an instance of a role.
	 *
	 * Returns NULL if there was no match for the provided id in the database.
	 * @param integer $id
	 * @return Role
	 */
	static public function getInstance($id){
		Number::validatePositiveInteger($id, 'Id inv&aacute;lido.');
		return RoleDAM::getInstance($id);
	}
}


/**
 * Represents a user account for accesing the system.
 * @package UserAccount
 * @author Roberto Oliveros
 */
class UserAccount extends PersistObject{
	/**
	 * Holds the account's username.
	 *
	 * @var string
	 */
	private $_mUserName;
	
	/**
	 * Holds the user's first name.
	 *
	 * @var string
	 */
	private $_mFirstName;
	
	/**
	 * Holds the user's last name.
	 *
	 * @var string
	 */
	private $_mLastName;
	
	/**
	 * Holds the account's encrypted password.
	 *
	 * @var string
	 */
	private $_mPassword;
	
	/**
	 * Holds the account's role.
	 *
	 * @var Role
	 */
	private $_mRole;
	
	/**
	 * Holds the account's deactivate flag.
	 *
	 * Holds true if the account is deactivated, otherwise is false.
	 * @var boolean
	 */
	private $_mDeactivated = false;
	
	/**
	 * Constructs the account with the provided username and status.
	 *
	 * Parameters must be set only if called from the database layer.
	 * @param string $userName
	 * @param integer $status
	 * @throws Exception
	 */
	public function __construct($userName = NULL, $status = Persist::IN_PROGRESS){
		parent::__construct($status);
		
		if(!is_null($userName))
			try{
				String::validateString($userName, 'Usuario inv&aacute;lido.');
			} catch(Exception $e){
				$et = new Exception('Interno: Llamando al metodo construct en UserAccount con datos erroneos! ' .
						$e->getMessage());
				throw $et;
			}
		
		$this->_mUserName = $userName;
	}
	
	/**
	 * Returns the account's username.
	 *
	 * @return string
	 */
	public function getUserName(){
		return $this->_mUserName;
	}
	
	/**
	 * Retuns the user's first name.
	 *
	 * @return string
	 */
	public function getFirstName(){
		return $this->_mFirstName;
	}
	
	/**
	 * Retuns the user's last name.
	 *
	 * @return string
	 */
	public function getLastName(){
		return $this->_mLastName;
	}
	
	/**
	 * Retuns the account's encrypted password.
	 *
	 * @return string
	 */
	public function getPassword(){
		return $this->_mPassword;
	}
	
	/**
	 * Returns the account's role.
	 *
	 * @return Role
	 */
	public function getRole(){
		return $this->_mRole;
	}
	
	/**
	 * Retuns value of the account's deactivated flag.
	 *
	 * @return boolean
	 */
	public function isDeactivated(){
		return $this->_mDeactivated;
	}
	
	/**
	 * Sets the account's username.
	 *
	 * @param string $userName
	 * @throws Exception
	 */
	public function setUserName($userName){
		if($this->_mStatus == self::CREATED)
			throw new Exception('No se puede editar el nombre de la cuenta.');
		
		String::validateString($userName, 'Usuario inv&aacute;lido.');
		$this->verifyUserName($userName);
		$this->_mUserName = $userName;
	}
	
	/**
	 * Sets the user's first name.
	 *
	 * @param string $firstName
	 */
	public function setFirstName($firstName){
		String::validateString($firstName, 'Nombre inv&aacute;lido.');
		$this->_mFirstName = $firstName;
	}
	
	/**
	 * Sets the user last name.
	 *
	 * @param string $lastName
	 */
	public function setLastName($lastName){
		String::validateString($lastName, 'Nombre inv&aacute;lido.');
		$this->_mLastName = $lastName;
	}
	
	/**
	 * Sets the account's password.
	 *
	 * It encrypts the password before setting it.
	 * @param string $password
	 */
	public function setPassword($password){
		String::validateString($password, 'Contrase&ntilde;a inv&aacute;lida.');
		$this->_mPassword = UserAccountUtility::encrypt($password);
	}
	
	/**
	 * Sets the account's role.
	 *
	 * @param Role $obj
	 */
	public function setRole(Role $obj){
		$this->_mRole = $obj;
	}
	
	/**
	 * Sets the account's deactivation flag value.
	 *
	 * @param boolean $bool
	 */
	public function deactivate($bool){
		$this->_mDeactivated = (boolean)$bool;
	}
	
	/**
	 * Sets the account's properties.
	 *
	 * Must be called only from the database layer corresponding class. The object's status must be set to
	 * Persist::CREATED in the constructor method too.
	 * @param string $firstName
	 * @param string $lastName
	 * @param Role $role
	 * @param boolean $deactivated
	 * @throws Exception
	 */
	public function setData($firstName, $lastName, $role, $deactivated){
		try{
			String::validateString($firstName, 'Nombre inv&aacute;lido.');
			String::validateString($lastName, 'Apellido inv&aacute;lido.');
		} catch(Exception $e){
			$et = new Exception('Interno: Llamando al metodo setData en UserAccount con datos erroneos! '.
					$e->getMessage());
			throw $et;
		}
		
		$this->_mFirstName = $firstName;
		$this->_mLastName = $lastName;
		$this->_mRole = $role;
		$this->_mDeactivated = (boolean)$deactivated;
	}
	
	/**
	 * Saves account's data to the database.
	 * 
	 * If the object's status is set to Persist::IN_PROGRESS the method insert()
	 * is called, if it's set to Persist::CREATED the method update() is called.
	 * @return void
	 * @throws Exception
	 */
	public function save(){
		if(UserAccountUtility::isRoot($this->_mUserName))
			throw new Exception('Cuenta reservada para el superusuario.');
		
		$this->validateMainProperties();
		
		if($this->_mStatus == self::IN_PROGRESS){
			$this->verifyUserName($this->_mUserName);
			$this->insert();
			$this->_mStatus = self::CREATED;
		}
		else
			$this->update();
	}
	
	/**
	 * Returns an instance of a user account.
	 *
	 * Returns NULL if there was no match in the database for the providad username.
	 * @param string $userName
	 * @return UserAccount
	 */
	static public function getInstance($userName){
		String::validateString($userName, 'Usuario inv&aacute;lido.');
		
		if(UserAccountUtility::isRoot($userName))
			return new UserAccount(UserAccountUtility::ROOT, Persist::CREATED);
		else
			return UserAccountDAM::getInstance($userName);
	}
	
	/**
	 * Deletes the account from the database.
	 *
	 * Throws an exception due dependencies.
	 * @param UserAccount $obj
	 * @throws Exception
	 */
	static public function delete(UserAccount $obj){
		self::validateObjectFromDatabase($obj);			
		if(!UserAccountDAM::delete($obj))
			throw new Exception('Cuenta de Usuario tiene dependencias y no se puede eliminar.');
	}
	
	/**
	 * Validates account's main properties.
	 * 
	 * Verifies that the account's username, fisrt name, last name and password are not emty. The role
	 * property must not be NULL. Otherwise it throws an exception.
	 * @throws Exception
	 */
	protected function validateMainProperties(){
		String::validateString($this->_mUserName, 'Usuario inv&aacute;lido.');
		String::validateString($this->_mFirstName, 'Nombre inv&aacute;lido.');
		String::validateString($this->_mLastName, 'Apellido inv&aacute;lido.');
		String::validateString($this->_mPassword, 'Contrase&ntilde;a inv&aacute;lida.');
		
		if(is_null($this->_mRole))
			throw new Exception('Rol inv&accute;lido.');
	}
	
	/**
	 * Inserts the account's data in the database.
	 *
	 */
	protected function insert(){
		UserAccountDAM::insert($this);
	}
	
	/**
	 * Updates the account's data in the database.
	 *
	 */
	protected function update(){
		UserAccountDAM::update($this);
	}
	
	/**
	 * Verifies if the account's username already exists in the database.
	 * 
	 * Throws an exception if it does.
	 * @param string $userName
	 * @throws Exception
	 */
	private function verifyUserName($userName){
		if(UserAccountDAM::exists($userName))
			throw new Exception('Nombre de cuenta ya existe.');
	}
}


/**
 * Defines necessary routines regarding user accounts.
 * @package UserAccount
 * @author Roberto Oliveros
 */
class UserAccountUtility{
	/**
	 * Username of the superuser account.
	 *
	 */
	const ROOT = 'ROOT';
	
	/**
	 * Prefix for the hashing functionality for passwords.
	 *
	 */
	const HASH_PREFIX = 'bO2';
	
	/**
	 * Verifies the user account exists in the database.
	 *
	 * Returns true if the user account exists and uses the provided password. Otherwise returns false.
	 * @param string $userName
	 * @param string $password
	 * @return boolean
	 */
	static public function isValid($userName, $password){
		String::validateString($userName, 'Usuario inv&aacute;lido.');
		String::validateString($password, 'Contrase&ntilde;a inv&aacute;lida.');
		
		if(self::isRoot($userName))
			return UserAccountUtilityDAM::isValidRoot(self::encrypt($password));
		else
			return UserAccountUtilityDAM::isValid($userName, self::encrypt($password));
	}
	
	/**
	 * Returns true if it is the username of the supersuser account, otherwise false.
	 *
	 * @param string $userName
	 * @return boolean
	 */
	static public function isRoot($userName){
		if(strtoupper($userName) == 'ROOT')
			return true;
		else
			return false;
	}
	
	/**
	 * Changes the user account's password.
	 *
	 * Returns true on success.
	 * @param UserAccount $account
	 * @param string $password
	 * @param string $newPassword
	 * @return boolean
	 * @throws Exception
	 */
	static public function changePassword(UserAccount $account, $password, $newPassword){
		Persist::validateObjectFromDatabase($account);
		String::validateString($password, 'Contrase&ntilde;a actual inv&aacute;lida.');
		String::validateString($newPassword, 'Nueva contrase&ntilde;a inv&aacute;lida.');
		
		$account_name = $account->getUserName();
		if(!self::isValid($account_name, $password))
			throw new Exception('Contrase&ntilde;a inv&aacute;lida.');
		
		if(self::isRoot($account_name))
			return UserAccountUtilityDAM::changeRootPassword(self::encrypt($newPassword));
		else
			return UserAccountUtilityDAM::changePassword($account, self::encrypt($newPassword));
	}
	
	/**
	 * Encrypts the provided password.
	 *
	 * @param string $password
	 * @return string
	 */
	static public function encrypt($password){
		return sha1(HASH_PREFIX . $password);
	}
}
?>