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
    public function __construct(
        $msg,
        private readonly array $choices = [
        ]
    ) {
        $this->setMessage($msg);
    }

    protected function isValid($val)
    {
        return \in_array($val, $this->choices, true);
    }
}
