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

class Login extends SectionBase
{
    private $form = null;

    public function initSection($additional)
    {
        switch ($additional) {
            case 'password_reset':
                $this->addError('password_reset', \__('Your password has been reset. Please Log in.', FE_ACCOUNTS_TD));
                break;
            case 'registration_complete':
                $this->addError(
                    'registration_complete',
                    $this->allowUserPasswords() ? \__('Registration complete, please log in.', FE_ACCOUNTS_TD) : \__('Registration complete. Check your email for a password.', FE_ACCOUNTS_TD)
                );
                break;
        }
    }

    public function save($data, $additional)
    {
        $form = $this->getForm();

        $form->bind($data);

        [$data, $errors] = $form->validate();

        if (!empty($errors)) {
            foreach ($errors as $k => $err) {
                $this->addError("validation_{$k}", $err);
            }

            return $this->dispatchFailed($data, $additional);
        }

        $user = \wp_signon();

        if (!$user || \is_wp_error($user)) {
            foreach ($user->get_error_codes() as $code) {
                $this->addError("validation_{$code}", $this->getWpErrorMessage($code));
            }

            return $this->dispatchFailed($data, $additional);
        }

        \do_action('wp_login', $user->user_login, $user); // XXX wp-login.php compat
        \do_action('frontend_accounts_login_success', $user, $data, $additional, $this);

        $redirect_to = !empty($data['redirect_to']) ? $data['redirect_to'] : $this->getDefaultRedirect($user);

        \wp_safe_redirect(
            \apply_filters('frontend_accounts_login_redirect_to', $redirect_to, $user, $data, $additional, $this),
            303
        );
        exit;
    }

    public function getTitle()
    {
        return \esc_html__('Login', FE_ACCOUNTS_TD);
    }

    protected function showContent()
    {
        $this->getForm()->render();

        echo $this->submit(\__('Login', FE_ACCOUNTS_TD));

        echo '<p class="fe-accounts-forgot-password">';
        \printf(
            '<a href="%s">%s</a>',
            static::url('forgot_password'),
            \__('Forgot password?', FE_ACCOUNTS_TD)
        );
        echo '</p>';
    }

    protected function getName()
    {
        return 'login';
    }

    protected function dispatchFailed($data, $additional)
    {
        if (!empty($data['log'])) {
            \do_action('wp_login_failed', $data['log']); // XXX compat for wp-login.php
        }

        parent::dispatchFailed($data, $additional);
    }

    protected function getForm()
    {
        if ($this->form) {
            return $this->form;
        }

        $this->form = Form\Form::create([
            'redirect_to' => $_GET['redirect_to'] ?? null,
        ]);

        $this->form->addField('log', [
            'label'         => \__('Username', FE_ACCOUNTS_TD),
            'validators'    => [new Validator\NotEmpty(\__('Please enter a username', FE_ACCOUNTS_TD))],
        ]);

        $this->form->addField('pwd', [
            'type'          => 'password',
            'label'         => \__('Password', FE_ACCOUNTS_TD),
            'validators'    => [new Validator\NotEmpty(\__('Please enter a password', FE_ACCOUNTS_TD))],
        ]);

        $this->form->addField('rememberme', [
            'type'          => 'checkbox',
            'label'         => \__('Remember Me', FE_ACCOUNTS_TD),
        ]);

        $this->form->addField('redirect_to', [
            'type'          => 'hidden',
        ]);

        \do_action('frontend_accounts_alter_login_form', $this->form);

        return $this->form;
    }

    private function getWpErrorMessage($code)
    {
        $msg = match ($code) {
            'invalid_username', 'incorrect_password' => \__('Invalid username and/or password', FE_ACCOUNTS_TD),
            default => \__('Error logging in', FE_ACCOUNTS_TD),
        };

        return \apply_filters('frontend_accounts_login_wp_error_message', $msg, $code);
    }

    private function getDefaultRedirect($user)
    {
        return \apply_filters('frontend_accounts_default_login_redirect', static::url('edit'), $user);
    }
}
