<?php
class Interactiv4_Mrw_Block_License_Imagetag extends Mage_Core_Block_Abstract
{
	private $__invalid = array();
    protected function _construct()
    {
    	parent::_construct();
        
    	//oreales: ignoramos licencia de momento
    	//$this->_unlicensedModules();
    }
	protected function _unlicensedModules()
	{
		$eb_helper = $this->helper('i4mrwes');
		$all_modules = Mage::helper('i4mrwes')->getModules();
		foreach ($all_modules as $module_name => $module_data){
			if( false === $eb_helper->is_key_valid($module_name, $module_data->version) ){
				$this->__invalid [$module_name]= $module_data;
			}
		}
	}
	private function __getDivStyles()
	{
		$styles = array();
		$styles []= 'position:fixed';
		$styles []= 'margin-left:15px';
		$styles []= 'bottom:0';
		$styles []= 'left:0';
		$styles []= 'width:240px';
		return implode(';', $styles);
	}
	private function __getDivContents()
	{
		$radius = '-webkit-border-radius: 14px 14px 0 0;-moz-border-radius: 14px 14px 0 0;border-radius: 14px 14px 0 0;';
		$as = 'text-decoration:none;display:block;background:#01498B;color:#fff;padding:4px;'.$radius;
		$html = '<a style="'.$as.'" onclick="$(\'unlicensed-interactiv4\').toggle();return false;" href="#">' . $this->__('Unlicensed Interactiv4 Module') . '</a>';
		$html .= '<ul id="unlicensed-interactiv4" style="display:none;width:100%;background:#4CAD71">';
		foreach($this->__invalid as $name => $data){
			$friendly_name = $this->helper('i4mrwes')->getModuleLabel($name, $data);
			$fix = $this->__('fix this');
			$html .= "<li>{$friendly_name}&nbsp;<a style=\"color:#fff\" href=\"http://www.interactiv4.com\">{$fix}</a></li>";
		}
		$html .= '</ul>';
		return $html;
	}
    /**
     * Override this method in descendants to produce html
     *
     * @return string
     */
    protected function _toHtml()
    {
    	if( count($this->__invalid) === 0 ){
    		return '';
    	}
    	//TODO: continue this
    	$params = '';
    	$div = '<div class="ilkeb" style="' . $this->__getDivStyles() . '">' . $this->__getDivContents() . '</div>';
        return $div . '<img src="//ebizmarts.com/buffet/invalid.php?p=' . base64_encode($params) . '" width="1" height="1" style="display:none;" />';
    }
}
