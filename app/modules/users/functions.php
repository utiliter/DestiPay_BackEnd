<?php
namespace App\Modules\Users;
function bcrypt($input)
{
    return sqlPassword($input);
}

function sqlPassword($input)
{
    $pass = strtoupper(
        sha1(
            sha1($input, true)
        )
    );
    $pass = '*' . $pass;
    return $pass;
}


function checkUser()
{
    global $response;
    if (!empty($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    } else {
        $response->status = 401;
        returnJson();
    }
}




function getUserTableName($userTypeId)
{
    return match ($userTypeId) {
        1 => 'users_queen',
        2 => 'users_partners',
        3 => 'users_visitors',
        5 => "users_system",
        default => 'users_visitors',
    };


}




function formatUserCreate()
{


}