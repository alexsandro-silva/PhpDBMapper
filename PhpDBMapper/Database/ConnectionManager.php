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
 * Description of ConnectionManager
 *
 * @author alexsandro
 */
class ConnectionManager {
    
    private static $connections = array();
    
    public static function addConnection(string $dbName, \PDO $connection) {
        if(array_key_exists($dbName, self::$connections)) {
            throw new \RuntimeException(sprintf("A conexão %s já existe", $dbName));
        }
        
        self::$connections[$dbName] = $connection;
    }
    
    public static function removeConnection(string $dbName) {
        if(array_key_exists($dbName, self::$connections)) {
            unset(self::$connections[$dbName]);
        }
    }
    
    public static function getConnections() {
        return self::$connections;
    }

    public static function getConnection(string $dbName) {
        if(array_key_exists($dbName, self::$connections)) {
            return self::$connections[$dbName];
        } else {
            throw new \RuntimeException(sprintf("A conexão %s ainda não foi aberta.", $dbName));
        }
    }
}
