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
        $modelsList = [];

        if ($brands) {
            foreach ($brands->Principal as $brand) {
                $brandIgnore[] = $brand->N;
                if ($brand->N) {
                    $models = $this->getAllModelsByBrands($brand->N, true);
                    $modelsList = $models;
                }
            }

//            foreach ($brands->Common as $brand) {
//                if (!in_array($brand->N, $brandIgnore)) {
//                    $brandIgnore[] = $brand->N;
//                    if ($brand->N) {
//                        $models = $this->getAllModelsByBrands($brand->N, true);
//                        $modelsList = array_merge($modelsList, $models);
//                    }
//                }
//            }
        }

        if ($modelsList) {
            foreach ($modelsList as $model) {
                if($model->N) {
                    $this->getAllVersionsByModel($model->N, '', '', true);
                }
            }
        }
    }

    public function getAllBrands($createJsonFile = false)
    {
        $response = Curl::to($this->activeBrands)->get();
        $responseDecode = json_decode($response);

        if (count($responseDecode)) {
            if ($createJsonFile) {
                Storage::put('webmotors-crawler/brands.json', $response);
            }
        }
        return json_decode($response);
    }

    public function getAllModelsByBrands($brand, $createJsonFile = false)
    {
        $brand = strtoupper($brand);
        $response = Curl::to(str_replace('[BRAND]', $brand, $this->activeModels))->get();
        $responseDecode = json_decode($response);

        if (count($responseDecode)) {
            if ($createJsonFile) {
                Storage::put('webmotors-crawler/models/' . $this->removeCharacters(utf8_decode($brand)) . '-models.json', $response);
            }
        }

        return $responseDecode;
    }

    public function getAllVersionsByModel($model, $yearInit = '', $yearEnd = '', $createJsonFile = false)
    {
        $model = strtoupper($model);
        $response = Curl::to(str_replace(
                ['[MODEL]', '[YEARINIT]', '[YEAREND]',], [$model, $yearInit, $yearEnd],
                $this->activeModels)
        )->get();
        $responseDecode = json_decode($response);

        if (count($responseDecode)) {
            if ($createJsonFile) {
                Storage::put('webmotors-crawler/models/versions/' . $this->removeCharacters(utf8_decode($model)) . '-versions.json', $response);
            }
        }

        return $responseDecode;
    }

    public function removeCharacters($string)
    {
        $newString = preg_replace("/[^a-zA-Z0-9_.]/", "", strtr($string, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ ", "aaaaeeiooouucAAAAEEIOOOUUC_"));
        return $newString;
    }
}