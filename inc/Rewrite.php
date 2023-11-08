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

/**
 * Create the "pretty url" rewrites for the accounts. Also creates some
 * subactions that get fired when accounts, save, and such.
 *
 * @since   0.1
 */
class Rewrite extends AccountBase
{
    private $section = null;

    private $additional = null;

    public function _setup()
    {
        \add_action('init', [$this, 'addRule']);
        \add_filter('query_vars', [$this, 'addVars']);
        \add_action('template_redirect', [$this, 'catchAccount']);
    }

    public function addRule()
    {
        \add_rewrite_rule(
            '^account/([A-Za-z0-9_-]+)(?:/([^/]+))?/?$',
            'index.php?' . static::ACCOUNT_VAR . '=$matches[1]&' . static::ADDITIONAL_VAR . '=$matches[2]',
            'top'
        );
    }

    public function addVars($vars)
    {
        $vars[] = static::ACCOUNT_VAR;
        $vars[] = static::ADDITIONAL_VAR;
        return $vars;
    }

    public function catchAccount()
    {
        global $wp_query;

        $this->section = \get_query_var(static::ACCOUNT_VAR);

        // are we on an accounts page?
        if (!$this->section) {
            return;
        }

        // make sure we're on out of our whitelisted sections or 404
        if (!\in_array($this->section, $this->getRegisteredSections(), true)) {
            $wp_query->set_404();
            return;
        }

        $this->additional = \trim(\get_query_var(static::ADDITIONAL_VAR), '/');

        $this->dispatchSave($_POST);
        $this->dispatchInit();

        \add_action('frontend_accounts_content', [$this, 'contentSubAction']);
        \add_filter('template_include', [$this, 'changeTemplate']);
    }

    public function contentSubAction()
    {
        \do_action("frontend_accounts_content_{$this->section}", $this->additional);
    }

    public function changeTemplate($tmp)
    {
        $found = \locate_template(\apply_filters('frontend_accounts_templates', [
            "account-{$this->section}.php",
            'account.php',
        ], $this->section, $this->additional));

        return $found ?: $tmp;
    }

    private function dispatchSave($postdata)
    {
        if (\strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
            \do_action(
                "frontend_accounts_save_{$this->section}",
                $postdata,
                $this->additional
            );
        }
    }

    private function dispatchInit()
    {
        \do_action('frontend_accounts_init', $this->section, $this->additional);
        \do_action("frontend_accounts_init_{$this->section}", $this->additional);
    }

    private function getRegisteredSections()
    {
        return \apply_filters('frontend_accounts_registered_sections', []);
    }
}
