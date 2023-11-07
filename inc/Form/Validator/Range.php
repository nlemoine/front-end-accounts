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

class Range extends ValidatorBase
{
    public function __construct($msg, private $min, private $max=null)
    {
        $this->setMessage($msg);
    }

    protected function isValid($val)
    {
        if (!is_int($this->min)) {
            return false;
        }

        if (is_int($this->max)) {
            return $val >= $this->min && $val <= $this->max;
        }

        return $val >= $this->min;
    }
}
