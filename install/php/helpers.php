<?php

function exit_error($msg = '')
{
    exit(json_encode([
        'success' => false,
        'msg' => $msg,
    ]));
}

function exit_success($msg = '')
{
    exit(json_encode([
        'success' => true,
        'msg' => $msg,
    ]));
}
