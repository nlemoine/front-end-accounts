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

namespace Chrisguitarguy\FrontEndAccounts\Form;

class Form implements FormInterface
{
    private array $fields = [];

    private array $bound = [];

    public function __construct(
        private array $initial = [
        ]
    ) {
    }

    public static function create(array $initial = [])
    {
        return new self($initial);
    }

    public function render()
    {
        foreach ($this->getFields() as $field) {
            $this->renderRow($field);
        }
    }

    public function renderField($field)
    {
        $fields = $this->getFields();
        if (!isset($fields[$field])) {
            throw new \InvalidArgumentException(\sprintf('Field "%s" does not exist', $field));
        }

        $this->renderRow($this->fields[$field]);
    }

    public function validate()
    {
        $values = $errors = [];

        foreach ($this->getFields() as $id => $field) {
            // replace value with bound data if we have it.
            if (isset($this->bound[$id])) {
                $field->setValue($this->bound[$id]);
            }

            try {
                $values[$id] = $field->validate();
            } catch (Validator\ValidationException $e) {
                $errors[$id] = $e->getMessage();
            }
        }

        return [$values, $errors];
    }

    public function bind(array $data)
    {
        $this->bound = $data;
        return $this;
    }

    /**
     * @todo    Maybe lazy load classes here?
     */
    public function addField($field_id, array $args = [])
    {
        $this->fields[$field_id] = $this->getFieldObject($field_id, $args);

        if (isset($this->initial[$field_id])) {
            $this->fields[$field_id]->setValue($this->initial[$field_id]);
        }

        return $this->fields[$field_id];
    }

    public function removeField($field_id)
    {
        if (isset($this->fields[$field_id])) {
            unset($this->fields[$field_id]);
            return true;
        }

        return false;
    }

    public function getField($field_id)
    {
        return $this->fields[$field_id] ?? null;
    }

    public function getFields()
    {
        return $this->fields;
    }

    protected function renderRow(Field\FieldInterface $f)
    {
        if ($f instanceof Field\HiddenInput) {
            return $f->render();
        }

        $tag = \apply_filters('frontend_accounts_field_wraptag', 'p', $f);
        $cls = \apply_filters('frontend_accounts_field_wrapclass', \sprintf('fe-accounts-field-wrap %s', $f->getName()), $f);

        \printf('<%s class="%s">', \tag_escape($tag), \esc_attr($cls));
        \do_action('frontend_accounts_field_before_label', $f);
        $f->label();
        \do_action('frontend_accounts_field_before_input', $f);
        $f->render();
        \do_action('frontend_accounts_field_after_input', $f);
        echo "</{$tag}>";
    }

    protected function getFieldObject($name, array $args)
    {
        $type = $args['type'] ?? 'text';

        $cls = match ($type) {
            'text'           => 'TextInput',
            'password'       => 'PasswordInput',
            'hidden'         => 'HiddenInput',
            'color'          => 'ColorInput',
            'date'           => 'DateInput',
            'datetime'       => 'DateTimeInput',
            'datetime-local' => 'DateTimeLocalInput',
            'email'          => 'EmailInput',
            'month'          => 'MonthInput',
            'number'         => 'NumberInput',
            'search'         => 'SearchInput',
            'time'           => 'TimeInput',
            'url'            => 'UrlInput',
            'week'           => 'WeekInput',
            'multiple'       => 'Multiple',
            'radio'          => 'Radio',
            'select'         => 'Select',
            'textarea'       => 'Textarea',
            'checkbox'       => 'Checkbox',
            'file'           => 'FileInput',
            default          => 'DummyField',
        };

        $cls = "Chrisguitarguy\\FrontEndAccounts\\Form\\Field\\{$cls}";

        return \apply_filters('frontend_accounts_field_object', new $cls($name, $args), $name, $args);
    }
}
