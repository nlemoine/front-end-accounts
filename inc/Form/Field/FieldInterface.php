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

namespace Chrisguitarguy\FrontEndAccounts\Form\Field;

/**
 * The interface for an individual field.
 *
 * @since   0.1
 */
interface FieldInterface extends \ArrayAccess
{
    /**
     * Set the value of the field.
     *
     * @since   0.1
     * @access  public
     * @param   scalar $val The value of the form field (probably a string)
     */
    public function setValue($val);

    /**
     * Get the value if the form field.
     *
     * @since   0.1
     * @access  public
     * @return  scalar The value of the form field (or null)
     */
    public function getValue();

    /**
     * Set the name of the form field.
     *
     * @since   0.1
     * @access  public
     * @param   string $name
     */
    public function setName($name);

    /**
     * Get the name of the form field.
     *
     * @since   0.1
     * @access  public
     * @return  string
     */
    public function getName();

    /**
     * Render the HTML of the field
     *
     * @since   0.1
     * @access  public
     */
    public function render();

    /**
     * Render the HTML for the fields label.
     *
     * @since   0.1
     * @access  public
     */
    public function label();

    /**
     * Validate the form field. Throws an exception if invalid.
     *
     * @since   0.1
     * @access  public
     * @return  scalar The valid form field value on success
     */
    public function validate();
}
