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

    public function __construct()
    {
        parent::__construct();

        $this->loadConfig();

        $this->header_name = (string) $this->conf['header_name'];
        $this->default_groups = (array) $this->conf['default_groups'];
        $this->admin_allowlist = (array) $this->conf['admin_allowlist'];
        $this->missing_header_error = (bool) $this->conf['missing_header_error'];

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

    public function logOff()
    {
        header('Location: _oauth/logout');
        exit();
    }

    public function getUserData($user, $requireGroups=true)
    {
        $groups = $this->default_groups;

        if (in_array($user, $this->admin_allowlist)) {
            $groups[] = 'admin';
        }

        return [
            'name' => $user,
            'mail' => $user,
            'grps' => $groups,
        ];
    }
}
