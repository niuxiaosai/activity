<?php

class Logger
{

    public function __construct()
    {

    }

    public function __get($param = null)
    {
        return new self();
    }

    public function __set($k, $v)
    {
        return new self();
    }

    public function __call($func, $param)
    {
        return new self();
    }

    public function __callStatic($func, $param)
    {
        return new self();
    }

    public function __toString()
    {
        return 'Dumb Logger';
    }
}