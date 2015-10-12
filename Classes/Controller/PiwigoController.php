<?php
namespace Mom\MomPiwigo\Controller;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PiwigoController
 */
class PiwigoController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {


	private $piwigoURL = null;
	/**
	 * action show
	 *
	 * @param string $category
	 * @return void
	 */
	public function showAction($category = 0) {

	//	var_dump($this->settings);

		$this->piwigoURL = $this->settings['piwigoURL'];

		$categories = null;
		if (isset($this->settings['mode']) && $this->settings['mode'] == 'latest') {
			$categories = $this->getLatestCategory($category, 2);
		} else {
			$categories = $this->getCategoryList($category);
		}

		$this->view->assign('categories', $categories);

		$images = $this->getImages($category);
		$this->view->assign('images', $images);
	}



	private function getCategoryList($cat_id = 0, $tree_output = 'false')
	{

		$url = $this->piwigoURL . '/ws.php?format=json&method=pwg.categories.getList&cat_id=' .$cat_id . '&tree_output='. $tree_output;

		$data = $this->getJSONData($url);

		return $data['result']['categories'];

	}


	private function getLatestCategory($cat_id = 0, $limit = 2 ) {
		// http://piwigo.local/ws.php?format=rest&method=pwg.categories.getList&cat_id=0$ca&recursive=true

		$url = $this->piwigoURL . '/ws.php?format=json&method=pwg.categories.getList&recursive=true&cat_id=' .$cat_id;

		$data = $this->getJSONData($url);

		$cats = $data['result']['categories'];


		uasort($cats, array($this, 'catOrderReverse'));

		$latestCats = array();
		foreach( $cats as $cat) {
			if ($cat['nb_categories'] == 0) {
				$latestCats[] = $cat;
				$limit--;
				if ($limit == 0) {
					break;
				}


			}
		}

		return $latestCats;

	}


	public function catOrderReverse($x, $y) {
		//

		if ( $x['date_last'] == $y['date_last'] )
			return 0;
		else if ( $x['date_last'] < $y['date_last'] )
			return 1;
		else
			return -1;
	}
	

	private function getImages($categoryId = 0) {

			$url = $this->piwigoURL . '/ws.php?format=json&method=pwg.categories.getImages&cat_id=' . $categoryId . '&recursive=false';
			$data = $this->getJSONData($url);
			return $data['result']['images'];


	}

	/**
	 * @param $url
	 * @return mixed
	 */
	private function getJSONData($url)
	{
/*		$ch = curl_init();

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);
*/
		$result = GeneralUtility::getUrl($url);

		$data = json_decode($result, true);

		return $data;
	}


}