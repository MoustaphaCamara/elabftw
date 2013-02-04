<?php
// elabftw update file. Run it after each git pull.
// php update.php on normal server
// /Applications/MAMP/bin/php/php5.3.6/bin/php update.php for MAMP install
//
// check if it's run from cli (cron) or webserver; do nothing if it's from webserver
if(php_sapi_name() != 'cli' || !empty($_SERVER['REMOTE_ADDR'])) {
    die("<p>Thank you for using eLabFTW. <br />To update your database, run this file only from the command line.</p>");
}
require_once('inc/connect.php');
// ADD elabid in experiments table
$sql = "SELECT * from experiments";
$req = $bdd->prepare($sql);
$req->execute();
$test = $req->fetch();
if(isset($test['elabid'])) {
    echo "Column 'elabid' already exists. Nothing to do.\n";
} else {
    echo "Creating field <strong>elabid</strong>...\n";
    $sql = "ALTER TABLE `experiments` ADD `elabid` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
    $req = $bdd->prepare($sql);
    $result = $req->execute();
    if($result) {
        echo 'Field <strong>elabid</strong> successfully added :) \n';
    } else {
        echo 'There was a problem in the database update :/ Please report a bug to nicolas.carpi@gmail.com';
        die();
    }
}

// ADD elabid for experiments without it
// get id of experiments with empty elabid
$sql = "SELECT id from experiments WHERE elabid LIKE ''";
$req = $bdd->prepare($sql);
$req->execute();
// array to store the id
$id_arr = array();
while ($get_id = $req->fetch()) {
    $id_arr[] = $get_id['id']." ";
}
foreach($id_arr as $id) {
    // get date
    $sql = "SELECT date from experiments WHERE id = :id";
    $req = $bdd->prepare($sql);
    $req->execute(array(
        'id' => $id
    ));
    $elabid_fill = $req->fetch();
    $date = $elabid_fill['date'];
    // Generate unique elabID
    $elabid = $date."-".sha1(uniqid($date, TRUE));
    // add elabid
    $sql = "UPDATE experiments SET elabid=:elabid WHERE id=:current_id";
    $req = $bdd->prepare($sql);
    $result = $req->execute(array(
        'elabid' => $elabid,
        'current_id' => $id
    ));
    if ($result) {
        echo "Experiment id ".$id." updated.\n";
    } else {
        echo 'There was a problem in the database update :/ Please report a bug to nicolas.carpi@gmail.com';
        die();
    }
}

// ADD locked in experiments table
if(isset($test['locked'])) {
    echo "Column 'locked' already exists. Nothing to do.\n";
} else {
    echo 'Creating field...';
    $sql = "ALTER TABLE `experiments` ADD `locked` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'";
    $req = $bdd->prepare($sql);
    $result = $req->execute();
    if($result) {
        echo 'Field <strong>locked</strong> successfully added :) \n';
    } else {
        echo 'There was a problem in the database update :/ Please report a bug to nicolas.carpi@gmail.com';
        die();
    }
}
// items_type :
$sql = "SHOW TABLES";
$req = $bdd->prepare($sql);
$req->execute();
$test = $req->fetch();
$test_arr = array();
while ($row = $req->fetch()) {
        $test_arr[] = $row[0];
}

