<?php

namespace EvoDev\WebMotorsCrawler;

use Curl;
use Storage;
use File;

class WebMotorsCrawler
{
    protected $webmotorsUrl = 'https://www.webmotors.com.br/carro/';
    protected $activeBrands = '';

    public function __construct()
    {
        $this->activeBrands = $this->webmotorsUrl . 'marcasativas?tipoAnuncio=novos-usados';
        $this->activeModels = $this->webmotorsUrl . 'modelosativos?&marca=[BRAND]&tipoAnuncio=novos-usados';
        $this->activeVersions = $this->webmotorsUrl . 'versoesativas?modelo=[MODEL]&anoModeloDe=[YEARINIT]&anoModeloAte=[YEAREND]';
    }

    public function saveJsonFiles()
    {
        set_time_limit(0);

        $brands = $this->getAllBrands(true);
        $brandIgnore = [];

        if ($brands) {
            foreach ($brands as $lot) {
                foreach ($lot as $brand) {
                    if (!in_array($brand->N, $brandIgnore)) {
                        $brandIgnore[] = $brand->N;
                        $models = $this->getAllModelsByBrands($brand->N, true);
                        if (count($models)) {
                            $this->getVersions($brand->N, $models, true);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param bool $createJsonFile
     * @return mixed
     */
    public function getAllBrands($createJsonFile = false)
    {
        $response = Curl::to($this->activeBrands)->get();
        $responseDecode = json_decode($response);

        if (count($responseDecode)) {
            if ($createJsonFile) {
                Storage::put('webmotors-crawler/BRANDS.json', $response);
            }
        }
        return json_decode($response);
    }

    /**
     * @param $brand
     * @param bool $createJsonFile
     * @return mixed
     */
    public function getAllModelsByBrands($brand, $createJsonFile = false)
    {
        $brand = strtoupper($brand);
        $url = str_replace('[BRAND]', urlencode($brand), $this->activeModels);
        $response = Curl::to($url)->get();
        $responseDecode = json_decode($response);
        dd($responseDecode);

        if (count($responseDecode)) {
            if ($createJsonFile) {
                Storage::put('webmotors-crawler/models/' . $this->removeCharacters($brand) . '/MODELS.json', $response);
            }
        }

        return $responseDecode;
    }

    /**
     * @param $brand
     * @param $modelsList
     * @param bool $createJsonFile
     */
    public function getVersions($brand, $modelsList, $createJsonFile = false)
    {
        foreach ($modelsList as $model) {
            if ($model->N) {
                $this->getAllVersionsByModel($brand, $model->N, '', '', $createJsonFile);
            }
        }
    }

    /**
     * @param $brand
     * @param $model
     * @param string $yearInit
     * @param string $yearEnd
     * @param bool $createJsonFile
     * @return mixed
     */
    public function getAllVersionsByModel($brand, $model, $yearInit = '', $yearEnd = '', $createJsonFile = false)
    {
        $brand = $this->removeCharacters(strtoupper($brand));
        $model = strtoupper($model);
        $url = str_replace(
            ['[MODEL]', '[YEARINIT]', '[YEAREND]',], [urlencode($model), $yearInit, $yearEnd],
            $this->activeVersions);
        $response = Curl::to($url)->get();
        $responseDecode = json_decode($response);

        if (count($responseDecode)) {
            if ($createJsonFile) {
                Storage::put('webmotors-crawler/models/' . $brand . '/versions/' . $this->removeCharacters($model) . '-versions.json', $response);
            }
        }

        return $responseDecode;
    }

    /**
     * @param $string
     * @return mixed|string
     */
    private function removeCharacters($string)
    {
        if ($string == 'CITROËN') {
            $newString = 'CITROEN';
        } else {
            $newString = preg_replace("/[^a-zA-Z0-9_.]/", "", strtr($string, "áàãâäéêëíïóôõöúüçÁÀÃÂÄÉÊËÍÏÓÔÕÖÚÜÇ ", "aaaaaeeeiioo0ouuucAAAAAEEEIIOOOOUUUC_"));
        }
        return $newString;
    }
}