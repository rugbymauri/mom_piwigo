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

	const DEFAULT_LIMIT = 2;
	const DEFAULT_CATEGORY = 0;
	const DEFAULT_TREEOUTPUT = 'false';

	private $categories = null;

	private $piwigoURL = null;
	/**
	 * action show
	 *
	 * @param string $category
	 * @return void
	 */
	public function showAction($category = self::DEFAULT_CATEGORY) {

	//	var_dump($this->settings);

		$this->piwigoURL = $this->settings['piwigoURL'];

		$categories = null;
		$limit = self::DEFAULT_LIMIT;

		if (isset($this->settings['mode'])) {
			switch ($this->settings['mode']) {
				case 'latest':
					$categories = $this->getLatestCategory($category, $limit);
					break;
				case 'random':
					$categories = $this->getRandomCategory($category, $limit);
					break;
				default:
					$categories = $this->getCategoryList($category);
			}
		}

		$this->view->assign('categories', $categories);

		$images = $this->getImages($category);
		$this->view->assign('images', $images);


		$breadCrumb = $this->getBreadCrumb($category);
		$this->view->assign('breadcrumb', $breadCrumb);

		if ($category > 0) {
			$this->initCategories();
			$this->view->assign('current', $this->categories[$category] );
		}

	}

	private function getBreadCrumb($catId = self::DEFAULT_CATEGORY) {
		$result = array();
		$this->initCategories();

		if (isset($this->categories[$catId])) {

			$upperCats = array_reverse(explode(',', $this->categories[$catId]['uppercats']));
			foreach ($upperCats as $cat) {
				array_unshift($result, $this->categories[$cat]);
			}


		}

		return $result;


	}


	private function getCategoryList($cat_id = self::DEFAULT_CATEGORY, $tree_output = self::DEFAULT_TREEOUTPUT)
	{

		$url = $this->piwigoURL . '/ws.php?format=json&method=pwg.categories.getList&cat_id=' .$cat_id . '&tree_output='. $tree_output;

		$data = $this->getJSONData($url);


		$categories = $data['result']['categories'];

		$result = array();

		foreach($categories as $category) {
			if ($cat_id == $category['id']) {
				continue;
			}

			$result[] = $category;
		}

		return $result;

	}


	private function getLatestCategory($cat_id = self::DEFAULT_CATEGORY, $limit = self::DEFAULT_LIMIT ) {
		// http://piwigo.local/ws.php?format=rest&method=pwg.categories.getList&cat_id=0$ca&recursive=true

		$url = $this->piwigoURL . '/ws.php?format=json&method=pwg.categories.getList&recursive=true&cat_id=' .$cat_id;

		$data = $this->getJSONData($url);

		$cats = $data['result']['categories'];


		uasort($cats, array($this, 'catOrderReverse'));

		$latestCats = array();
		foreach( $cats as $category) {
			if ($cat_id == $category['id']) {
				continue;
			}

			if ($category['nb_categories'] == 0) {
				$latestCats[] = $category;
				$limit--;
				if ($limit == 0) {
					break;
				}
			}
		}

		return $latestCats;

	}

	private function getRandomCategory($cat_id = self::DEFAULT_CATEGORY, $limit = self::DEFAULT_LIMIT ) {
		// http://piwigo.local/ws.php?format=rest&method=pwg.categories.getList&cat_id=0$ca&recursive=true

		$url = $this->piwigoURL . '/ws.php?format=json&method=pwg.categories.getList&recursive=true&cat_id=' .$cat_id;

		$data = $this->getJSONData($url);

		$cats = $data['result']['categories'];

		$numberOfCatagories = count($cats);

		$randomCategories = array();
		if ($numberOfCatagories >= $limit) {
			$randomCategories = array_rand($cats, $limit);
		} else {
			$randomCategories = $cats;
		}



		foreach( $cats as $cat) {
			if ($cat['nb_categories'] == 0) {
				$randomCategories[] = $cat;
				$limit--;
				if ($limit == 0) {
					break;
				}


			}
		}

		return $randomCategories;

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
	

	private function getImages($categoryId = self::DEFAULT_CATEGORY) {

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
		$result = GeneralUtility::getUrl($url . '&' . rand(0, 100000000));

		$data = json_decode($result, true);

		return $data;
	}

	/**
	 * @return mixed
	 */
	private function initCategories()
	{
		if ($this->categories == null) {
			$this->categories = array();
			$url = $this->piwigoURL . '/ws.php?format=json&method=pwg.categories.getList&cat_id=0&recursive=true';
			$data = $this->getJSONData($url);


			foreach ($data['result']['categories'] as $cat) {
				$this->categories[$cat['id']] = $cat;
			}

		}
	}


}