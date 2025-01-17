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

use Chrisguitarguy\FrontEndAccounts\Form\Validator;

class ResetPassword extends SectionBase
{
    private $form = null;

    private $user = null;

    public function initSection($reset_key)
    {
        if (!$this->getUser($reset_key)) {
            return $this->abort();
        }
    }

    public function save($postdata, $reset_key)
    {
        $user = $this->getUser($reset_key);

        if (!$user) {
            return $this->abort();
        }

        $form = $this->getForm();

        $form->bind($postdata);

        [$valid, $errors] = $form->validate();

        if (!empty($errors)) {
            foreach ($errors as $k => $err) {
                $this->addError("validation_{$k}", $err);
            }

            return $this->dispatchFailed($postdata, $reset_key);
        }

        \do_action('validate_password_reset', new \WP_Error(), $user); // XXX wp-login.php compat, not 100% compat??

        if ($valid['password'] !== $valid['password_again']) {
            $this->addError('password_match', \__('Password do not match.', FE_ACCOUNTS_TD));

            return $this->dispatchFailed($postdata, $reset_key);
        }

        // not really a way to check of this actually worked...
        $this->setPassword($user, $valid['password']);

        $this->addError('success', \__('Your password has been reset.', FE_ACCOUNTS_TD));

        \do_action('frontend_accounts_reset_password_success', $postdata, $reset_key, $user, $this);
    }

    public function getTitle()
    {
        return \esc_html__('Reset Password', FE_ACCOUNTS_TD);
    }

    public function removeTemplate()
    {
        \remove_filter('template_include', [Rewrite::instance(), 'changeTemplate'], 10);
    }

    protected function showContent()
    {
        $this->getForm()->render();
        echo $this->submit(\__('Reset Password', FE_ACCOUNTS_TD));
    }

    protected function getName()
    {
        return 'reset_password';
    }

    protected function getForm()
    {
        if ($this->form) {
            return $this->form;
        }

        $this->form = Form\Form::create();

        $this->form->addField('password', [
            'label'         => \__('Password', FE_ACCOUNTS_TD),
            'type'          => 'password',
            'required'      => true,
            'validators'    => [new Validator\NotEmpty(\__('Please enter a new password.', FE_ACCOUNTS_TD))],
        ]);

        $this->form->addField('password_again', [
            'type'          => 'password',
            'label'         => \__('Password Again', FE_ACCOUNTS_TD),
            'validators'    => [new Validator\NotEmpty(\__('Please enter your new password again.', FE_ACCOUNTS_TD))],
        ]);

        \do_action('frontend_accounts_alter_reset_password_form', $this->form);

        return $this->form;
    }

    private function getUser($reset_key)
    {
        global $wpdb;

        if ($this->user !== null) {
            return $this->user;
        }

        if ($reset_key) {
            $this->user = $wpdb->get_row($wpdb->prepare(
                "SELECT * from {$wpdb->users} WHERE user_activation_key = %s LIMIT 1", // XXX select * is probably terrible...
                $reset_key
            ));
        } else {
            $this->user = false;
        }

        return $this->user;
    }

    private function abort()
    {
        global $wp_query;
        \add_filter('template_redirect', $this->removeTemplate(...), 11);
        \add_filter('frontend_accounts_disable_page_title', '__return_true');
        return $wp_query->set_404();
    }

    private function setPassword($user, $new_pass)
    {
        \do_action('password_reset', $user, $new_pass); // XXX wp-login.php compat

        \wp_set_password($new_pass, $user->ID);

        \wp_password_change_notification($user);
    }
}
