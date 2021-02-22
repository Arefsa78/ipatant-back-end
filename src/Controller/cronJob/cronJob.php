<?php

function updateBlackList(){
    $now=time();
    $sql="DELETE FROM `black_list` WHERE `expires_at`<'$now'";
    $db=new authDB();
    $db->getConnection()->query($sql);
}

function updateUserList(){
    $db=new databaseController();
    $sql="DELETE FROM `user` WHERE `status`='0'";
    $db->getConnection()->query($sql);
}



updateBlackList();
updateUserList();




