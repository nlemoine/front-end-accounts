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

/**
 * Handler for the actual account page. Please note that while other areas of
 * front end accounts strive to remain compatible with other areas (eg.
 * wp-login.php), this does not.
 *
 * @since   0.1
 */
class Account extends SectionBase
{
    public const NONCE = 'fe_accounts_edit';

    private $form = null;

    public function initSection($additional)
    {
        $this->maybeRedirect();

        if ($this->hasNag()) {
            $this->addError(
                'default_password_nag',
                \__("Looks like you're using a default password. Please change it.", FE_ACCOUNTS_TD)
            );
        }

        \add_action('frontend_accounts_after_fields_' . $this->getName(), [$this, 'addNonce']);
    }

    public function save($data, $additional)
    {
        $this->maybeRedirect();

        if (
            empty($data[static::NONCE]) ||
            !\wp_verify_nonce($_POST[static::NONCE], static::NONCE . \get_current_user_id())
        ) {
            $this->addError('bad_request', \__('Bad Request. Try again?', FE_ACCOUNTS_TD));
            return;
        }

        $form = $this->getForm();

        $form->bind($data);

        list($to_save, $errors) = $form->validate();

        if (!empty($errors)) {
            foreach ($errors as $k => $err) {
                $this->addError("validation_{$k}", $err);
            }

            return $this->dispatchFailed($data, $additional);
        }

        $user_id = $this->saveUser($to_save);

        if (!$user_id) {
            $this->addError('save_error', \__('Error saving! Try again.', FE_ACCOUNTS_TD));

            return $this->distpatchFailed($data, $additional);
        }

        $this->addError('success', \__('Account updated.', FE_ACCOUNTS_TD));

        \do_action('frontend_accounts_account_save_success', $user_id, $this);
    }

    public function getTitle()
    {
        return \esc_html__('Account', FE_ACCOUNTS_TD);
    }

    public function addNonce()
    {
        \wp_nonce_field(static::NONCE . \get_current_user_id(), static::NONCE, false);
    }

    protected function showContent()
    {
        $this->getForm()->render();
        echo $this->submit(\__('Save', FE_ACCOUNTS_TD));
    }

    protected function getName()
    {
        return 'edit';
    }

    protected function getForm()
    {
        if ($this->form) {
            return $this->form;
        }

        $user = \wp_get_current_user();

        // XXX do I need isset checks here? or does WP take of of setting empty values?
        $this->form = Form\Form::create(\apply_filters('frontend_accounts_account_form_initial', [
            'email'             => $user->user_email,
            'first_name'        => $user->first_name,
            'last_name'         => $user->last_name,
            'nickname'          => $user->nickname,
            'display_name'      => $user->display_name,
            'description'       => $user->description,
        ], $user));

        $this->form->addField('email', [
            'label'         => \__('Email', FE_ACCOUNTS_TD),
            'type'          => 'email',
            'required'      => true,
            'validators'    => [
                new Validator\NotEmpty(\__('Please enter an email.', FE_ACCOUNTS_TD)),
                new Validator\Email(\__('Please enter a valid email.', FE_ACCOUNTS_TD)),
            ],
        ]);

        $this->form->addField('first_name', [
            'label'         => \__('First Name', FE_ACCOUNTS_TD),
        ]);

        $this->form->addField('last_name', [
            'label'         => \__('Last Name', FE_ACCOUNTS_TD),
        ]);

        $this->form->addField('nickname', [
            'label'         => \__('Nickname', FE_ACCOUNTS_TD),
            //'required'      => true,
            'validators'    => [
                new Validator\NotEmpty(\__('Please enter a nickname.', FE_ACCOUNTS_TD)),
            ],
        ]);

        // XXX this is a select in the admin area, not going to do that here...
        $this->form->addField('display_name', [
            'label'         => \__('Display Name', FE_ACCOUNTS_TD),
        ]);

        $this->form->addField('description', [
            'label'         => \__('Description', FE_ACCOUNTS_TD),
            'type'          => 'textarea',
        ]);

        $this->form->addField('new_password', [
            'label'         => \__('New Password', FE_ACCOUNTS_TD),
            'type'          => 'password',
        ]);

        $this->form->addField('new_password_again', [
            'label'         => \__('New Password Again', FE_ACCOUNTS_TD),
            'type'          => 'password',
        ]);

        \do_action('frontend_accounts_alter_account_form', $this->form, $user);

        return $this->form;
    }

    private function maybeRedirect()
    {
        if (!\is_user_logged_in()) {
            \wp_safe_redirect(static::url('login'), 303);
            exit;
        } elseif (\apply_filters('frontend_accounts_redirect_readers', \current_user_can('read'))) {
            \wp_safe_redirect(\admin_url('profile.php'), 303);
            exit;
        }
    }

    private function saveUser(array $save)
    {
        $user = \wp_get_current_user();

        if (!empty($save['email'])) {
            $user->user_email = \sanitize_text_field($save['email']);
        }

        $user->first_name = isset($save['first_name']) ? \sanitize_text_field($save['first_name']) : '';
        $user->last_name = isset($save['last_name']) ? \sanitize_text_field($save['last_name']) : '';
        $user->nickname = isset($save['nickname']) ? \sanitize_text_field($save['nickname']) : '';
        $user->display_name = isset($save['display_name']) ? \sanitize_text_field($save['display_name']) : '';
        $user->description = isset($save['description']) ? \trim($save['description']) : '';

        $password_changed = false;
        if (!empty($save['new_password'])) {
            $pass = $save['new_password'];
            $pass_a = $save['new_password_again'];
            $allow = \apply_filters('frontend_accounts_allow_password_change', true, $user, $pass, $pass_a, $this);

            if ($allow && $pass === $pass_a) {
                $user->user_pass = $pass;
                $password_changed = true;
            } else {
                unset($user->data->user_pass);
                $this->addError('pass_error', \__('Could not update password.', FE_ACCOUNTS_TD));
            }
        } else {
            if (isset($user->data->user_pass)) {
                unset($user->data->user_pass);
            }
        }

        // other plugins can hook in here and modify things to save.
        \do_action('frontend_accounts_account_pre_save_user', $user, $save, $this);

        $user_id = \wp_update_user($user);

        if ($user_id && $password_changed && $this->hasNag()) {
            \delete_user_option($user_id, 'default_password_nag', true);
        }

        \do_action('frontend_accounts_account_post_save_user', $user, $save, $this);

        return $user_id;
    }

    private function hasNag()
    {
        return \apply_filters(
            'frontend_accounts_show_default_password_nag',
            \get_user_option('default_password_nag', \get_current_user_id())
        );
    }
}
