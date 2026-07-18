<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Source Collection Handles
    |--------------------------------------------------------------------------
    |
    | The Statamic collection handles where the apply_internal_links modifier
    | is allowed to run. Set this to an array such as ['blog', 'projects'].
    | Leave it null or empty to fall back to the legacy blog_collection key.
    |
    */

    'collections' => null,

    /*
    |--------------------------------------------------------------------------
    | Legacy Blog Collection Handle
    |--------------------------------------------------------------------------
    |
    | Deprecated alias kept for backward compatibility with existing installs.
    | Used only when collections is null, empty, or not a non-empty array.
    |
    */

    'blog_collection' => 'blog',

    /*
    |--------------------------------------------------------------------------
    | Admin Site Handle
    |--------------------------------------------------------------------------
    |
    | The site handle used to query the internal_links collection. This should
    | be the handle of the site you use to manage content in the CP.
    | Common values: pl, en, default.
    |
    */

    'admin_site' => 'en',

];
