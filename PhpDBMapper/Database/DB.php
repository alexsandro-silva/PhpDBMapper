<?php

/*
 * Copyright 2019 alexsandro.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PhpDBMapper\Database;

/**
 * Classe responsável pelas operações de banco de dados
 *
 * @author alexsandro
 */
class DB {
    
    const DEFAULT = 'DEFAULT';
    
    private $statement;

    public function open($db_name, $dsn, $user, $password) {
        $connection = new \PDO($dsn, $user, $password);
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        ConnectionManager::addConnection($db_name, $connection);
    }
    
    public function close(string $dbName) {
        ConnectionManager::removeConnection($dbName);
    }

    public function executeQuery($sql, $bindings = array()) {
        $this->statement = null;
        $pdoInstance = ConnectionManager::getConnection(self::DEFAULT);
        if(count($bindings) > 0) {
            $this->statement = $pdoInstance->prepare($sql);
            $executed = $this->statement->execute($bindings);
            if(! $executed) {
                throw new \PDOException();
            }
        } else {
            $this->statement = $pdoInstance->query($sql);
            if($this->statement === false) {
                throw new \PDOException();
            }
        }
        
        return $this->statement;
    }
    
    public function execute($sql, array $bindings = array()) {
        return $this->executeQuery($sql, $bindings);
    }

}
