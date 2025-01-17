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

abstract class ValidatorBase implements ValidatorInterface
{
    private string $msg = '';

    public function __construct($msg)
    {
        $this->setMessage($msg);
    }

    public function setMessage($errmsg)
    {
        $this->msg = $errmsg;
        return $this;
    }

    public function getMessage()
    {
        return $this->msg;
    }

    /**
     * {@inheritidoc}
     */
    public function valid($val)
    {
        if ($this->isValid($val) === false) {
            throw new ValidationException($this->getMessage());
        }

        return $val;
    }

    abstract protected function isValid($val);
}
