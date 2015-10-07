<?php
namespace Mom\MomPiwigo\Utility;

/**
 * Created by PhpStorm.
 * User: mauri
 * Date: 07.10.15
 * Time: 10:05
 */


class FlexFormHelper
{
    /**
     * @param array $fConfig
     * @param \TYPO3\CMS\Backend\Form\FormEngine $fObj
     *
     * @return void
     */
    public function getCategories(&$fConfig, $fObj) {


        $pluginConfiguration = array(
            'extensionName' => 'mompiwigo_piwigo',
            'pluginName' => 'tx_mompiwigo'
        );
        /** @var  \TYPO3\CMS\Extbase\Core\Bootstrap $bootstrap */
        $bootstrap = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Core\\Bootstrap');
        $bootstrap->initialize($pluginConfiguration);

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var  \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager */
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');


        $configuration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'tx_mompiwigo');


         var_dump($configuration);


        $categoryData = $this->getCategoryList();





        // change conf
        foreach ($categoryData as $category) {
            array_push($fConfig['items'], array(
                $category['name'],
                $category['id']
            ));

        }
    }


    private function getCategoryList($cat_id = 0, $tree_output = 'true')
    {

        $url = 'http://piwigo.local/ws.php?format=json&method=pwg.categories.getList&recursive=true&fullname=true';

        $data = $this->getJSONData($url);

        return $data['result']['categories'];

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
        $result = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($url);

        $data = json_decode($result, true);

        return $data;
    }
}