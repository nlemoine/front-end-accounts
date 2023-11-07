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

namespace Chrisguitarguy\FrontEndAccounts\Form\Validator;

class Email extends ValidatorBase
{
    public function __construct($msg, private $regex)
    {
        $this->setMessage($msg);
    }

    public function isValid($val)
    {
        return filter_var($this->regex, FILTER_VALIDATE_REGEXP, ['options' => ['regexp'    => $this->pattern]]);
    }
}
