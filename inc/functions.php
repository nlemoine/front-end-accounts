<?php
/**
 * Front End Accounts
 *
 * @category    WordPress
 * @package     FrontEndAccounts
 * @since       0.1
 * @author      Christopher Davis <http://christopherdavis.me>
 * @copyright   2013 Christopher Davis
 * @license     http://opensource.org/licenses/MIT MIT
 */

!defined('ABSPATH') && exit;

use Chrisguitarguy\FrontEndAccounts as Accounts;

/**
 * Template Tag for use with this plugin.
 *
 * @since   0.1
 * @uses    do_action
 */
function the_account()
{
    do_action('frontend_accounts_content');
}

/**
 * Sub-action to plugins that extend this one to  hook into without having to
 * worry about checking for the existence of contants or the like.
 *
 * @since   0.1
 * @uses    do_action
 */
function frontend_accounts_init()
{
    do_action('frontend_accounts_init');
}

/**
 * Main "load" function. Hooked into `plugins_loaded` calls all of our classes
 * and sets things up.
 *
 * @since   0.1
 * @uses    is_admin
 */
function frontend_accounts_load()
{
    Accounts\Rewrite::init();

    if (!is_admin()) {
        Accounts\Login::init();
        Accounts\Account::init();
        Accounts\ForgotPassword::init();
        Accounts\ResetPassword::init();
        Accounts\Seo::init();

        if (get_option('users_can_register')) {
            Accounts\Register::init();
        }
    }

    if (!apply_filters('frontend_accounts_disable_themes', false)) {
        frontend_accounts_register_themes();
    }
}

/**
 * Register our unpriviledged user role. You probably want front end accounts
 * because you don't want people to see the admin area. So this plugin provides
 * a user role without the `read` capability that grants users access to their
 * admin area profiles. Additionally, if a user is logged in and can `read`
 * FrontEndAccounts will redirect them to the admin area.
 *
 * @since   0.1
 * @uses    add_role
 */
function frontend_accounts_add_role()
{
    add_role(FE_ACCOUNTS_ROLE, __('Unprivileged User', FE_ACCOUNTS_TD), [
        'read'  => false,
    ]);
}

/**
 * Remove our unpriviledge role.
 *
 * @since   0.1
 * @uses    remove_role
 */
function frontend_accounts_remove_role()
{
    remove_role(FE_ACCOUNTS_ROLE);
}

/**
 * Activation hook.
 *
 * @since   0.1
 * @uses    frontend_accounts_add_role
 * @uses    flush_rewrite_rules
 */
function frontend_accounts_activate()
{
    frontend_accounts_add_role();
    Accounts\Rewrite::instance()->addRule();
    flush_rewrite_rules();
}

/**
 * Deactivation hook.
 *
 * @since   0.1
 * @uses    frontend_accounts_remove_role
 * @uses    flush_rewrite_rule
 */
function frontend_accounts_deactivate()
{
    frontend_accounts_remove_role();
    flush_rewrite_rules();
}

/**
 * Register our theme directory.
 *
 * @since   0.1
 * @uses    register_theme_directory
 */
function frontend_accounts_register_themes()
{
    register_theme_directory(dirname(__DIR__) . '/themes');
}
