<?php

namespace Standord\MuseCore;

/**
 * Manage Get, Post, Cookies, Files, Server Data, Session Data and Env data.  
 */
class DataHandling
{
    public $get;
    public $post;
    public $cookie;
    public $request;
    public $files;
    public $server;
    public $session;
    public $env;

    public function __construct()
    {
        global $envPath;
        
        $this->get = (object) $_GET;
        $this->post = (object) $_POST;
        $this->cookie = (object) $_COOKIE;
        $this->request = (object) $_REQUEST;
        $this->files = (object) $_FILES;

        if (isset($_SESSION)) {
            $this->session = (object) $_SESSION;
        } else {
            $this->session = null;
        }

        # Looing for .env at the root directory
        $env = parse_ini_file( $envPath);
        $this->env = (object) $env;
    }
}