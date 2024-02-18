<?php

namespace App\Models;

use App\DAO\UserDAO;

use App\Exceptions\InvalidParamException;
use App\Exceptions\SqlQueryException;

class UserModel {
    private string $name;
    private string $email;
    private string $password;

    private $DAO;

    public function __construct() {
        $this->DAO = new UserDAO();
    }

    public function setName(string $name) {
        Security::validateName($name);

        $this->name = $name;
    }

    public function setEmail(string $email) {
        Security::validateEmail($email);

        $this->email = $email;
    }

    public function setPassword(string $password) {
        Security::validatePass($password);

        $this->password = $password;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function insert(self $user) {
        $dao = $this->DAO;

        if (!$dao->executeInsert($user)) {
            throw new SqlQueryException('Não foi possível realizar o cadastro, por favor, tente novamente mais tarde');
        }
    }

    private static function setSession($userID) {
        $_SESSION['user_id'] = $userID;
    }

    public function personAuth(string $user, string $pass512) {
        $dao = $this->DAO;
        $user = $dao->getAuthUser($user, $pass512);

        if (!$user) {
            throw new SqlQueryException('Usuário não encontrado.');
        }
        if ($user['status'] == 0) {
            throw new SqlQueryException('Conta do usuário inativa, entre em contato com o suporte para mais informações.');
        }

        self::setSession($user['id']);

        return true;
    }

    public function getPerfil(int $userID) {
        $dao = $this->DAO;

        $userInfo = $dao->getUserPerfil($userID);
        if (!$userInfo) {
            throw new SqlQueryException('Não foi possível obter os dados, por favor, tente novamente mais tarde');
        }

        return $userInfo;
    }

    public function updatePerfil(int $userID, array $fields) {
        $dao = $this->DAO;

        $updated = $dao->updatePerfil($userID, $fields);
        if (empty($updated)) {
            throw new SqlQueryException('Não foi possível alterar o perfil, por favor, tente novamente mais tarde');
        }
    }

    public function makeArrayUpdatePerfil(array $perfil) {
        $fieldsToUpdate = [];

        if (!empty($perfil['user_name'])) {
            $fieldsToUpdate['name'] = Security::sanitizeString($perfil['user_name']);
        }
        if (!empty($perfil['user_email'])) {
            $fieldsToUpdate['email'] = Security::sanitizeEmail($perfil['user_email']);
        }
        if (!empty($perfil['user_password'])) {
            Security::validatePass($perfil['user_password']);
            $fieldsToUpdate['pass'] = Security::convertToSha512($perfil['user_password']);
        }

        return $fieldsToUpdate;
    }
}
