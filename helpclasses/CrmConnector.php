<?php

namespace app\helpclasses;
use PHPUnit\Util\Json;


/**
 * Класс для подключения к crm системе и получению из неё данных
 */
class CrmConnector{

    /**
     * @var cUrl Объект класса curl для подключения к crm
     */
    protected $curl;

    /**
     * @var string Токен для доступа к API сервису crm
     */
    private $token;

    /**
     * Конструктор класса
     *
     * @param string $token Ключ доступа к сервису
     */
    public function __construct($token){
        $this->token = $token;

    }

    /**
     * Получениие значения ключа доступа
     *
     * @return string Значение ключа
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Инициализируем подключение к crm и возвращаем нужные данные
     *
     * @param string $url Ссылка по которой необходимо выполнить запрос
     *
     * @return Json Необходимая информация в формате JSON
     */
    protected function curlInit($url)
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array (
            'Authorization: Bearer '.$this->token,
            'Accept-version: 1.0.0',
        ));
        $result = curl_exec($this->curl);
        curl_close($this->curl);
        $json = \GuzzleHttp\json_decode($result);
        return $json;
    }

    /**
     * Получаем список всех сайтов к которым имеет доступ заданный ключ
     *
     * @return array Список всех доступных сайтов
     */
    public function getSites()
    {
        $sites = [];
        $sitesInJson = $this->curlInit('https://api.webflow.com/sites');
        foreach ($sitesInJson as $item){
            $sites[$item->_id] = $item->name;
        }
        return $sites;
    }

    /**
     * Получаем список всех коллекций для заданного сайта
     *
     * @param string $siteId Уникальный идентификатор сайта
     *
     * @return array Список всех коллекций для сайта
     */
    public function getCollectionList($siteId)
    {
        $collections = [];
        $collectionsInJson = $this->curlInit('https://api.webflow.com/sites/'.$siteId.'/collections');
        foreach ($collectionsInJson as $item){
            $collections[$item->_id] = $item->name;
        }
        return $collections;
    }

    /**
     * Получаем название полей для выбранной модели
     *
     * @param string $collectionId Уникальный идентификатор выбранной коллекции
     *
     * @return array Название всех полей выбранной коллекцией
     */
    protected function getCollectionFields($collectionId)
    {
        $fieldsName = [];
        $namesInJson = $this->curlInit('https://api.webflow.com/collections/'.$collectionId);
        foreach ($namesInJson->fields as $field){
            $fieldsName [] = $field->name;
        }
        return $fieldsName;
    }

    /**
     * Получаем данные всех полей из выбранной коллекции
     *
     * @param $collectionId
     *
     * @return array Список всех ячеек заданной коллекции
     */
    protected function getItemsFromCollection($collectionId)
    {
        $fieldsItems = [];
        $itemsInJson = $this->curlInit('https://api.webflow.com/collections/'.$collectionId.'/items');
        foreach ($itemsInJson as $item){
            $fieldsItems [] = $item;
        }
        return $fieldsItems;
    }

    /**
     * Преобразование списков в строку которая подходит для CSV файла
     *
     * @param string $collectionId Идентификатор коллекции
     *
     * @return string Преобразованные данные в строку
     */
    public function convertArrayDataIntoString($collectionId)
    {
        $textFile = '';
        $fieldText = '';
        $fieldNames = $this->getCollectionFields($collectionId);
        $fieldContent = $this->getItemsFromCollection($collectionId);
        foreach ($fieldContent[0] as $content){
            foreach ($fieldNames as $name){
                $name = strtolower($name);
                var_dump($name);
                if(!isset($content->$name)){
                    break;
                }
                $fieldText .= $content->$name.';';
            }
            $fieldText .= PHP_EOL;
        }
        var_dump($fieldText);
        die();

        return $textFile;
    }
}