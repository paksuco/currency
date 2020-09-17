<?php

return [
    /**
     * This setting defines the prefix for the package routes.
     *
     * For example if your admin page lives at /admin, the package route for
     * permission-ui roles page will be '/admin/roles', or the admin page is
     * set to '/management', you should change this to 'management' to set role
     * management routing to 'management/roles'
     */
    'admin_route_prefix' => "admin",

    /**
     * Guards for the page
     */
    'middleware' => ["web", "auth"],

    /**
     * Your admin template layout to extend
     */
    'template_to_extend' => "layouts.app",

    /**
     * Default currency to show when no default is selected
     */
    'default' => 'USD',

    /**
     * Use client country when there's no currency set?
     */
    'use_client' => false,

    /**
     * Saving the current currency method
     */
    'method' => 'session',

    /**
     * Create currency configuration per user?
     */
    'users_have_currencies' => true,

    /**
     * Will create currency per user column
     */
    'users_table' => 'users',

    /**
     * Will use the column name to create user currency id
     */
    'currency_column' => 'currency_id',

    /**
     * Fixer IO API Key to retrieve currencies
     */
    'fixer_api_key' => env("FIXER_API_KEY", ""),

    /**
     * If you're using Paksuco/Menu, you can set the priority here
     */
    'menu_priority' => 30
];
