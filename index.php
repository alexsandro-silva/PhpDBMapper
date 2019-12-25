<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        include_once './PhpDBMapper.php';
        
        use PhpDBMapper\Database\DB;
        
        class Hero extends \PhpDBMapper\BaseModel {
            
            static $tableName = 'hero';
            
        }
        
        DB::open('mysql:host=localhost;dbname=heroes', 'root', '');
        
        $heroes = Hero::find_all();
        
        
        ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>civilian_name</th>
                <th>hero_name</th>
            </tr>
            <?php
            for($i = 0; $i < sizeof($heroes); $i++) {
                $hero = $heroes[$i];
            ?>
            <tr>
                <td><?= $hero->id ?></td>
                <td><?= $hero->civilian_name ?></td>
                <td><?= $hero->hero_name ?></td>
            </tr>
            <?php
            }
            ?>
        </table>
    </body>
</html>
