# ForwardAuth plugin for Dokuwiki

I wanted to use Traefik's ForwardAuth middleware with Dokuwiki. 

This is the result. 

## Configuration

### `header_name`

Request header name to treat as authenticated user. 
It's important to make sure that this header cannot be set by end-users. 
The reverse-proxy should take care of that. 

Internally, the header is converted to PHP's `$_SERVER[]` style: `X-Forwarded-User` becomes `HTTP_X_FORWARDED_USER`. 

### `default_groups`

All groups are member of the groups listed here. 
Use comma-separated values. 

### `admin_allowlist`

Users in this list additionally are assigned the `admin` group. 

### `missing_header_error`

Whether to bail out when the header is missing. 
Might be an attempt to circumvent the authentication. 