if(in_array('items_types',$test_arr)) {
      echo "Table 'items_types' already exists. Nothing to do.\n";
} else {


    $create_sql = "CREATE TABLE `items_types` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
        `name` TEXT NOT NULL ,
        `bgcolor` VARCHAR( 6 ) DEFAULT '000000',
        `template` TEXT NULL,
        `tags` TEXT NULL,
        PRIMARY KEY ( `id` )
    ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;";
    $req = $bdd->prepare($create_sql);
    $result = $req->execute();
    if($result) {
        echo 'Table items_types successfully created.\n';
    } else {
        echo 'There was a problem in the database update :/';
        die();
    }

    // Transform all ant => 1, pla => 2, pro => 3
    // get id of items type ant
    $sql = "SELECT id from items WHERE type LIKE 'ant'";
    $req = $bdd->prepare($sql);
    $req->execute();
    // array to store the id
    $id_arr = array();
    while ($get_id = $req->fetch()) {
        $id_arr[] = $get_id['id']." ";
    }
    foreach($id_arr as $id) {
        // change value
        $sql = "UPDATE items SET type=:type WHERE id=:current_id";
        $req = $bdd->prepare($sql);
        $result = $req->execute(array(
            'type' => '1',
            'current_id' => $id
        ));
        if ($result) {
            echo "Item id ".$id." updated.\n";
        } else {
            echo 'There was a problem in the database update :/ Please report a bug to nicolas.carpi@gmail.com';
            die();
        }
    }
    // get id of items type pla
    $sql = "SELECT id from items WHERE type LIKE 'pla'";
    $req = $bdd->prepare($sql);
    $req->execute();
    // array to store the id
    $id_arr = array();
    while ($get_id = $req->fetch()) {
        $id_arr[] = $get_id['id']." ";
    }
    foreach($id_arr as $id) {
        // change value
        $sql = "UPDATE items SET type=:type WHERE id=:current_id";
        $req = $bdd->prepare($sql);
        $result = $req->execute(array(
            'type' => '2',
            'current_id' => $id
        ));
        if ($result) {
            echo "Item id ".$id." updated.\n";
        } else {
            echo 'There was a problem in the database update :/ Please report a bug to nicolas.carpi@gmail.com';
            die();
        }
    }
    // get id of items type pro
    $sql = "SELECT id from items WHERE type LIKE 'pro'";
    $req = $bdd->prepare($sql);
    $req->execute();
    // array to store the id
    $id_arr = array();
    while ($get_id = $req->fetch()) {
        $id_arr[] = $get_id['id']." ";
    }
    foreach($id_arr as $id) {
        // change value
        $sql = "UPDATE items SET type=:type WHERE id=:current_id";
        $req = $bdd->prepare($sql);
        $result = $req->execute(array(
            'type' => '3',
            'current_id' => $id
        ));
        if ($result) {
            echo "Item id ".$id." updated.\n";
        } else {
            echo 'There was a problem in the database update :/ Please report a bug to nicolas.carpi@gmail.com';
            die();
        }
    }
    $sql = "";

    // Change type of type (string => int) in items table and fill table items_types
    $sql = "ALTER TABLE `items` CHANGE `type` `type` INT UNSIGNED NOT NULL;INSERT INTO `items_types` (`id`, `name`, `bgcolor`, `template`, `tags`) VALUES (NULL, 'Antibody', '2cff00', NULL, NULL);INSERT INTO `items_types` (`id`, `name`, `bgcolor`, `template`, `tags`) VALUES (NULL, 'Plasmid', '004bff', NULL, NULL);INSERT INTO `items_types` (`id`, `name`, `bgcolor`, `template`, `tags`) VALUES (NULL, 'Protocol', 'ff0000', NULL, NULL);";
    $req = $bdd->prepare($sql);
    $result = $req->execute();
    if($result) {
        echo 'Database successfully updated with default values.\n';
    } else {
        echo 'There was a problem in the database update :/';
    }


}

// change outcome in status
// check if it exists first
$sql = "SELECT * from experiments";
$req = $bdd->prepare($sql);
$req->execute();
$test = $req->fetch();
if(isset($test['status'])) {
    echo "Column 'status' already exists. Nothing to do.\n";
} else {
    $sql = "ALTER TABLE `experiments` CHANGE `outcome` `status` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
    $req = $bdd->prepare($sql);
    $result = $req->execute();
    if($result) {
        echo 'Outcome is now status.\n';
    } else {
        echo 'There was a problem in the database update :/';
    }
}

// remove unused items_templates table
$sql = "DROP TABLE IF EXISTS `items_templates`";
$req = $bdd->prepare($sql);
$result = $req->execute();
if($result) {
    echo 'Removed items_templates table.\n';
} else {
    echo 'There was a problem in the database update :/';
}
?>
