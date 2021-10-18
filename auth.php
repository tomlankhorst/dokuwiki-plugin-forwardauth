<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class auth_plugin_forwardauth extends DokuWiki_Auth_Plugin
{
    protected $header_name;
    protected $default_groups;
    protected $admin_allowlist;
    protected $missing_header_error;
    protected $logout_url;

    public function __construct()
    {
        parent::__construct();

        $this->loadConfig();

        $this->header_name = (string) $this->conf['header_name'];
        $this->default_groups = $this->csvToArray($this->conf['default_groups']);
        $this->admin_allowlist = $this->csvToArray($this->conf['admin_allowlist']);
        $this->missing_header_error = (bool) $this->conf['missing_header_error'];
        $this->logout_url = (string) $this->conf['logout_url'];

        $this->success = true;

        $this->cando['external'] = true;
        $this->cando['logout'] = true;
    }

    public function trustExternal($user, $pass, $sticky = false)
    {
        $header = $this->phpHeader($this->header_name);

        $forwardauth = $_SERVER[$header] ?? null;

        if ($forwardauth) {
            $this->authUser($forwardauth);
            return true;
        } elseif ($this->missing_header_error) {
            die('Missing authentication header, this is an unexpected fatal error.');
        }

        return false;
    }

    protected function authUser($user)
    {
        global $USERINFO;

        $USERINFO = array_merge($USERINFO ?? [], $this->getUserData($user));
        $_SERVER['REMOTE_USER'] = $user;
    }

    protected function phpHeader($header)
    {
        $header = strtoupper($header);
        $header = str_replace('-', '_', $header);
        $header = 'HTTP_'.$header;

        return $header;
    }

    protected function csvToArray(string $csv, string $sep = ',', bool $trim = true) {
        $parts = explode($sep, $csv);

        if ($trim) {
            $parts = array_map(function($part){
                return trim($part);
            }, $parts);
        }

        return $parts;
    }

    public function logOff()
    {
        header('Location: '.$this->logout_url);
        exit();
    }

    public function getUserData($user, $requireGroups=true)
    {
        $groups = $this->default_groups;

        if (in_array($user, $this->admin_allowlist) && !in_array('admin', $groups)) {
            $groups[] = 'admin';
        }

        return [
            'name' => $user,
            'mail' => $user,
            'grps' => $groups,
        ];
    }
}
