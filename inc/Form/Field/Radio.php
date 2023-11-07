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

class Radio extends FieldBase implements FieldInterface
{
    /**
     * {@inheritdoc}
     * @see     Chrisguitarguy\FrontEndAccounts\Form\Field\FieldInterface::render();
     */
    public function render()
    {
        $name = $this->getName();
        $attr = $this->arrrayToAttr($this->getAdditionalAttributes());

        foreach ($this->getArg('choices', []) as $key => $label) {
            printf(
                '<label for="%1$s[%2$s]"><input type="radio" name="%1$s" id="%1$s[%2$s]" value="%2$s" %3$s /> %4$s</label>',
                $this->escAttr($name),
                $this->escAttr($key),
                $attr,
                $this->escHtml($label)
            );

            echo '<br />';
        }
    }
}
