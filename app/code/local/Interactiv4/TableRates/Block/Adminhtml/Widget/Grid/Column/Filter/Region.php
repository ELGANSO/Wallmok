<?php
class Interactiv4_TableRates_Block_Adminhtml_Widget_Grid_Column_Filter_Region
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    /**
     * Get condition
     *
     * @return array
     */
    public function getCondition()
    {
        $value = trim($this->getValue());
        if ($value == '*') {
            return array('null' => true);
        } else {
            return array('like' => '%'.$this->_escapeValue($this->getValue()).'%');
        }
    }
}
