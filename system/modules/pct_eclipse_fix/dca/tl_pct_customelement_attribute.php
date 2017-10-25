<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2017
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		eclise
 * @link		http://contao.org
 * @license		http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

$GLOBALS['TL_DCA']['tl_pct_customelement_attribute']['config']['onsubmit_callback'][] = array('pct_eclipse_fix_tl_pct_customelement_attribute','mt_default_s__to__mb_default_s');


/**
 * Class
 */
class pct_eclipse_fix_tl_pct_customelement_attribute extends \Backend
{
	public function mt_default_s__to__mb_default_s($objDC)
	{
		$objDatabase = \Database::getInstance();
		if(!$objDC->activeRecord)
		{
			$objDC->activeRecord = $objDatabase->prepare("SELECT * FROM ".$objDC->table." WHERE id=?")->limit(1)->execute($objDC->id);
		}
		
		$strField = 'margin_bottom_mobile';
		if($objDC->activeRecord->alias == $strField)
		{
			$arrOptions = deserialize($objDC->activeRecord->options);
			
			if($arrOptions[0]['value'] == 'mt-default-s')
			{
				$arrOptions[0]['value'] = 'mb-default-s';
				// update attribute
				$objDatabase->prepare("UPDATE ".$objDC->table." %s WHERE id=?")->set( array('options'=>serialize($arrOptions)) )->execute($objDC->id);
			}
			
			// update wizards
			$arrUpdated = array();
			if($arrOptions[0]['value'] == 'mb-default-s')
			{
				$objWizards = $objDatabase->prepare("SELECT * FROM tl_pct_customelement_vault WHERE type=? AND data_blob IS NOT NULL")->execute('wizard');
				while($objWizards->next())
				{
					$arrWizard = deserialize($objWizards->data_blob);
					if($arrWizard['values'][ $objDC->activeRecord->uuid ] == 'mt-default-s')
					{
						$arrWizard['values'][ $objDC->activeRecord->uuid ] = 'mb-default-s';
						// update wizard
						$objDatabase->prepare("UPDATE tl_pct_customelement_vault %s WHERE id=?")->set( array('data_blob'=>serialize($arrWizard)) )->execute($objWizards->id);
						
						$arrUpdated[] = $objWizards->id;
					}
				}
			}
			
			if(count($arrUpdated) > 0)
			{
				$msg = 'Attribut "'.$strField.'" in tl_pct_customelement_vault.id='.implode(',', $arrUpdated).' updated';
				\System::log($msg,__METHOD__,TL_GREEN);
				\Message::addInfo($msg);
			}		
			

		}
		
	}	
}