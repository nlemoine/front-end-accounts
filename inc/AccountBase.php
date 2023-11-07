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

namespace Chrisguitarguy\FrontEndAccounts;

!\defined('ABSPATH') && exit;

abstract class AccountBase
{
    public const ACCOUNT_VAR = 'fe_account';

    public const ADDITIONAL_VAR = 'fe_account_add';

    private static $reg = [];

    public static function instance()
    {
        $cls = static::class;

        if (!isset(self::$reg[$cls])) {
            self::$reg[$cls] = new $cls();
        }

        return self::$reg[$cls];
    }

    public static function init()
    {
        \add_action('plugins_loaded', [static::instance(), '_setup'], 10);
    }

    abstract public function _setup();

    public static function url($area, $additional = null)
    {
        global $wp_rewrite;

        // maybe I should deal with trailingslash/non trailingslashhere?
        if ($wp_rewrite->using_permalinks()) {
            $path = "/account/{$area}";

            if ($additional) {
                $path .= '/' . $additional;
            }

            if (\substr($wp_rewrite->permalink_structure, -1) === '/') {
                $path = \trailingslashit($path);
            }
        } else {
            $q = [
                static::ACCOUNT_VAR => $area,
            ];

            if ($additional) {
                $q[static::ADDITIONAL_VAR] = $additional;
            }

            $path = '?' . \http_build_query($q);
        }

        return \apply_filters('frontend_accounts_url', \home_url($path), $area, $additional);
    }

    protected function getRole()
    {
        return \apply_filters('frontend_accounts_role', FE_ACCOUNTS_ROLE);
    }
}
