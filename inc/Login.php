<?php
/**
 * Front End Accounts
 *
 * @category    WordPress
 * @package     FrontEndAcounts
 * @since       0.1
 * @author      Christopher Davis <http://christopherdavis.me>
 * @copyright   2013 Christopher Davis
 * @license     http://opensource.org/licenses/MIT MIT
 */

namespace Chrisguitarguy\FrontEndAccounts;

!defined('ABSPATH') && exit;

class Login extends SectionBase
{
    protected function showContent()
    {
        
    }

    protected function getTitle()
    {
        return esc_html__('Login', FE_ACCOUNTS_TD);
    }

    protected function getName()
    {
        return 'login';
    }
}
